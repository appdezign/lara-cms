<?php

namespace Lara\Front\Http\Traits;

use Google\Cloud\Translate\TranslateClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lara\Common\Models\Blacklist;

trait FrontSecurityTrait
{

	/**
	 * @param object $entity
	 * @param object $object
	 * @param array $fieldtypes
	 * @return object
	 */
	private function detectSpam(object $entity, object $object, array $fieldtypes)
	{

		$app = app();
		$data = $app->make('stdClass');

		// patch 6.2.23 - start
		$isBlackListed = false;
		if (isset($object->ipaddress)) {
			$isBlackListed = $this->isBlacklisted($object->ipaddress);
		}

		if ($isBlackListed) {

			// ip address is blacklisted
			// no further checks necessary
			$data->result = true;
			$data->message = 'too many requests ...';

		} else {
			// patch 6.2.23 - end

			$spamScore = 0;

			// check for links
			$detectLinks = $this->detectLinkInString($entity, $object, $fieldtypes);
			if ($detectLinks) {
				$spamScore = $spamScore + config('lara.forms_anti_spam.spam_score_link');
			}

			// check for email addresses
			$detectEmails = $this->detectEmailInString($entity, $object, $fieldtypes);
			if ($detectEmails) {
				$spamScore = $spamScore + config('lara.forms_anti_spam.spam_score_email');
			}

			// detect language
			$matchLang = $this->matchLanguage($entity, $object);
			if (!$matchLang) {
				$spamScore = $spamScore + config('lara.forms_anti_spam.spam_score_language');
			}

			// check total score
			if ($spamScore >= config('lara.forms_anti_spam.threshold')) {
				$data->result = true;
				$data->message = 'spam detected';

				// patch 6.2.23 - start
				$this->addToBlacklist($object->ipaddress);
				// patch 6.2.23 - end

			} else {
				$data->result = false;
				$data->message = 'passed';
			}
		}

		return $data;

	}

	/*
	 * part of patch 6.2.23
	 */
	private function addToBlacklist($ipaddress)
	{

		return Blacklist::create(['ipaddress' => $ipaddress]);

	}

	/*
	 * part of patch 6.2.23
	 */
	private function isBlacklisted($ipaddress)
	{

		// check blacklist table
		$this->checkBlackListTable();

		$blackListCheck = Blacklist::where('ipaddress', $ipaddress)->first();

		if ($blackListCheck) {
			return true;
		} else {
			return false;
		}

	}

	/*
	 * part of patch 6.2.23
	 */
	private function checkBlackListTable()
	{

		// check blacklist table
		$tablename = 'lara_sys_blacklist';
		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) {
				$table->increments('id');
				$table->string('ipaddress')->nullable();
				$table->timestamps();
			});
		}

		return true;

	}

	/*
	 * part of patch 6.2.23
	 */
	private function checkBlackListColumn($entity)
	{

		$column = 'ipaddress';

		$modelClass = $entity->getEntityModelClass();
		$model = new $modelClass;
		$tablename = $model->getTable();

		if (!Schema::hasColumn($tablename, $column)) {
			Schema::table($tablename, function ($table) use ($column) {
				$table->string($column)->nullable();
			});
		}

	}

	/**
	 * @param object $entity
	 * @param object $object
	 * @param array $fieldtypes
	 * @return bool
	 */
	private function detectEmailInString(object $entity, object $object, array $fieldtypes): bool
	{
		// This regular expression extracts all emails from a string:
		$regexp = '/([a-z0-9_\.\-])+(\@|\[at\])+(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';

		$stringHasEmail = false;

		foreach ($entity->getCustomColumns() as $field) {
			if (in_array($field->fieldtype, $fieldtypes)) {
				$fieldname = $field->fieldname;
				$fieldval = $object->$fieldname;
				preg_match_all($regexp, $fieldval, $m);
				if (sizeof($m[0]) > 0) {
					$stringHasEmail = true;
					$object->$fieldname = '[SPAM] - ' . $fieldval;
					$object->save();
				}
			}
		}

		return $stringHasEmail;

	}

	/**
	 * @param object $entity
	 * @param object $object
	 * @param array $fieldtypes
	 * @return bool
	 */
	private function detectLinkInString(object $entity, object $object, array $fieldtypes): bool
	{

		$patterns = config('lara.detect_link_patterns');

		$stringHasLinks = false;

		foreach ($entity->getCustomColumns() as $field) {
			if (in_array($field->fieldtype, $fieldtypes)) {
				$fieldname = $field->fieldname;
				$fieldval = $object->$fieldname;
				foreach ($patterns as $pattern) {
					if (Str::contains($fieldval, $pattern)) {
						$stringHasLinks = true;
						$object->$fieldname = '[SPAM] - ' . $fieldval;
						$object->save();
					}
				}
			}
		}

		return $stringHasLinks;
	}

	/**
	 * @param object $entity
	 * @param $object
	 * @return bool
	 */
	private function matchLanguage(object $entity, $object): bool
	{

		$matchLang = true;

		if (config('lara.detect_language.enabled')) {

			if (config('lara.google_translate_api_key')) {

				$translate = new TranslateClient([
					'key' => config('lara.google_translate_api_key'),
				]);

				$allowedLanguages = config('lara.detect_language.languages_allowed');
				$detectFields = config('lara.detect_language.entity_fields');
				$wordThresholdMin = config('lara.detect_language.wordcount_threshold_min');
				$wordThresholdMax = config('lara.detect_language.wordcount_threshold_max');

				if (array_key_exists($entity->getEntityKey(), $detectFields)) {

					$entkey = $entity->getEntityKey();
					$detectEntityFields = $detectFields[$entkey];

					foreach ($entity->getCustomColumns() as $field) {
						if (in_array($field->fieldname, $detectEntityFields)) {
							$fieldname = $field->fieldname;
							$fieldval = $object->$fieldname;
							if (str_word_count($fieldval) > $wordThresholdMin) {
								if (str_word_count($fieldval) < $wordThresholdMax) {
									$result = $translate->detectLanguage($fieldval);
									if (!in_array($result['languageCode'], $allowedLanguages)) {
										// detected language is not allowed, mark as spam
										$matchLang = false;
									}
								} else {
									// too many words, mark as spam
									$matchLang = false;
								}

							}
						}
					}
				}
			}
		}

		return $matchLang;

	}

	/**
	 * @param object $entity
	 * @return array
	 */
	private function getValidationRules(object $entity)
	{

		$requiredFields = array();

		foreach ($entity->getCustomColumns() as $field) {

			$fieldname = $field->fieldname;

			if ($field->fieldtype == 'email') {
				if ($field->required) {
					$requiredFields[$fieldname] = 'email:rfc,dns|required';
				} else {
					$requiredFields[$fieldname] = 'email:rfc,dns';
				}
			} else {
				if ($field->required) {
					$requiredFields[$fieldname] = 'required';
				}
			}
		}

		return $requiredFields;

	}

}
