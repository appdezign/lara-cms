<?php

namespace Lara\Common\Http\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Entitygroup;
use Lara\Common\Models\Translation;
use Lara\Common\Models\Language;
use Lara\Common\Models\Headertag;

use Bouncer;

trait CommonDbUpdateTrait
{

	/**
	 * @return false|string|null
	 */
	private function checkForLaraUpdates()
	{

		$builds = [
			'7.1.4',
			'7.5.11',
			'7.5.36',
			'7.5.46',
			'8.1.11',
			'8.2.1',
			'8.2.5',
			'8.2.10',
			'8.2.11',
		];

		// current versions
		$databaseVersion = $this->getCommonLaraDBVersion();
		$composerVersion = $this->getCommonLaraVersion();
		$laraVersion = $composerVersion->version;

		$updates = array();

		foreach ($builds as $build) {
			if (version_compare($build, $databaseVersion, '>') && version_compare($build, $laraVersion, '<=')) {
				$updates[] = $build;
			}
		}

		if (!empty($updates)) {

			/* ~~~~~~~~~~~~ UPDATES ~~~~~~~~~~~~ */

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

			if (in_array('8.2.10', $updates)) {

				$this->addHeadertagTranslations();

				$this->setSetting('system', 'lara_db_version', '8.2.10');

			}

			if (in_array('8.2.11', $updates)) {

				$this->addBackendLanguages();

				$this->setSetting('system', 'lara_db_version', '8.2.11');

			}

			// Post-update actions
			$this->clearCache();

			return end($builds);

		} else {

			return null;

		}

	}

	private function addBackendLanguages()
	{

		$tablenames = config('lara-common.database');
		$tablename = $tablenames['sys']['languages'];
		if (!Schema::hasColumn($tablename, 'backend')) {
			Schema::table($tablename, function ($table) {
				$table->boolean('backend')->default(0)->after('default');
			});
		}
		if (!Schema::hasColumn($tablename, 'backend_default')) {
			Schema::table($tablename, function ($table) {
				$table->boolean('backend_default')->default(0)->after('backend');
			});
		}
		if (!Schema::hasColumn($tablename, 'updated_at')) {
			Schema::table($tablename, function ($table) {
				$table->timestamps();
			});
		}

		$nl = Language::where('code', 'nl')->first();
		if($nl) {
			$nl->backend = 1;
			$nl->backend_default = 1;
			$nl->save();
		}

		$en = Language::where('code', 'en')->first();
		if($en) {
			$en->backend = 1;
			$en->backend_default = 0;
			$en->save();
		}

	}

	private function addHeadertagTranslations()
	{

		$this->checkTranslation('nl', 'lara-admin', 'mainmenu', 'items', 'headertags', 'header tags', true);

		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'entity', 'entity_title', 'header tags', true);
		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'entity', 'entity_single', 'header tag', true);
		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'entity', 'entity_plural', 'header tags', true);

		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'column', 'title', 'titel', true);
		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'column', 'cgroup', 'groep', true);
		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'column', 'templatefile_id', 'template file id', true);
		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'column', 'title_tag', 'title tag', true);
		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'column', 'list_tag', 'list tag', true);
		$this->checkTranslation('nl', 'lara-admin', 'headertag', 'column', 'subtitle_tag', 'subtitle tag', true);

		$this->exportTranslationsToFile(['lara-admin']);
	}

	private function addSubtitleToHeaderTags()
	{

		$tablenames = config('lara-common.database');
		$tablename = $tablenames['sys']['headertags'];
		if (!Schema::hasColumn($tablename, 'subtitle_tag')) {
			Schema::table($tablename, function ($table) {
				$table->string('subtitle_tag')->nullable()->after('title_tag');
			});
		}

		// add default values fo existing rows
		$headerTags = Headertag::get();
		foreach($headerTags as $htag) {
			if(empty($htag->subtitle_tag)) {
				$htag->subtitle_tag = ($htag->cgroup == 'module') ? 'h2' : 'h3';
				$htag->save();
			}
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

				$lara = $this->getCommonEntityVarByKey($ent->entity_key);
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

}
