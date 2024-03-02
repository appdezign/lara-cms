<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Str;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Entitygroup;
use Lara\Common\Models\EntityView;
use Lara\Common\Models\Setting;
use Lara\Common\Models\Tag;
use Lara\Common\Models\Taxonomy;
use Lara\Common\Models\Translation;
use Lara\Common\Models\MediaImage;
use Lara\Common\Models\User;

use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;
use Bouncer;

use Google\Cloud\Translate\TranslateClient;

trait AdminDbUpdateTrait
{

	/**
	 * @return RedirectResponse|null
	 */
	private function checkForLaraUpdates($process = false)
	{

		// current DB version
		$databaseVersion = $this->getVersionFromSettings();

		// new DB version
		$laraVersion = config('lara.lara_db_version');

		$builds = config('lara.lara_db_builds');

		$updates = array();

		foreach ($builds as $build) {
			if (version_compare($build, $databaseVersion, '>') && version_compare($build, $laraVersion, '<=')) {
				$updates[] = $build;
			}
		}

		if ($process) {

			if (!empty($updates)) {

				/* ~~~~~~~~~~~~ UPDATES ~~~~~~~~~~~~ */

				if (in_array('6.0.1', $updates)) {

					$this->updateTranslationTable();

					$this->setSetting('system', 'lara_db_version', '6.0.1');

				}

				if (in_array('6.0.2', $updates)) {

					$this->addIndexToPivotTable();

					$this->setSetting('system', 'lara_db_version', '6.0.2');

				}

				if (in_array('6.0.3', $updates)) {

					$this->fixLaraVersionKey();

					$this->setSetting('system', 'lara_db_version', '6.0.3');

				}

				if (in_array('6.0.4', $updates)) {

					$this->addHeroImage();

					$this->setSetting('system', 'lara_db_version', '6.0.4');

				}

				if (in_array('6.2.1', $updates)) {

					$this->addPrevNextToViews();
					$this->addPositionToImages();
					$this->addTaxonomy();

					$this->setSetting('system', 'lara_db_version', '6.2.1');

				}

				if (in_array('6.2.5', $updates)) {

					$this->addHideInGalleryToImages();

					$this->setSetting('system', 'lara_db_version', '6.2.5');

				}

				if (in_array('6.2.8', $updates)) {

					$this->fixWidgetsColumn();

					$this->setSetting('system', 'lara_db_version', '6.2.8');

				}

				if (in_array('6.2.16', $updates)) {

					$this->scanContactFormsForSpam();

					$this->setSetting('system', 'lara_db_version', '6.2.16');

				}

				if (in_array('6.2.20', $updates)) {

					$this->updatePermissions();

					$this->setSetting('system', 'lara_db_version', '6.2.20');

				}

				if (in_array('6.2.22', $updates)) {

					$this->addDocsTranslations();

					$this->setSetting('system', 'lara_db_version', '6.2.22');

				}

				if (in_array('6.2.23', $updates)) {

					$this->addHideInLIst();

					$this->setSetting('system', 'lara_db_version', '6.2.23');

				}

				if (in_array('6.3.1', $updates)) {

					$this->addDynamicDisks();
					$this->addInfiniteToViews();
					$this->addBlackList();
					$this->addEmailVerfication();
					$this->addVideoFiles();
					$this->adduserProfiles();

					$this->setSetting('system', 'lara_db_version', '6.3.1');

				}

				if (in_array('7.0.1', $updates)) {

					$this->setSetting('system', 'lara_db_version', '7.0.1');

				}

				if (in_array('7.1.0', $updates)) {

					$this->setSetting('system', 'lara_db_version', '7.1.0');

				}

				if (in_array('7.1.4', $updates)) {

					$this->addImageTranslations();

					$this->setSetting('system', 'lara_db_version', '7.1.4');

				}

				if (in_array('7.5.11', $updates)) {

					$this->addIndexToSlugColumn();

					$this->setSetting('system', 'lara_db_version', '7.5.11');

				}

				if (in_array('7.5.36', $updates)) {

					$this->updateAdminMenuIcons();

					$this->setSetting('system', 'lara_db_version', '7.5.36');

				}

				if (in_array('7.5.46', $updates)) {

					$this->addPreventCropping();

					$this->setSetting('system', 'lara_db_version', '7.5.46');

				}

				if (in_array('8.1.11', $updates)) {

					$this->updateFormTranslations();

					$this->setSetting('system', 'lara_db_version', '8.1.11');

				}

				if (in_array('8.2.1', $updates)) {

					$this->updateImageTable();
					$this->updateMenuItemTable();

					$this->updateConfigFiles();
					$this->createSeoGroup();
					$this->addHeaderTags();

					$this->setSetting('system', 'lara_db_version', '8.2.1');

				}

				if (in_array('8.2.5', $updates)) {

					$this->addSubtitleToHeaderTags();

					$this->setSetting('system', 'lara_db_version', '8.2.5');

				}

				// Post-update actions
				$this->clearCache();

				return redirect()->route('admin.dashboard.index', ['newversion' => end($updates)])->send();
			}

		} else {

			return $updates;

		}

	}



	private function addSubtitleToHeaderTags() {

		$tablenames = config('lara-common.database');
		$tablename = $tablenames['sys']['headertags'];
		if (!Schema::hasColumn($tablename, 'subtitle_tag')) {
			Schema::table($tablename, function ($table) {
				$table->string('subtitle_tag')->nullable()->after('title_tag');
			});
		}

	}

	private function updateConfigFiles()
	{

		// lara-admin
		$laraAdminSource = base_path() . '/laracms/core/config/lara-admin.php';
		$laraAdminDest = config_path() . '/lara-admin.php';
		$laraAdminBck = config_path() . '/lara-admin-org.php';
		if (!file_exists($laraAdminBck)) {
			if (file_exists($laraAdminDest)) {
				rename($laraAdminDest, $laraAdminBck);
			}
			if (file_exists($laraAdminSource)) {
				copy($laraAdminSource, $laraAdminDest);
			}
		}

		// lara-common
		$laraCommonSource = base_path() . '/laracms/core/config/lara-common.php';
		$laraCommonDest = config_path() . '/lara-common.php';
		$laraCommonBck = config_path() . '/lara-common-org.php';
		if (!file_exists($laraCommonBck)) {
			if (file_exists($laraCommonDest)) {
				rename($laraCommonDest, $laraCommonBck);
			}
			if (file_exists($laraCommonSource)) {
				copy($laraCommonSource, $laraCommonDest);
			}
		}

	}

	private function createSeoGroup(): bool
	{

		$seoGroup = Entitygroup::where('key', 'seo')->first();

		if (empty($seoGroup)) {

			// create new group
			$seoGroup = Entitygroup::create([
				'title'                     => 'SEO',
				'key'                       => 'seo',
				'path'                      => 'Lara',
				'group_has_columns'         => 0,
				'group_has_objectrelations' => 0,
				'group_has_filters'         => 0,
				'group_has_panels'          => 0,
				'group_has_media'           => 0,
				'group_has_customcolumns'   => 0,
				'group_has_relations'       => 0,
				'group_has_views'           => 0,
				'group_has_widgets'         => 0,
				'group_has_sortable'        => 0,
				'group_has_managedtable'    => 0,
			]);
		}

		// move SEO entity
		$seoEntity = Entity::where('entity_key', 'seo')->first();
		$seoEntity->group_id = $seoGroup->id;
		$seoEntity->menu_parent = 'seo';
		$seoEntity->menu_position = '990';
		$seoEntity->save();

		// move Redirect entity
		$redirectEntity = Entity::where('entity_key', 'redirect')->first();
		$redirectEntity->group_id = $seoGroup->id;
		$redirectEntity->menu_parent = 'seo';
		$redirectEntity->menu_position = '991';
		$redirectEntity->save();

		return true;

	}

	private function addHeaderTags(): bool
	{

		$seoGroup = Entitygroup::where('key', 'seo')->first();

		if ($seoGroup) {

			$headerTagEntity = Entity::where('entity_key', 'headertag')->first();

			if (empty($headerTagEntity)) {
				// add entity
				$entity = Entity::create([
					'group_id'           => $seoGroup->id,
					'title'              => 'Headertags',
					'entity_model_class' => 'Lara\Common\Models\Headertag',
					'entity_key'         => 'headertag',
					'entity_controller'  => 'HeadertagsController',
					'resource_routes'    => 1,
					'has_front_auth'     => 0,
					'menu_parent'        => 'seo',
					'menu_position'      => '992',
					'menu_icon'          => null,
				]);

				$entity->columns()->create([
					'entity_id'      => $entity->id,
					'has_user'       => 0,
					'has_lang'       => 0,
					'has_slug'       => 0,
					'has_lead'       => 0,
					'has_body'       => 0,
					'has_status'     => 0,
					'has_hideinlist' => 0,
					'has_expiration' => 0,
					'has_app'        => 0,
					'has_groups'     => 1,
					'group_values'   => 'module, larawidget, textwidget, entitywidget, sliderwidget, ctawidget, pagetitlewidget',
					'group_default'  => null,
					'is_sortable'    => 0,
					'sort_field'     => 'id',
					'sort_order'     => 'asc',
					'sort2_field'    => null,
					'sort2_order'    => null,
					'has_fields'     => 0,
				]);

				$entity->objectrelations()->create([
					'entity_id'      => $entity->id,
					'has_seo'        => 0,
					'has_opengraph'  => 0,
					'has_layout'     => 0,
					'has_related'    => 0,
					'is_relatable'   => 0,
					'has_tags'       => 0,
					'tag_default'    => null,
					'has_sync'       => 0,
					'has_images'     => 0,
					'has_videos'     => 0,
					'has_videofiles' => 0,
					'has_files'      => 0,
					'max_images'     => 1,
					'max_videos'     => 1,
					'max_videofiles' => 1,
					'max_files'      => 1,
					'disk_images'    => 'localdisk',
					'disk_videos'    => 'localdisk',
					'disk_files'     => 'localdisk',
				]);

				$entity->panels()->create([
					'entity_id'     => $entity->id,
					'has_search'    => 0,
					'has_batch'     => 0,
					'has_filters'   => 0,
					'show_author'   => 0,
					'show_status'   => 0,
					'has_tiny_lead' => 0,
					'has_tiny_body' => 0,
				]);

			}

		}

		// add tables
		$tablenames = config('lara-common.database');

		$tablename = $tablenames['sys']['templatefiles'];
		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) {

				$table->bigIncrements('id');
				$table->string('template_file')->nullable();
				$table->string('type')->nullable();
				$table->timestamps();

			});
		}

		$tablename = $tablenames['sys']['headertags'];
		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) use ($tablenames) {

				$table->bigIncrements('id');

				$table->string('title')->nullable();
				$table->string('cgroup')->nullable();

				$table->bigInteger('templatefile_id')->unsigned();
				$table->foreign('templatefile_id')
					->references('id')
					->on($tablenames['sys']['templatefiles'])
					->onDelete('cascade');

				$table->string('title_tag')->nullable();
				$table->string('list_tag')->nullable();

				$table->timestamps();

				$table->timestamp('locked_at')->nullable();
				$table->bigInteger('locked_by')->nullable()->unsigned();

				$table->foreign('locked_by')
					->references('id')
					->on($tablenames['auth']['users'])
					->onDelete('cascade');

			});
		}

		return true;

	}

	private function updateMenuItemTable()
	{
		$tablenames = config('lara-common.database');
		$tablename = $tablenames['menu']['menuitems'];
		if (!Schema::hasColumn($tablename, 'slug_lock')) {
			Schema::table($tablename, function ($table) {
				$table->boolean('slug_lock')->default(0)->after('slug');
			});
		}
	}

	private function updateImageTable()
	{

		$tablenames = config('lara-common.database');
		$tablename = $tablenames['object']['images'];
		if (!Schema::hasColumn($tablename, 'isicon')) {
			Schema::table($tablename, function ($table) {
				$table->boolean('isicon')->default(0)->after('featured');
			});
		}

	}

	private function updateFormTranslations()
	{

		$formEntities = Entity::EntityGroupIs('form')->get();

		foreach ($formEntities as $formEntity) {

			$entityKey = $formEntity->entity_key;

			// email subject
			$subjectSource = Translation::langIs('nl')->where('module', 'lara-front')->where('cgroup', $entityKey)->where('tag', 'email')->where('key', 'subject')->first();
			if ($subjectSource) {
				$subjectTranslation = $subjectSource->value;
				$this->checkTranslation('nl', 'lara-eve', $entityKey, 'email', 'subject', $subjectTranslation, true);
			}

			// custom form fields
			$formFields = $formEntity->customcolumns()->get();
			foreach ($formFields as $formField) {
				$fieldname = $formField->fieldname;
				$fieldSource = Translation::langIs('nl')->where('module', 'lara-front')->where('cgroup', $entityKey)->where('tag', 'formfield')->where('key', $fieldname)->first();
				if ($fieldSource) {
					$fieldTranslation = $fieldSource->value;
					$this->checkTranslation('nl', 'lara-eve', $entityKey, 'formfield', $fieldname, $fieldTranslation, true);
				}
			}
		}

		$this->exportTranslationsToFile(['lara-eve']);

	}

	private function addPreventCropping()
	{

		$tablenames = config('lara-common.database');
		$tablename = $tablenames['object']['images'];
		if (!Schema::hasColumn($tablename, 'prevent_cropping')) {
			Schema::table($tablename, function ($table) {
				$table->boolean('prevent_cropping')->default(0)->after('image_alt');
			});
		}
	}

	private function fixFormSortOrder()
	{
		$formGroup = Entitygroup::where('key', 'form')->first();
		if ($formGroup) {
			$formEntities = Entity::where('group_id', $formGroup->id)->get();
			foreach ($formEntities as $formEntity) {
				$columns = $formEntity->columns;
				$columns->sort_field = 'created_at';
				$columns->sort_order = 'desc';
				$columns->save();
			}
		}
	}

	private function updateAdminMenuIcons()
	{

		// dashboard
		$dashboardEntity = Entity::where('entity_key', 'dashboard')->first();
		if ($dashboardEntity) {
			$dashboardEntity->menu_icon = 'fad fa-home-alt';
			$dashboardEntity->save;
		}

		// page
		$pageEntity = Entity::where('entity_key', 'page')->first();
		if ($pageEntity) {
			$pageEntity->menu_icon = 'fad fa-file-alt';
			$pageEntity->save();
		}

		// blog
		$blogEntity = Entity::where('entity_key', 'blog')->first();
		if ($blogEntity) {
			$blogEntity->menu_icon = 'fad fa-file-alt';
			$blogEntity->save;
		}

	}

	private function addIndexToSlugColumn()
	{

		$ents = Entity::where('group_id', 9)->get();
		foreach ($ents as $ent) {
			if ($ent->columns->has_slug == 1) {

				$lara = $this->getEntityVarByKey($ent->entity_key);
				$entity = new $lara;

				$modelClass = $entity->getEntityModelClass();
				$tablename = $modelClass::getTableName();
				$columnName = 'slug';
				$indexName = $tablename . '_' . $columnName . '_index';

				Schema::table($tablename, function (Blueprint $table) use ($tablename, $columnName, $indexName) {
					$sm = Schema::getConnection()->getDoctrineSchemaManager();
					$indexesFound = $sm->listTableIndexes($tablename);

					if (!array_key_exists($indexName, $indexesFound)) {
						$table->index($columnName);
					}
				});
			}
		}
	}

	private function addImageTranslations()
	{

		$this->checkTranslation('nl', 'lara-admin', 'default', 'label', 'prevent_cropping', 'voorkom afsnijden', true);
		$this->checkTranslation('en', 'lara-admin', 'default', 'label', 'prevent_cropping', 'prevent cropping', true);

		$this->exportTranslationsToFile(['lara-admin']);

	}

	private function adduserProfiles()
	{
		$tablenames = config('lara-common.database');
		$tablename = $tablenames['auth']['profiles'];
		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) use ($tablenames) {

				$table->increments('id');
				$table->integer('user_id')->unsigned();
				$table->boolean('dark_mode')->default(0);

				$table->timestamps();

				// foreign keys
				$table->foreign('user_id')
					->references('id')
					->on($tablenames['auth']['users'])
					->onDelete('cascade');

			});
		}
	}

	private function addVideoFiles()
	{

		$tablenames = config('lara-common.database');

		$tablename = $tablenames['ent']['entityobjectrelations'];
		if (!Schema::hasColumn($tablename, 'has_videofiles')) {
			Schema::table($tablename, function ($table) {
				$table->boolean('has_videofiles')->default(0)->after('has_videos');
			});
		}
		if (!Schema::hasColumn($tablename, 'max_videofiles')) {
			Schema::table($tablename, function ($table) {
				$table->integer('max_videofiles')->unsigned()->default(1);
			});
		}

		$tablename = $tablenames['sys']['uploads'];
		if (!Schema::hasColumn($tablename, 'dz_session_id')) {
			Schema::table($tablename, function ($table) {
				$table->string('dz_session_id')->nullable();
			});
		}

		$tablename = $tablenames['object']['videofiles'];
		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) {

				$table->increments('id');

				$table->string('entity_type')->nullable();
				$table->integer('entity_id')->unsigned();

				$table->string('title')->nullable();

				$table->string('cfs_uid')->nullable();
				$table->boolean('cfs_ready')->default(0);
				$table->integer('cfs_thumb_offset')->unsigned()->default(0);

				$table->text('filename')->nullable();
				$table->string('mimetype')->nullable();

				$table->boolean('featured')->default(0);

				$table->timestamps();

			});
		}

	}

	private function addEmailVerfication()
	{

		$tablenames = config('lara-common.database');

		$tablename = $tablenames['auth']['users'];
		if (!Schema::hasColumn($tablename, 'email_verified_at')) {
			Schema::table($tablename, function ($table) {
				$table->timestamp('email_verified_at')->nullable()->after('remember_token');
			});
		}

		DB::table($tablename)->where('type', 'web')->update([
			'email_verified_at' => '2021-01-01 12:00:00',
		]);

	}

	private function addBlackList()
	{

		$tablenames = config('lara-common.database');
		$tablename = $tablenames['sys']['blacklist'];
		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) use ($tablenames) {
				$table->increments('id');
				$table->string('ipaddress')->nullable();
				$table->timestamps();
			});
		}

		// add ipadress column to all form tables
		$formEntities = Entity::EntityGroupIs('form')->get();
		foreach ($formEntities as $formEntity) {
			$modelClass = $formEntity->getEntityModelClass();

			if (class_exists($modelClass)) {
				$tablename = $modelClass::getTableName();
				if (!Schema::hasColumn($tablename, 'ipaddress')) {
					Schema::table($tablename, function ($table) {
						$table->string('ipaddress')->nullable();
					});
				}
			}

		}

	}

	private function addDynamicDisks()
	{

		if (!Schema::hasColumn('lara_ent_entity_object_relations', 'disk_images')) {
			Schema::table('lara_ent_entity_object_relations', function ($table) {
				$table->string('disk_images')->nullable();
			});
		}
		if (!Schema::hasColumn('lara_ent_entity_object_relations', 'disk_videos')) {
			Schema::table('lara_ent_entity_object_relations', function ($table) {
				$table->string('disk_videos')->nullable();
			});
		}
		if (!Schema::hasColumn('lara_ent_entity_object_relations', 'disk_files')) {
			Schema::table('lara_ent_entity_object_relations', function ($table) {
				$table->string('disk_files')->nullable();
			});
		}

		$defaultDisk = config('lara-admin.upload_disks.default');

		DB::table('lara_ent_entity_object_relations')->where('has_images', 1)->update([
			'disk_images' => $defaultDisk,
			'disk_videos' => $defaultDisk,
			'disk_files'  => $defaultDisk,
		]);

	}

	private function addHideInLIst()
	{

		if (!Schema::hasColumn('lara_ent_entity_columns', 'has_hideinlist')) {
			Schema::table('lara_ent_entity_columns', function ($table) {
				$table->boolean('has_hideinlist')->default(0)->after('has_status');
			});
		}

		$entities = Entity::EntityGroupIs('entity')->get();
		foreach ($entities as $entity) {

			$cols = $entity->columns;
			$cols->has_hideinlist = 1;
			$cols->save();

			$modelClass = $entity->getEntityModelClass();
			$tablename = $modelClass::getTableName();
			if ($entity->columns->has_status) {
				if (!Schema::hasColumn($tablename, 'publish_hide')) {
					Schema::table($tablename, function ($table) {
						$table->boolean('publish_hide')->default(0)->after('publish');
					});
				}
			}
		}

		$this->checkTranslation('nl', 'lara-admin', 'default', 'column', 'publish_hide', 'verberg in lijst', true);
		$this->checkTranslation('en', 'lara-admin', 'default', 'column', 'publish_hide', 'hide in list', true);

		$this->exportTranslationsToFile(['lara-admin']);

	}

	private function addDocsTranslations()
	{

		$this->checkTranslation('nl', 'lara-admin', 'default', 'message', 'docs_not_published_yet', 'Let op: dit document is nog niet gepubliceerd. Ga naar het tabblad \'Content\' om het document te publiceren.', true);
		$this->checkTranslation('en', 'lara-admin', 'default', 'message', 'docs_not_published_yet', 'Note: this document has not been published yet. Go to the \'Content\' tab to publish this document.', true);

		$this->exportTranslationsToFile(['lara-admin']);

	}

	private function updatePermissions()
	{
		$results = DB::table('lara_auth_abilities')
			->whereRaw("BINARY entity_type = 'Lara\\\Common\\\Models\\\MenuItem'")->get();

		foreach ($results as $result) {
			$model = Ability::find($result->id);
			$model->entity_type = 'Lara\\Common\\Models\\Menuitem';
			$model->save();
		}

		// add contactform permissions to webmaster
		$entity = Entity::where('entity_key', 'contactform')->first();
		if ($entity) {
			$modelClass = $entity->entity_model_class;
			$role = Role::where('name', 'webmaster')->first();
			if ($role) {
				$abilities = config('lara-admin.abilities');
				foreach ($abilities as $ability) {
					Bouncer::allow('webmaster')->to($ability, $modelClass);
				}
			}

			// make contactform comment field primary
			$commentField = $entity->customcolumns()->where('fieldname', 'comment')->first();
			if ($commentField) {
				$commentField->primary = 1;
				$commentField->save();
			}

		}

	}

	private function scanContactFormsForSpam()
	{

		$entity = Entity::where('entity_key', 'contactform')->first();
		if ($entity) {

			$patterns = config('lara.detect_link_patterns');
			$regexp = '/([a-z0-9_\.\-])+(\@|\[at\])+(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';

			$entity = new \Eve\Lara\ContactformEntity;

			foreach ($entity->getCustomColumns() as $field) {
				if ($field->fieldtype == 'text') {

					$modelClass = $entity->getEntityModelClass();
					$objects = $modelClass::all();

					foreach ($objects as $object) {

						$stringHasLinks = false;
						$stringHasEmail = false;

						$fieldname = $field->fieldname;
						$fieldval = $object->$fieldname;

						// detect links
						foreach ($patterns as $pattern) {
							if (Str::contains($fieldval, $pattern)) {
								$stringHasLinks = true;
							}
						}

						// detect email
						preg_match_all($regexp, $fieldval, $m);
						if (sizeof($m[0]) > 0) {
							$stringHasEmail = true;
						}

						$matchLanguage = $this->checkAllowedLanguages($entity, $object);

						if ($stringHasLinks || $stringHasEmail || !$matchLanguage) {
							$object->$fieldname = '[SPAM] - ' . $fieldval;
							$object->save();
							$object->delete();
						}

					}

				}
			}

		}

	}

	/**
	 * @return void
	 */
	private function fixWidgetsColumn()
	{

		// fix entity
		$entity = Entity::where('entity_key', 'larawidget')->first();
		if ($entity) {
			$customcol = $entity->customcolumns->where('fieldname', 'filtertag')->first();
			if ($customcol) {
				$customcol->fieldname = 'term';
				$customcol->fieldtitle = 'term';
				$customcol->save();
			}
		}

		// fix larawidget table column
		$tablename = 'lara_blocks_larawidgets';
		$from = 'filtertag';
		$to = 'term';
		if (Schema::hasColumn($tablename, $from)) {
			Schema::table($tablename, function ($table) use ($from, $to) {
				$table->renameColumn($from, $to);
			});
		}

		$tablename = 'lara_blocks_larawidgets';
		$from = 'filtertaxonomy';
		$to = 'term';
		if (Schema::hasColumn($tablename, $from)) {
			Schema::table($tablename, function ($table) use ($from, $to) {
				$table->renameColumn($from, $to);
			});
		}

	}

	/**
	 * @return void
	 */
	private function addHideInGalleryToImages()
	{

		$tablename = 'lara_object_images';
		$colname = 'hide_in_gallery';
		if (!Schema::hasColumn($tablename, $colname)) {
			Schema::table($tablename, function ($table) use ($colname) {
				$table->boolean('hide_in_gallery')->default(0)->after('herosize');
			});
		}

		// set all features and hero images to value 1
		MediaImage::where('featured', 1)->update(['hide_in_gallery' => 1]);
		MediaImage::where('ishero', 1)->update(['hide_in_gallery' => 1]);

	}

	private function addTaxonomy()
	{

		$tablenames = config('lara-common.database');
		$tablename = $tablenames['object']['taxonomy'];

		if (!Schema::hasTable($tablename)) {
			Schema::create($tablename, function (Blueprint $table) use ($tablenames) {

				// ID's
				$table->increments('id');

				$table->string('title')->nullable();
				$table->string('slug')->nullable();
				$table->boolean('slug_lock')->default(0);

				$table->boolean('has_hierarchy')->default(0);
				$table->boolean('is_default')->default(0);

				// timestamp
				$table->timestamps();

				// record lock
				$table->timestamp('locked_at')->nullable();
				$table->integer('locked_by')->nullable()->unsigned();

				// sortable
				$table->integer('position')->unsigned()->nullable()->index();

				// foreign keys
				$table->foreign('locked_by')
					->references('id')
					->on($tablenames['auth']['users'])
					->onDelete('cascade');

			});

			Taxonomy::create([
				'title'         => 'Category',
				'slug'          => 'category',
				'slug_lock'     => 1,
				'has_hierarchy' => 1,
				'is_default'    => 1,
			]);

			Taxonomy::create([
				'title'         => 'Tag',
				'slug'          => 'tag',
				'slug_lock'     => 1,
				'has_hierarchy' => 0,
				'is_default'    => 0,
			]);

		}

		$tablename = 'lara_object_tags';
		$colname = 'taxonomy_id';
		if (!Schema::hasColumn($tablename, $colname)) {
			Schema::table($tablename, function ($table) use ($colname, $tablenames) {
				$table->integer($colname)->unsigned()->default('1');
				$table->foreign($colname)
					->references('id')
					->on($tablenames['object']['taxonomy'])
					->onDelete('cascade');

			});

			Tag::query()->update(['taxonomy_id' => 1]);
		}

		// update views
		EntityView::query()->where('showtags', '_sortbytag')->update(['showtags' => '_sortbytaxonomy']);

	}

	/**
	 * @return void
	 */
	private function addPositionToImages()
	{

		$tablename = 'lara_object_images';
		$colname = 'position';
		if (!Schema::hasColumn($tablename, $colname)) {
			Schema::table($tablename, function ($table) use ($colname) {
				$table->integer($colname)->unsigned()->nullable()->index();
			});
		}
	}

	/**
	 * @return void
	 */
	private function addPrevNextToViews()
	{
		$tablename = 'lara_ent_entity_views';
		$colname = 'prevnext';
		if (!Schema::hasColumn($tablename, $colname)) {
			Schema::table($tablename, function ($table) use ($colname) {
				$table->boolean($colname)->default(0)->after('paginate');
			});
		}

	}

	/**
	 * @return void
	 */
	private function addInfiniteToViews()
	{
		$tablename = 'lara_ent_entity_views';
		$colname = 'infinite';
		if (!Schema::hasColumn($tablename, $colname)) {
			Schema::table($tablename, function ($table) use ($colname) {
				$table->boolean($colname)->default(0)->after('paginate');
			});
		}

	}

	/**
	 * @return void
	 */
	private function addHeroImage()
	{

		$tablename = 'lara_object_images';
		if (!Schema::hasColumn($tablename, 'ishero')) {
			Schema::table($tablename, function ($table) {
				$table->boolean('ishero')->default(0)->after('featured');
			});
		}

		$tablename = 'lara_object_images';
		if (!Schema::hasColumn($tablename, 'herosize')) {
			Schema::table($tablename, function ($table) {
				$table->integer('herosize')->unsigned()->default(0)->after('ishero');
			});
		}

	}

	/**
	 * @return void
	 */
	private function fixLaraVersionKey()
	{

		$modelClass = \Lara\Common\Models\Setting::class;

		$object = $modelClass::where('cgroup', 'system')
			->where('key', 'lara_version')
			->first();

		if ($object) {
			// remove legacy version key
			$object->delete();
		}

	}

	/**
	 * @param string $entity_type
	 * @param int $entity_id
	 * @param string $layout_key
	 * @param string $layout_value
	 * @return bool
	 */
	private function setLayoutItem(string $entity_type, int $entity_id, string $layout_key, string $layout_value)
	{

		$result = DB::table('lara_object_layout')
			->updateOrInsert(
				[
					'entity_type' => $entity_type,
					'entity_id'   => $entity_id,
					'layout_key'  => $layout_key,
				],
				[
					'layout_value' => $layout_value,
				]
			);

		return $result;

	}

	/**
	 * @return void
	 */
	private function addIndexToPivotTable()
	{

		$tablename = 'lara_object_taggables';
		if (!Schema::hasColumn($tablename, 'id')) {
			Schema::table($tablename, function ($table) {
				$table->increments('id')->first();
			});
		}

		$tablename = 'lara_object_pageables';
		if (!Schema::hasColumn($tablename, 'id')) {
			Schema::table($tablename, function ($table) {
				$table->increments('id')->first();
			});
		}

	}

	/**
	 * @return void
	 */
	private function updateTranslationTable()
	{

		$translations = Translation::get();

		foreach ($translations as $translation) {
			$module = $translation->module;
			$prefix = substr($module, 0, 5);
			if ($prefix != 'lara-') {
				// add prefix
				$newModule = 'lara-' . $module;
				$translation->module = $newModule;
				$translation->save();
			}
		}

	}

	/**
	 * @return mixed
	 */
	private function getVersionFromSettings()
	{

		// current version
		$currentBuild = Setting::where('cgroup', 'system')->where('key', 'lara_db_version')->first();

		if (empty($currentBuild)) {

			$laraVersion = config('lara.lara_db_version');

			$currentBuild = Setting::create([
				'title'  => 'Lara Version',
				'cgroup' => 'system',
				'key'    => 'lara_db_version',
				'value'  => $laraVersion,

			]);
		}

		return $currentBuild->value;

	}

	/**
	 * @return void
	 */
	private function clearCache()
	{

		File::cleanDirectory(storage_path('framework/cache/data'));
		File::delete(base_path('bootstrap/cache/config.php'));
		File::cleanDirectory(storage_path('framework/views'));
		File::cleanDirectory(storage_path('httpcache'));

	}

	/**
	 * @param string $language
	 * @param string $module
	 * @param string $cgroup
	 * @param string $tag
	 * @param string $key
	 * @param string $value
	 * @param bool $force
	 * @return bool
	 */
	private function checkTranslation(string $language, string $module, string $cgroup, string $tag, string $key, string $value, $force = false)
	{

		$trans = Translation::where('language', $language)
			->where('module', $module)
			->where('cgroup', $cgroup)
			->where('tag', $tag)
			->where('key', $key)
			->first();

		$change = false;

		if ($trans) {

			// check value
			if ($trans->value != $value) {
				if (substr($trans->value, 0, 1) == '_' || $force) {
					$trans->value = $value;
					$trans->save();
					$change = true;
				}
			}

		} else {

			Translation::create([
				'language' => $language,
				'module'   => $module,
				'cgroup'   => $cgroup,
				'tag'      => $tag,
				'key'      => $key,
				'value'    => $value,
			]);

			$change = true;

		}

		return $change;

	}

	private function checkAllowedLanguages(object $entity, $object): bool
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

}
