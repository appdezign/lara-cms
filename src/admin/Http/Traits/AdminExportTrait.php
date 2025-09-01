<?php

namespace Lara\Admin\Http\Traits;

use Lara\Common\Models\Entity;

trait AdminExportTrait
{

	private function buildCsVContent($ent) {

		$entityKey = $ent->entity_key;
		$laraClass = $this->getEntityVarByKey($entityKey);
		$entity = new $laraClass;

		$entityExport = $this->getExport($ent->id);
		$export = $entityExport->$entityKey;

		// get content
		$exportColumns = array();
		$select = array();
		$select[] = 'id';
		foreach ($export->columns as $key => $column) {
			if ($column['export'] === true) {
				unset($column['group']);
				unset($column['export']);
				$exportColumns[$key] = $column;
				$select[] = $column['fieldname'];
			}
		}
		$export->columns = $exportColumns;

		$modelClass = $entity->getEntityModelClass();

		$objects = $modelClass::select($select)->langIs($this->mainLanguage)->get()->toArray();

		$csv = $this->formatCsvContent($select, $objects);

		// see: http://blog.programovani.net/en/php/special-characters-export-to-csv/
		$csv = chr(0xEF) . chr(0xBB) . chr(0xBF) . $csv;

		return $csv;

	}

	private function formatCsvContent($select, $objects)
	{

		$delimiter = ',';
		$enclosure = '"';
		$eol = "\n";

		$content = '';

		// header row
		$headerCols = config('laravellocalization.localesOrder');
		array_unshift($headerCols , 'Fields');

		$i = 1;
		$hline = '';
		foreach ($headerCols as $headerCol) {
			$headerColStr = ($i > 1) ? strtoupper($headerCol) : $headerCol;
			$hline .= $enclosure . $headerColStr . $enclosure;
			if ($i < sizeof($headerCols)) {
				$hline .= $delimiter;
			}
			$i++;
		}
		$content .= trim($hline) . $eol;

		foreach ($objects as $object) {

			$hasContent = false;
			foreach ($select as $col) {
				if (!in_array($col, ['id', 'title'])) {
					if (!empty($object[$col])) {
						$hasContent = true;
					}
				}
			}

			if ($hasContent) {
				foreach ($select as $col) {
					$contenStr = $object[$col];
					if (!empty($contenStr)) {
						$contenStr = $this->cleanUpContent($contenStr);
						$line = $enclosure . $col . $enclosure . $delimiter;
						$line .= $enclosure . $contenStr . $enclosure;
						$content .= trim($line) . $eol;
					}
				}
				$content .= $eol;
			}

		}

		return $content;
	}

	private function getExport($entityId = null)
	{

		$configExportFields = json_decode(json_encode(config('lara-eve.export_language_content')), false);

		if (empty($configExportFields)) {
			return array();
		}

		$export = $this->makeNewObject();

		if ($entityId) {
			$entity = Entity::find($entityId);
			if ($entity) {
				$entityKey = $entity->entity_key;
				if (property_exists($configExportFields, $entityKey)) {
					$exportColumns = $this->getExportObjects($entity, $configExportFields);
				} else {
					$exportColumns = array();
				}
				$export->$entityKey = $this->makeNewObject();
				$export->$entityKey->id = $entity->id;
				$export->$entityKey->entity_key = $entity->entity_key;
				$export->$entityKey->columns = $exportColumns;
			}
		} else {
			$entities = Entity::EntityGroupIsOneOf(['page', 'entity'])->whereNotNull('menu_position')->get();
			foreach ($entities as $entity) {
				$entityKey = $entity->entity_key;
				if (property_exists($configExportFields, $entityKey)) {
					$exportColumns = $this->getExportObjects($entity, $configExportFields);
				} else {
					$exportColumns = array();
				}
				$export->$entityKey = $this->makeNewObject();
				$export->$entityKey->id = $entity->id;
				$export->$entityKey->entity_key = $entity->entity_key;
				$export->$entityKey->columns = $exportColumns;
			}

			$entities = Entity::EntityGroupIs('block')->whereNotNull('menu_position')->get();
			foreach ($entities as $entity) {
				$entityKey = $entity->entity_key;
				if (property_exists($configExportFields, $entityKey)) {
					$exportColumns = $this->getExportObjects($entity, $configExportFields);
				} else {
					$exportColumns = array();
				}
				$export->$entityKey = $this->makeNewObject();
				$export->$entityKey->id = $entity->id;
				$export->$entityKey->entity_key = $entity->entity_key;
				$export->$entityKey->columns = $exportColumns;
			}

		}

		return $export;
	}

	private function getExportObjects($entity, $configExportFields)
	{

		$entityKey = $entity->entity_key;

		// check if entity has content
		$modelClass = $entity->entity_model_class;
		if ($modelClass::count() > 0) {

			$entityExportColumns = array();

			// Add standard fields
			$entityColumns = $entity->columns()->first();

			$totalRows = $this->getTotalRows($modelClass);
			$contentRows = $this->getContentRows($modelClass, 'title');
			$entityExportColumns['title'] = [
				'group'       => 'standard',
				'fieldname'   => 'title',
				'fieldtype'   => 'string',
				'export'      => true,
				'totalrows'   => $totalRows,
				'contentrows' => $contentRows,
			];

			$exportLead = $entityColumns->has_lead == 1;
			$totalRows = ($exportLead) ? $this->getTotalRows($modelClass) : null;
			$contentRows = ($exportLead) ? $this->getContentRows($modelClass, 'lead') : null;

			$entityExportColumns['lead'] = [
				'group'       => 'standard',
				'fieldname'   => 'lead',
				'fieldtype'   => 'text',
				'export'      => $exportLead,
				'totalrows'   => $totalRows,
				'contentrows' => $contentRows,
			];

			$exportBody = $entityColumns->has_body == 1;
			$totalRows = ($exportBody) ? $this->getTotalRows($modelClass) : null;
			$contentRows = ($exportBody) ? $this->getContentRows($modelClass, 'body') : null;
			$entityExportColumns['body'] = [
				'group'       => 'standard',
				'fieldname'   => 'body',
				'fieldtype'   => 'text',
				'export'      => $exportBody,
				'totalrows'   => $totalRows,
				'contentrows' => $contentRows,
			];

			// Add custom fields

			if (property_exists($configExportFields, $entityKey)) {

				$configExportEntityFields = $configExportFields->$entityKey;

				$customcolumns = $entity->customcolumns()->get();
				foreach ($customcolumns as $col) {
					if (in_array($col->fieldtype, ['string', 'text', 'mcefull', 'mcemin'])) {
						$exportCol = in_array($col->fieldname, $configExportEntityFields);
						$totalRows = $this->getTotalRows($modelClass);
						$contentRows = $this->getContentRows($modelClass, $col->fieldname);
						$entityExportColumns[$col->fieldname] = [
							'group'       => 'custom',
							'fieldname'   => $col->fieldname,
							'fieldtype'   => $col->fieldtype,
							'export'      => $exportCol,
							'totalrows'   => $totalRows,
							'contentrows' => $contentRows,
						];
					}
				}
			}

			return $entityExportColumns;

		}

	}

	private function getContentRows($modelClass, $col)
	{

		return $modelClass::langIs($this->mainLanguage)->whereNotNull($col)->where($col, '<>', '')->count();

	}

	private function getTotalRows($modelClass)
	{

		return $modelClass::langIs($this->mainLanguage)->count();

	}

	private function cleanUpContent($str)
	{


		// remove all EOL from html before we strp the tags

		// remove extra line breaks
		$str = str_replace('<p>&nbsp;</p>', '', $str);

		// strip all tags
		$str = strip_tags($str);


		$shcodes = [
			'[kolom_1van2]',
			'[kolom_2van2]',

			'[kolom_1van3]',
			'[kolom_2van3]',
			'[kolom_3van3]',

			'[kolom_1van4]',
			'[kolom_2van4]',
			'[kolom_3van4]',
			'[kolom_4van4]',

			'[kolom_1_van_3]',
			'[kolom_12_van_3]',
			'[kolom_23_van_3]',
			'[kolom_3_van_3]',

			'[kolom_1_van_4]',
			'[kolom_123_van_4]',
			'[kolom_234_van_4]',
			'[kolom_4_van_4]',
		];

		// add end tags
		$endtags = array();
		foreach ($shcodes as $shcode) {
			$endtags[] = '[/' . substr($shcode, 1);
		}

		$shortcodes = array_merge($shcodes, $endtags);

		foreach ($shortcodes as $shortcode) {
			$str = str_replace($shortcode, '', $str);
		}


		$str = html_entity_decode($str);

		$extra = [
			"\u{A0}" => "",
			"\r\n" => "\n",
		];
		foreach ($extra as $s => $r) {
			$str = str_replace($s, $r, $str);
		}

		$str = $this->removeDoubleLineBreaks($str);

		return $str;

	}

	private function removeDoubleLineBreaks($str) {

		$needle = "\n\n";
		$replace = "\n";

		$pos = strpos($str, $needle);
		if ($pos === false) {
			return $str;
		} else {
			$str = str_replace($needle, $replace, $str);
			return $this->removeDoubleLineBreaks($str);
		}

	}

}

