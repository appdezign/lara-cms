<?php

namespace Lara\Common\Http\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Lara\Common\Models\Entity;
use Lara\Common\Models\Translation;

trait LaraUpdate
{

	/**
	 * @return void
	 */
	private function preUpdate()
	{

		$ds = DIRECTORY_SEPARATOR;

		$basePath = explode('/', base_path());
		$vhost = array_slice($basePath, 0, 5);
		$importBase = implode('/', $vhost);

		// remove old backups
		$importdirs = config('lara-common.import_on_update.dirs');
		if ($importdirs) {
			foreach ($importdirs as $importdir) {
				$folder = $importdir['folder'];
				if ($folder) {
					$dest = $importBase . $ds . $importdir['dest'];
					$backup = $importdir['backup'];
					if ($backup) {
						$oldbackup = $dest . '_' . $folder;
						if (file_exists($oldbackup)) {
							delete_directory($oldbackup);
						}
					}
				}
			}
		}

	}

	/**
	 * @return void
	 */
	private function fixSettings()
	{
		$tablename = 'lara_sys_settings';
		if (!Schema::hasColumn($tablename, 'locked_by_admin')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->boolean('locked_by_admin')->default(0);
			});
		}
	}

	/**
	 * @return void
	 */
	private function migrateAuth()
	{

		// abilities
		$tablename = 'lara_auth_abilities';
		if (!Schema::hasColumn($tablename, 'options') && !Schema::hasColumn($tablename, 'scope')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->longText('options')->nullable()->after('only_owned');
				$table->integer('scope')->nullable()->index()->after('options');
			});
		}

		$this->fixModelClass($tablename, 'entity_type', 'id');

		// has_abilities
		$tablename = 'lara_auth_has_abilities';
		if (!Schema::hasColumn($tablename, 'id')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->increments('id')->first();
			});
		}
		if (!Schema::hasColumn($tablename, 'scope')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->integer('scope')->nullable()->index();
			});
		}
		// fix entity_type
		DB::table('lara_auth_has_abilities')->update([
			'entity_type' => 'lara_auth_roles',
		]);

		// has_roles
		$tablename = 'lara_auth_has_roles';
		if (!Schema::hasColumn($tablename, 'restricted_to_id') && !Schema::hasColumn($tablename, 'restricted_to_type') && !Schema::hasColumn($tablename, 'scope')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->integer('restricted_to_id')->unsigned()->nullable();
				$table->string('restricted_to_type')->nullable();
				$table->integer('scope')->nullable()->index();
			});
		}

		$this->fixModelClass($tablename, 'entity_type', 'role_id');

		// roles
		$tablename = 'lara_auth_roles';
		if (!Schema::hasColumn($tablename, 'scope')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->integer('scope')->nullable()->index()->after('level');
			});
		}

	}

	/**
	 * @return void
	 */
	private function migrateEntityGroups()
	{

		$tablename = 'lara_ent_entity_groups';
		$backupname = 'zz_' . $tablename;

		// create backup
		$this->cloneTable($tablename, $backupname);

		// rename columns
		$this->renameColumn($tablename, 'group_has_settings', 'group_has_columns');
		$this->renameColumn($tablename, 'group_has_options', 'group_has_objectrelations');
		$this->renameColumn($tablename, 'group_has_fields', 'group_has_customcolumns');

		// add columns
		$this->addColumn($tablename, 'path', 'string', 'key');
		$this->addColumn($tablename, 'group_has_filters', 'boolean', 'group_has_customcolumns');
		$this->addColumn($tablename, 'group_has_panels', 'boolean', 'group_has_filters');
		$this->addColumn($tablename, 'group_has_managedtable', 'boolean', 'group_has_media');

		// drop columns
		$this->dropColumn($tablename, 'is_content');
		$this->dropColumn($tablename, 'is_form');
		$this->dropColumn($tablename, 'is_default');

		// rename entity
		DB::table($tablename)->where('key', 'content')->update([
			'title' => 'Page',
			'key'   => 'page',
		]);

		// add entity

		DB::table('lara_ent_entity_groups')
			->updateOrInsert(
				[
					'title'                     => 'Entity',
					'key'                       => 'entity',
					'path'                      => 'Eve',
					'group_has_columns'         => 1,
					'group_has_objectrelations' => 1,
					'group_has_customcolumns'   => 1,
					'group_has_filters'         => 1,
					'group_has_panels'          => 1,
					'group_has_media'           => 1,
					'group_has_relations'       => 1,
					'group_has_views'           => 1,
					'group_has_widgets'         => 0,
					'group_has_sortable'        => 1,
					'group_has_managedtable'    => 1,
					'position'                  => 9,
				],
				[
					'key' => 'entity',
				]
			);

		// set path
		DB::table($tablename)->whereNotIn('key', ['form', 'entity'])->update([
			'path' => 'Lara',
		]);
		DB::table($tablename)->whereIn('key', ['form', 'entity'])->update([
			'path' => 'Eve',
		]);

		// set filter
		DB::table($tablename)->whereIn('key', ['page', 'block', 'tools', 'entity'])->update([
			'group_has_filters' => 1,
		]);

		// set panels
		DB::table($tablename)->whereIn('key', ['page', 'block', 'tag', 'entity'])->update([
			'group_has_panels' => 1,
		]);

		// set managedtable
		DB::table($tablename)->whereIn('key', ['page', 'block', 'form', 'entity'])->update([
			'group_has_managedtable' => 1,
		]);

		// move entities to Entity Group
		DB::table('lara_ent_entities')->where('group_id', 1)->where('entity_key', '!=', 'page')->update([
			'group_id' => 9,
		]);

	}

	/**
	 * @return void
	 */
	private function migrateEntities()
	{

		$this->createNewEntityTables();

		$tablename = 'lara_ent_entities';

		$entities = DB::table($tablename)->get();

		foreach ($entities as $entity) {

			// get legacy
			$group = $this->getEntityGroup($entity->group_id);
			$options = $this->getLegacyOptions($entity->id);
			$settings = $this->getLegacySettings($entity->id);
			$fields = $this->getLegacyFields($entity->id);

			$this->updateEntityColumns($entity, $group, $options, $settings);
			$this->updateEntityObjectRelations($entity, $group, $options, $settings);
			$this->updateEntityPanels($entity, $group, $options, $settings);
			$this->updateCustomColumnsRelations($entity, $fields);

		}

		// fix model class
		$this->fixModelClass('lara_ent_entities', 'entity_model_class', 'id');

		// make pages sortable
		$pageEntity = DB::table('lara_ent_entities')->where('entity_key', 'page')->first();
		DB::table('lara_ent_entity_columns')->where('entity_id', $pageEntity->id)->update([
			'is_sortable' => 1,
		]);

		// fix unlicenced
		DB::table('lara_ent_entities')->whereIn('entity_key', ['portfolio', 'sponsor', 'product'])->update([
			'menu_position' => null,
		]);

		// move blog to modules menu
		DB::table('lara_ent_entities')->where('entity_key', 'blog')->update([
			'menu_parent' => 'modules',
		]);

		// fix widget status
		$blockGroup = DB::table('lara_ent_entity_groups')->where('key', 'block')->first();
		if ($blockGroup) {
			$blockEntities = DB::table('lara_ent_entities')->where('group_id', $blockGroup->id)->get();
			foreach ($blockEntities as $blockEntity) {
				DB::table('lara_ent_entity_columns')->where('entity_id', $blockEntity->id)->update([
					'has_status' => 1,
				]);
				DB::table('lara_ent_entity_panels')->where('entity_id', $blockEntity->id)->update([
					'show_status' => 1,
				]);
			}
		}

		// fix group_values
		$entityColumns = DB::table('lara_ent_entity_columns')->get();
		foreach ($entityColumns as $entityColumn) {
			$groupValue = $entityColumn->group_values;
			if ($groupValue) {
				$groupValueArray = json_decode($groupValue, true);
				if (is_array($groupValueArray)) {
					$groupValueStr = join(', ', $groupValueArray);
					DB::table('lara_ent_entity_columns')->where('id', $entityColumn->id)->update([
						'group_values' => $groupValueStr,
					]);
				}
			}
		}

		// convert Page group (block > module)
		$pageEntity = DB::table('lara_ent_entities')->where('entity_key', 'page')->first();
		if ($pageEntity) {
			$pageEntityId = $pageEntity->id;
			$pageEntityColumn = DB::table('lara_ent_entity_columns')->where('entity_id', $pageEntityId)->first();

			if ($pageEntityColumn) {
				DB::table('lara_ent_entity_columns')->where('entity_id', $pageEntityColumn->id)->update([
					'group_values' => 'page, module, email',
				]);
			}
		}

		// fix case:
		// UserRole > Userrole
		DB::table('lara_ent_entities')->where('entity_key', 'role')->update([
			'entity_model_class' => 'Lara\\Common\\Models\\Userrole',
		]);
		// UserAbility > Userability
		DB::table('lara_ent_entities')->where('entity_key', 'ability')->update([
			'entity_model_class' => 'Lara\\Common\\Models\\Userability',
		]);
		// MenuItem > menuitem
		DB::table('lara_ent_entities')->where('entity_key', 'menuitem')->update([
			'entity_model_class' => 'Lara\\Common\\Models\\Menuitem',
			'entity_controller'  => 'MenuitemsController',
		]);

		// fix sorting in Menu entity
		$menuEntity = DB::table('lara_ent_entities')->where('entity_key', 'menu')->first();
		if ($menuEntity) {
			$menuEntityId = $menuEntity->id;
			$menuEntityColumn = DB::table('lara_ent_entity_columns')->where('entity_id', $menuEntityId)->first();
			if ($menuEntityColumn) {
				DB::table('lara_ent_entity_columns')->where('entity_id', $menuEntityColumn->id)->update([
					'sort_field' => 'id',
					'sort_order' => 'asc',
				]);
			}
		}

		// backup
		$this->backupLegacyTables();
		$this->backupEntityColumns();

	}

	/**
	 * @return void
	 */
	private function migrateModulePages()
	{

		// update cgroup
		DB::table('lara_content_pages')->where('cgroup', 'block')->update([
			'cgroup' => 'module',
		]);

		// update slug
		$pages = DB::table('lara_content_pages')->where('cgroup', 'module')->get();
		foreach ($pages as $page) {
			$parts = explode('-', $page->slug);
			if (sizeof($parts) == 3) {
				if ($parts[2] == 'block') {
					$entityKey = $parts[0];
					$method = $parts[1];
					$language = $page->language;
					$newSlug = $entityKey . '-' . $method . '-module-' . $language;

					DB::table('lara_content_pages')->where('id', $page->id)->update([
						'slug' => $newSlug,
					]);

					// check sync relation
					$sync = DB::table('lara_object_sync')->where('entity_type', 'Lara\Common\Models\Page')->where('entity_id', $page->id)->first();
					if ($sync) {
						DB::table('lara_object_sync')->where('id', $sync->id)->update([
							'slug' => $newSlug,
						]);
					}

				}

			}
		}
	}

	/**
	 * @return void
	 */
	private function migrateMediaFolder()
	{

		$oldpath = Storage::disk('localdisk')->path('textwidget');
		$newpath = Storage::disk('localdisk')->path('larawidget');

		if (is_dir($oldpath)) {
			File::move($oldpath, $newpath);
		}

	}

	/**
	 * @return void
	 */
	private function migrateWidgets()
	{

		// fix widget class
		DB::table('lara_ent_entities')->where('entity_key', 'textwidget')->update([
			'entity_model_class' => 'Lara\\Common\\Models\\Larawidget',
			'entity_key'         => 'larawidget',
			'entity_controller'  => 'LarawidgetsController',
		]);

		// fix widget table
		if (Schema::hasTable('lara_blocks_textwidgets')) {
			Schema::rename('lara_blocks_textwidgets', 'lara_blocks_larawidgets');
		}

		// fix widget columns
		$this->fixWidgetColumns();

		// fix widget content
		// get homepage

		$homeMenuItem = null;
		$homePageId = null;

		$main = DB::table('lara_menu_menus')->where('slug', 'main')->first();
		if ($main) {
			$homeMenuItem = DB::table('lara_menu_menu_items')
				->where('menu_id', $main->id)
				->where('language', 'nl')
				->whereNull('parent_id')
				->first();
			if ($homeMenuItem) {
				$homePageId = $homeMenuItem->object_id;
			}
		}

		// purge pageable
		if ($homePageId) {
			DB::table('lara_object_pageables')->where('page_id', $homePageId)->delete();
		}

		// get widgets
		$widgets = DB::table('lara_blocks_larawidgets')->get();
		foreach ($widgets as $widget) {

			$hook = $widget->hook;

			// set template
			DB::table('lara_blocks_larawidgets')->where('id', $widget->id)->update([
				'template' => $hook,
			]);

			// set global
			if (substr($hook, 0, 6) == 'footer') {
				DB::table('lara_blocks_larawidgets')->where('id', $widget->id)->update([
					'isglobal' => 1,
				]);
			}

			// add to homepage
			if (substr($hook, 0, 4) == 'home') {
				if ($homeMenuItem) {
					$homePageId = $homeMenuItem->object_id;
					DB::table('lara_object_pageables')->insert([
						'page_id'     => $homePageId,
						'entity_type' => 'Lara\\Common\\Models\\Larawidget',
						'entity_id'   => $widget->id,
					]);
				}
			}

		}

	}

	/**
	 * @return void
	 */
	private function migrateCtas()
	{

		$ctas = DB::table('lara_blocks_ctas')->get();
		foreach ($ctas as $cta) {
			$ctaId = $cta->id;
			if (!empty($cta->lead) && empty($cta->body)) {
				// move content from lead to body
				DB::table('lara_blocks_ctas')->where('id', $ctaId)->update([
					'body' => $cta->lead,
				]);
			}
		}

		$entity = DB::table('lara_ent_entities')->where('entity_key', 'cta')->first();
		if ($entity) {
			$column = DB::table('lara_ent_entity_columns')->where('entity_id', $entity->id)->first();
			if ($column) {
				DB::table('lara_ent_entity_columns')->where('id', $column->id)->update([
					'has_lead' => 0,
					'has_body' => 1,
				]);
			}
		}

	}

	/**
	 * @return void
	 */
	private function migrateSliders()
	{

		$entity = Entity::where('entity_key', 'slider')->first();

		if ($entity) {

			// purge old field definitions
			$result = DB::table('lara_ent_entity_custom_columns')->where('entity_id', $entity->id)->delete();

			// insert new field definitions
			DB::table('lara_ent_entity_custom_columns')->insert(array(

				0 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Type',
						'fieldname'          => 'type',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'after',
						'fielddata'          => '{"0":"image","1":"caption","2":"payoff"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 18,
						'field_lock'         => 0,
					),
				1 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Caption',
						'fieldname'          => 'caption',
						'fieldtype'          => 'text',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'caption',
						'position'           => 19,
						'field_lock'         => 0,
					),
				2 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Payoff',
						'fieldname'          => 'payoff',
						'fieldtype'          => 'mcemin',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 21,
						'field_lock'         => 0,
					),
				3 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Url',
						'fieldname'          => 'url',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 22,
						'field_lock'         => 0,
					),
				4 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Url Title',
						'fieldname'          => 'urltitle',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 23,
						'field_lock'         => 0,
					),
				5 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Url Text',
						'fieldname'          => 'urltext',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 24,
						'field_lock'         => 0,
					),
				6 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Overlay transparency',
						'fieldname'          => 'overlaytransp',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'after',
						'fielddata'          => '{"0":"0","1":"10","2":"20","3":"30","4":"40","5":"50","6":"60","7":"70","8":"80","9":"90"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 75,
						'field_lock'         => 0,
					),

				7  =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Text Position',
						'fieldname'          => 'textposition',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'after',
						'fielddata'          => '{"0":"center","1":"left","2":"right"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 79,
						'field_lock'         => 0,
					),
				8  =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Overlay color',
						'fieldname'          => 'overlaycolor',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'after',
						'fielddata'          => '{"0":"black","1":"white","2":"brand1","3":"brand2","4":"brand3"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 25,
						'field_lock'         => 0,
					),
				9  =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Overlay size',
						'fieldname'          => 'overlaysize',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'after',
						'fielddata'          => '{"0":"full","1":"block"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'payoff',
						'position'           => 76,
						'field_lock'         => 0,
					),
				10 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Caption type',
						'fieldname'          => 'captiontype',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'after',
						'fielddata'          => '{"0":"block","1":"fade"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'caption',
						'position'           => 20,
						'field_lock'         => 0,
					),

			));

			$this->builderCheckFieldColumns($entity, 'lara_blocks_sliders');

		}

	}

	/**
	 * @return void
	 */
	private function migrateObjectRelations()
	{

		$this->fixModelClass('lara_object_files', 'entity_type', 'id');
		$this->fixModelClass('lara_object_images', 'entity_type', 'id');
		$this->fixModelClass('lara_object_layout', 'entity_type', 'id');
		$this->fixModelClass('lara_object_related', 'related_model_class', 'id');
		$this->fixModelClass('lara_object_sync', 'entity_type', 'id');
		$this->fixModelClass('lara_object_taggables', 'entity_type', 'tag_id');

		$this->fixContentLayout();

		// add columns
		$this->addColumn('lara_object_files', 'docdate', 'date', 'title');
		$this->addColumn('lara_object_tags', 'language_parent', 'intunsigned', 'language');

		// create new tables

		// open graph
		if (!Schema::hasTable('lara_object_opengraph')) {
			Schema::create('lara_object_opengraph', function (Blueprint $table) {
				$table->increments('id');
				$table->string('entity_type')->nullable();
				$table->integer('entity_id')->unsigned();
				$table->string('og_title')->nullable();
				$table->text('og_description')->nullable();
				$table->text('og_image')->nullable();
				$table->timestamps();
			});
		}

		// pageable
		if (!Schema::hasTable('lara_object_pageables')) {
			Schema::create('lara_object_pageables', function (Blueprint $table) {
				$table->integer('page_id')->unsigned()->index();
				$table->morphs('entity');
			});
		}

		// seo
		if (!Schema::hasTable('lara_object_seo')) {
			Schema::create('lara_object_seo', function (Blueprint $table) {
				$table->increments('id');
				$table->string('entity_type')->nullable();
				$table->integer('entity_id')->unsigned();
				$table->string('seo_focus')->nullable();
				$table->string('seo_title')->nullable();
				$table->string('seo_description')->nullable();
				$table->string('seo_keywords')->nullable();
				$table->timestamps();
			});
		}

		// videos
		if (!Schema::hasTable('lara_object_videos')) {
			Schema::create('lara_object_videos', function (Blueprint $table) {
				$table->increments('id');
				$table->string('entity_type')->nullable();
				$table->integer('entity_id')->unsigned();
				$table->string('title')->nullable();
				$table->string('youtubecode')->nullable();
				$table->boolean('featured')->default(0);
				$table->timestamps();
			});
		}

		// add open graph settings
		DB::table('lara_sys_settings')->insert(array(
			0 =>
				array(
					'title'           => 'OpenGraph Type',
					'cgroup'          => 'opengraph',
					'key'             => 'og_type',
					'value'           => 'website',
					'locked_by_admin' => 1,
					'position'        => 114,
				),
			1 =>
				array(
					'title'           => 'OpenGraph Image Width',
					'cgroup'          => 'opengraph',
					'key'             => 'og_image_width',
					'value'           => '1200',
					'locked_by_admin' => 1,
					'position'        => 115,
				),
			2 =>
				array(
					'title'           => 'OpenGraph Image Height',
					'cgroup'          => 'opengraph',
					'key'             => 'og_image_height',
					'value'           => '630',
					'locked_by_admin' => 1,
					'position'        => 116,
				),
			3 =>
				array(
					'title'           => 'OpenGraph Description Max Length',
					'cgroup'          => 'opengraph',
					'key'             => 'og_descr_max',
					'value'           => '300',
					'locked_by_admin' => 1,
					'position'        => 117,
				),
			4 =>
				array(
					'title'           => 'OpenGraph Site Name',
					'cgroup'          => 'opengraph',
					'key'             => 'og_site_name',
					'value'           => 'Firmaq Media',
					'locked_by_admin' => 1,
					'position'        => 118,
				),
		));

	}

	/**
	 * @return void
	 */
	private function fixContentLayout()
	{

		// remove non-page layouts
		DB::table('lara_object_layout')
			->where('entity_type', '!=', 'Lara\Common\Models\Page')
			->delete();

		// find homepage
		$homepage = DB::table('lara_content_pages')
			->where('ishome', 1)
			->first();

		if ($homepage) {

			$homePageId = $homepage->id;

			$this->setLayoutItem('Lara\Common\Models\Page', $homePageId, 'hero', 'hero_flexslider');
			$this->setLayoutItem('Lara\Common\Models\Page', $homePageId, 'pagetitle', 'hidden');
			$this->setLayoutItem('Lara\Common\Models\Page', $homePageId, 'content', 'boxed_default_col_12');
			$this->setLayoutItem('Lara\Common\Models\Page', $homePageId, 'share', 'hidden');
		}

		// convert layout values
		$layouts = DB::table('lara_object_layout')
			->where('layout_key', 'content')
			->whereNotNull('layout_value')
			->get();

		foreach ($layouts as $layout) {

			if (substr($layout->layout_value, 0, 11) == 'full_width_') {

				$array = explode('_', $layout->layout_value);
				$cols = $array[2];
				$newLayoutValue = 'boxed_default_col_' . $cols;
				DB::table('lara_object_layout')->where('id', $layout->id)->update([
					'layout_value' => $newLayoutValue,
				]);

			}
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
	private function migrateSeo()
	{

		$groups = DB::table('lara_ent_entity_groups')->whereIn('key', ['page', 'entity', 'tag'])->get();
		foreach ($groups as $group) {

			$entities = DB::table('lara_ent_entities')->where('group_id', $group->id)->get();

			foreach ($entities as $entity) {

				if ($group->key == 'tag') {
					$prefix = 'lara_object_';
				} else {
					$prefix = 'lara_content_';
				}
				$tablename = $prefix . str_plural($entity->entity_key);

				$modelClass = $entity->entity_model_class;

				$this->moveSeoData($tablename, $modelClass);
			}

		}

	}

	/**
	 * @return void
	 */
	private function migrateMenus()
	{

		// menu
		$this->dropColumn('lara_menu_menus', 'position');

		// menuitem
		$tablename = 'lara_menu_menu_items';
		if (!Schema::hasColumn($tablename, 'tag_id')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->integer('tag_id')->unsigned()->nullable()->after('type');
			});
		}
		if (!Schema::hasColumn($tablename, 'route_has_auth')) {
			Schema::table($tablename, function (Blueprint $table) {
				$table->boolean('route_has_auth')->default(0)->after('routename');
			});
		}

		// redirects
		if (!Schema::hasTable('lara_menu_redirects')) {
			Schema::create('lara_menu_redirects', function (Blueprint $table) {
				$table->increments('id');
				$table->string('language')->nullable();
				$table->string('title')->nullable();
				$table->string('redirectfrom')->nullable();
				$table->string('redirectto')->nullable();
				$table->string('redirecttype')->nullable();
				$table->boolean('auto_generated')->default(0);
				$table->boolean('locked_by_admin')->default(0);
				$table->boolean('has_error')->default(0);
				$table->timestamps();
				$table->boolean('publish')->default(0);
				$table->timestamp('locked_at')->nullable();
				$table->integer('locked_by')->nullable()->unsigned();
			});
		}

	}

	/**
	 * @return void
	 */
	private function migrateSystem()
	{

		// purge uploads
		DB::table('lara_sys_uploads')->delete();

	}

	/**
	 * @return void
	 */
	private function migrateManagedTables()
	{

		$groups = DB::table('lara_ent_entity_groups')->where('group_has_managedtable', 1)->get();
		foreach ($groups as $group) {
			$entities = DB::table('lara_ent_entities')->where('group_id', $group->id)->get();
			foreach ($entities as $entity) {

				// enable language column for page, block, and entities
				if (in_array($group->key, ['page', 'block', 'entity'])) {
					DB::table('lara_ent_entity_columns')->where('entity_id', $entity->id)->update([
						'has_lang' => 1,
					]);
				}
				// disable language column for forms
				if ($group->key == 'form') {
					DB::table('lara_ent_entity_columns')->where('entity_id', $entity->id)->update([
						'has_lang' => 0,
					]);
				}

				// update content tables
				$this->checkEntityTable($entity);

			}

		}

	}

	/**
	 * @param string $tablename
	 * @param string $modelClass
	 * @return void
	 */
	private function moveSeoData(string $tablename, string $modelClass)
	{

		$rows = DB::table($tablename)->get();
		foreach ($rows as $row) {

			if (property_exists($row, 'seo_focus') && property_exists($row, 'seo_title') && property_exists($row, 'seo_description') && property_exists($row, 'seo_keywords')) {
				$result = DB::table('lara_object_seo')
					->updateOrInsert(
						[
							'entity_type' => $modelClass,
							'entity_id'   => $row->id,
						],
						[
							'seo_focus'       => $row->seo_focus,
							'seo_title'       => $row->seo_title,
							'seo_description' => $row->seo_description,
							'seo_keywords'    => $row->seo_keywords,

						]
					);

			}

		}

		$this->renameColumn($tablename, 'seo_focus', '_seo_focus');
		$this->renameColumn($tablename, 'seo_title', '_seo_title');
		$this->renameColumn($tablename, 'seo_description', '_seo_description');
		$this->renameColumn($tablename, 'seo_keywords', '_seo_keywords');

	}

	/**
	 * @param string $table
	 * @param string $column
	 * @param string $identifier
	 * @return void
	 */
	private function fixModelClass(string $table, string $column, string $identifier)
	{

		$rows = DB::table($table)->get();
		foreach ($rows as $row) {

			$modelclass = $row->$column;

			$parts = explode('\\', $modelclass);

			if ($parts[0] == 'Lara' && ($parts[1] == 'Common' || $parts[1] == 'Entity') && $parts[2] == 'Models') {

				$modelClass = $parts[3];

				// convert TextWidget to Larawidget
				if ($modelClass == 'TextWidget') {
					DB::table($table)->where($identifier, $row->$identifier)->update([
						$column => 'Lara\\Common\\Models\\Larawidget',
					]);
				}

				// convert Lara Model Classes to Eve Model Classes
				$entityKey = strtolower($modelClass);
				$entity = DB::table('lara_ent_entities')->where('entity_key', $entityKey)->first();

				if ($entity) {

					$group = DB::table('lara_ent_entity_groups')->where('id', $entity->group_id)->first();

					if ($group->path == 'Eve') {

						$newModelClass = 'Eve\\Models\\' . $modelClass;

						DB::table($table)->where($identifier, $row->$identifier)->update([
							$column => $newModelClass,
						]);

					}

				}

			}

		}

	}

	/**
	 * @param object $entity
	 * @param object $group
	 * @param object $options
	 * @param object $settings
	 * @return void
	 */
	private function updateEntityColumns(object $entity, object $group, object $options, object $settings)
	{

		if (in_array($group->key, ['content', 'page', 'entity', 'block']) || $entity->entity_key == 'menuitem') {
			$has_user = 1;
		} else {
			$has_user = 0;
		}

		DB::table('lara_ent_entity_columns')
			->updateOrInsert(
				['entity_id' => $entity->id],
				[
					'has_user' => $has_user,

					'has_slug' => $options->has_slug,
					'has_lead' => $options->has_lead,
					'has_body' => $options->has_body,
					'has_app'  => $options->has_app,

					'has_lang'       => $settings->has_lang,
					'has_status'     => $settings->has_status,
					'has_expiration' => $settings->has_expiration,
					'has_groups'     => $settings->has_groups,
					'group_values'   => $settings->group_values,
					'group_default'  => $settings->group_default,
					'has_fields'     => $settings->has_fields,

					'is_sortable' => $entity->is_sortable,
					'sort_field'  => $entity->sort_field,
					'sort_order'  => $entity->sort_order,
					'sort2_field' => null,
					'sort2_order' => null,
				]
			);

	}

	/**
	 * @param object $entity
	 * @param object $group
	 * @param object $options
	 * @param object $settings
	 * @return void
	 */
	private function updateEntityObjectRelations(object $entity, object $group, object $options, object $settings)
	{

		$has_opengraph = (in_array($entity->entity_key, ['page', 'blog'])) ? 1 : 0;
		$has_layout = ($entity->entity_key == 'page') ? 1 : 0;
		$has_sync = (in_array($group->key, ['content', 'page', 'entity', 'block'])) ? 1 : 0;
		$has_videos = 0;
		$max_videos = 0;

		DB::table('lara_ent_entity_object_relations')
			->updateOrInsert(
				['entity_id' => $entity->id],
				[
					'has_opengraph' => $has_opengraph,
					'has_layout'    => $has_layout,
					'has_sync'      => $has_sync,
					'has_videos'    => $has_videos,
					'max_videos'    => $max_videos,

					'has_seo'      => $options->has_seo,
					'has_related'  => $options->has_related,
					'is_relatable' => $options->is_relatable,
					'has_images'   => $options->has_images,
					'has_files'    => $options->has_files,
					'max_images'   => $options->max_images,
					'max_files'    => $options->max_files,

					'has_tags'    => $settings->has_tags,
					'tag_default' => $settings->tag_default,
				]
			);

	}

	/**
	 * @param object $entity
	 * @param object $group
	 * @param object $options
	 * @param object $settings
	 * @return void
	 */
	private function updateEntityPanels(object $entity, object $group, object $options, object $settings)
	{

		$has_filters = 0;
		$show_status = (in_array($group->key, ['content', 'page', 'entity', 'block'])) ? 1 : 0;

		DB::table('lara_ent_entity_panels')
			->updateOrInsert(
				['entity_id' => $entity->id],
				[
					'has_filters' => $has_filters,
					'show_status' => $show_status,

					'has_tiny_lead' => $options->has_tiny_lead,
					'has_tiny_body' => $options->has_tiny_body,

					'has_batch'   => $settings->has_batch,
					'has_search'  => $settings->has_search,
					'show_author' => $settings->has_author,
				]
			);

	}

	/**
	 * @param object $entity
	 * @param object $fields
	 * @return void
	 */
	private function updateCustomColumnsRelations(object $entity, object $fields)
	{

		// purge
		DB::table('lara_ent_entity_custom_columns')->where('entity_id', $entity->id)->delete();

		foreach ($fields as $field) {

			DB::table('lara_ent_entity_custom_columns')->insert([
				'entity_id'          => $entity->id,
				'fieldtitle'         => $field->fieldtitle,
				'fieldname'          => $field->fieldname,
				'fieldtype'          => $field->fieldtype,
				'fieldhook'          => $field->fieldhook,
				'fielddata'          => $field->fielddata,
				'primary'            => $field->primary,
				'required'           => $field->required,
				'fieldstate'         => $field->fieldstate,
				'condition_field'    => $field->condition_field,
				'condition_operator' => $field->condition_operator,
				'condition_value'    => $field->condition_value,
				'position'           => $field->position,
				'field_lock'         => $field->field_lock,

			]);

		}

	}

	/**
	 * @param int $groupId
	 * @return object|null
	 */
	private function getEntityGroup(int $groupId)
	{
		$group = DB::table('lara_ent_entity_groups')->where('id', $groupId)->first();

		return $group;
	}

	/**
	 * @param int $entityId
	 * @return object|null
	 */
	private function getLegacyOptions(int $entityId)
	{

		$options = DB::table('lara_ent_entity_options')->where('entity_id', $entityId)->first();

		return $options;

	}

	/**
	 * @param int $entityId
	 * @return object|null
	 */
	private function getLegacySettings(int $entityId)
	{

		$settings = DB::table('lara_ent_entity_settings')->where('entity_id', $entityId)->first();

		return $settings;

	}

	/**
	 * @param int $entityId
	 * @return object
	 */
	private function getLegacyFields(int $entityId)
	{

		$fields = DB::table('lara_ent_entity_fields')->where('entity_id', $entityId)->get();

		return $fields;

	}

	/**
	 * @param string $tablename
	 * @param string $colname
	 * @return void
	 */
	private function dropColumn(string $tablename, string $colname)
	{
		if (Schema::hasColumn($tablename, $colname)) {
			Schema::disableForeignKeyConstraints();
			Schema::table($tablename, function ($table) use ($colname) {
				$table->dropColumn($colname);
			});
			Schema::enableForeignKeyConstraints();
		}
	}

	/**
	 * @param string $tablename
	 * @param string $colname
	 * @param string $coltype
	 * @param string $after
	 * @return void
	 */
	private function addColumn(string $tablename, string $colname, string $coltype, string $after)
	{

		Schema::table($tablename, function ($table) use ($colname, $coltype, $after) {

			if ($coltype == 'string') {
				$table->string($colname)->after($after)->nullable();
			}
			if ($coltype == 'text') {
				$table->text($colname)->nullable()->after($after);
			}
			if ($coltype == 'email') {
				$table->email($colname)->after($after);
			}
			if ($coltype == 'datetime') {
				$table->timestamp($colname)->nullable()->after($after);
			}
			if ($coltype == 'date') {
				$table->date($colname)->nullable()->after($after);
			}
			if ($coltype == 'time') {
				$table->time($colname)->nullable()->after($after);
			}
			if ($coltype == 'boolean') {
				$table->boolean($colname)->default(0)->after($after);
			}
			if ($coltype == 'integer') {
				$table->integer($colname)->default(0)->after($after);
			}
			if ($coltype == 'intunsigned') {
				$table->integer($colname)->unsigned()->default(0)->after($after);
			}

		});

	}

	/**
	 * @param string $tablename
	 * @param string $colname
	 * @param string $newname
	 * @return void
	 */
	private function renameColumn(string $tablename, string $colname, string $newname)
	{
		if (Schema::hasColumn($tablename, $colname)) {
			Schema::disableForeignKeyConstraints();
			Schema::table($tablename, function ($table) use ($colname, $newname) {
				$table->renameColumn($colname, $newname);
			});
			Schema::enableForeignKeyConstraints();
		}
	}

	/**
	 * @return void
	 */
	private function backupLegacyTables()
	{

		$this->backupTable('lara_ent_entity_fields');
		$this->backupTable('lara_ent_entity_options');
		$this->backupTable('lara_ent_entity_settings');

	}

	/**
	 * @param string $tablename
	 * @return void
	 */
	private function backupTable(string $tablename)
	{

		Schema::rename($tablename, 'zz_' . $tablename);

	}

	/**
	 * @return void
	 */
	private function createNewEntityTables()
	{

		if (!Schema::hasTable('lara_ent_entity_object_relations')) {

			Schema::create('lara_ent_entity_object_relations', function (Blueprint $table) {

				// ID's
				$table->increments('id');

				$table->integer('entity_id')->unsigned();

				$table->boolean('has_seo')->default(0);
				$table->boolean('has_opengraph')->default(0);
				$table->boolean('has_layout')->default(0);
				$table->boolean('has_related')->default(0);
				$table->boolean('is_relatable')->default(0);

				$table->boolean('has_tags')->default(0);
				$table->string('tag_default')->nullable();

				$table->boolean('has_sync')->default(0);

				$table->boolean('has_images')->default(0);
				$table->boolean('has_videos')->default(0);
				$table->boolean('has_files')->default(0);
				$table->integer('max_images')->unsigned()->default(1);
				$table->integer('max_videos')->unsigned()->default(1);
				$table->integer('max_files')->unsigned()->default(1);

				// foreign keys
				$table->foreign('entity_id')
					->references('id')
					->on('lara_ent_entities')
					->onDelete('cascade');

			});
		}

		if (!Schema::hasTable('lara_ent_entity_columns')) {

			Schema::create('lara_ent_entity_columns', function (Blueprint $table) {

				// ID's
				$table->increments('id');

				$table->integer('entity_id')->unsigned();

				$table->boolean('has_user')->default(0);
				$table->boolean('has_lang')->default(0);

				$table->boolean('has_slug')->default(0);
				$table->boolean('has_lead')->default(0);
				$table->boolean('has_body')->default(0);

				$table->boolean('has_status')->default(0);
				$table->boolean('has_expiration')->default(0);
				$table->boolean('has_app')->default(0);

				$table->boolean('has_groups')->default(0);
				$table->string('group_values')->nullable();
				$table->string('group_default')->nullable();

				$table->boolean('is_sortable')->default(0);
				$table->string('sort_field')->nullable();
				$table->string('sort_order')->nullable();
				$table->string('sort2_field')->nullable();
				$table->string('sort2_order')->nullable();

				$table->boolean('has_fields')->default(0);

				// foreign keys
				$table->foreign('entity_id')
					->references('id')
					->on('lara_ent_entities')
					->onDelete('cascade');

			});
		}

		if (!Schema::hasTable('lara_ent_entity_panels')) {

			Schema::create('lara_ent_entity_panels', function (Blueprint $table) {

				// ID's
				$table->increments('id');

				$table->integer('entity_id')->unsigned();

				$table->boolean('has_search')->default(0);
				$table->boolean('has_batch')->default(0);
				$table->boolean('has_filters')->default(0);
				$table->boolean('show_author')->default(0);
				$table->boolean('show_status')->default(0);

				$table->boolean('has_tiny_lead')->default(0);
				$table->boolean('has_tiny_body')->default(0);

				// foreign keys
				$table->foreign('entity_id')
					->references('id')
					->on('lara_ent_entities')
					->onDelete('cascade');

			});
		}

		if (!Schema::hasTable('lara_ent_entity_custom_columns')) {

			Schema::create('lara_ent_entity_custom_columns', function (Blueprint $table) {

				// ID's
				$table->increments('id');

				$table->integer('entity_id')->unsigned();

				$table->string('fieldtitle')->nullable();
				$table->string('fieldname')->nullable();
				$table->string('fieldtype')->nullable();
				$table->string('fieldhook')->nullable();
				$table->text('fielddata')->nullable();

				$table->boolean('primary')->default(0);
				$table->boolean('required')->default(0);

				$table->string('fieldstate')->nullable();
				$table->string('condition_field')->nullable();
				$table->string('condition_operator')->nullable();
				$table->string('condition_value')->nullable();

				$table->integer('position')->unsigned();

				$table->boolean('field_lock')->default(0);

				// foreign keys
				$table->foreign('entity_id')
					->references('id')
					->on('lara_ent_entities')
					->onDelete('cascade');

			});
		}

	}

	/**
	 * @param string $oldtable
	 * @param string $newtable
	 * @return void
	 */
	private function cloneTable(string $oldtable, string $newtable)
	{

		DB::statement("CREATE TABLE " . $newtable . " LIKE " . $oldtable);
		DB::statement("INSERT " . $newtable . " SELECT * FROM " . $oldtable);

	}

	/**
	 * @return void
	 */
	private function backupEntityColumns()
	{

		$tablename = 'lara_ent_entities';
		$legacyColumns = ['is_required', 'is_sortable', 'sort_field', 'sort_order', 'licence'];

		foreach ($legacyColumns as $legacyColumn) {
			$this->renameColumn($tablename, $legacyColumn, '_' . $legacyColumn);
		}

	}

	/**
	 * @return void
	 */
	private function cleanupBackups()
	{

		// cleanup backup tables
		$tablenames = DB::select('SHOW TABLES');

		foreach ($tablenames as $tablename) {
			foreach ($tablename as $key => $tname) {

				if (substr($tname, 0, 3) == 'zz_') {
					Schema::drop($tname);
					break;
				}
				if (substr($tname, 0, 1) == '_') {
					Schema::drop($tname);
					break;
				}

				// cleanup backup columns
				$columns = Schema::getColumnListing($tname);
				foreach ($columns as $column) {
					if (substr($column, 0, 1) == '_') {
						$this->dropColumn($tname, $column);
					}
				}

			}
		}

	}

	/**
	 * @param object $entity
	 * @return void
	 */
	private function checkEntityTable(object $entity) {

		// get Entity Model
		$entity = Entity::where('entity_key', $entity->entity_key)->first();

		if ($entity) {

			$modelClass = $entity->getEntityModelClass();
			$tablename = $modelClass::getTableName();

			// migrate content tables
			$this->builderCheckEntityTable($entity, $tablename);

		}

	}

	/**
	 * @return void
	 */
	private function createNewEntityFiles()
	{

		$group = DB::table('lara_ent_entity_groups')->where('key', 'entity')->first();
		$entities = DB::table('lara_ent_entities')->where('group_id', $group->id)->get();
		foreach ($entities as $entity) {

			// check if model exists
			if (!class_exists($entity->entity_model_class)) {

				// get Entity as a Model
				$newEntity = Entity::where('entity_key', $entity->entity_key)->first();

				// create new Admin controller
				$this->builderMakeAdminController($newEntity);

				// create new Front controller
				$this->builderMakeFrontController($newEntity);

				// create new model
				$this->builderMakeModel($newEntity);

				// create new entity
				$this->builderMakeEntity($newEntity);

			}

		}

	}

	/**
	 * @return void
	 */
	private function fixWidgetColumns()
	{

		$entity = Entity::where('entity_key', 'larawidget')->first();

		if ($entity) {

			// purge old field definitions
			DB::table('lara_ent_entity_custom_columns')->where('entity_id', $entity->id)->delete();

			// insert new field definitions
			DB::table('lara_ent_entity_custom_columns')->insert(array(
				0 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Hook',
						'fieldname'          => 'hook',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'before',
						'fielddata'          => '{"0":"content_top","1":"content_bottom","2":"home1","3":"home2","4":"home3","5":"home4","6":"home5","7":"home6","8":"home7","9":"home8","10":"home9","11":"home10","12":"footer1","13":"footer2","14":"footer3","15":"footer4"}',
						'primary'            => 1,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 38,
						'field_lock'         => 0,
					),
				1 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Icon class',
						'fieldname'          => 'iconclass',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'icon',
						'position'           => 42,
						'field_lock'         => 0,
					),
				2 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Icon align',
						'fieldname'          => 'iconalign',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'after',
						'fielddata'          => '{"0":"top","1":"left","2":"right"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'icon',
						'position'           => 43,
						'field_lock'         => 0,
					),
				3 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Type',
						'fieldname'          => 'type',
						'fieldtype'          => 'selectone',
						'fieldhook'          => 'before',
						'fielddata'          => '{"0":"text","1":"icon","2":"feature","3":"module"}',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 39,
						'field_lock'         => 0,
					),
				4 =>
					array(
						'entity_id'          => 3,
						'fieldtitle'         => 'Role',
						'fieldname'          => 'role',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 81,
						'field_lock'         => 0,
					),
				5 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Link text',
						'fieldname'          => 'linktext',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 36,
						'field_lock'         => 0,
					),
				6 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'Link url',
						'fieldname'          => 'linkurl',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 37,
						'field_lock'         => 0,
					),

				7  =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'relentkey',
						'fieldname'          => 'relentkey',
						'fieldtype'          => 'custom',
						'fieldhook'          => 'between',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'module',
						'position'           => 98,
						'field_lock'         => 0,
					),
				8  =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'term',
						'fieldname'          => 'term',
						'fieldtype'          => 'custom',
						'fieldhook'          => 'between',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'module',
						'position'           => 99,
						'field_lock'         => 0,
					),
				9  =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'imgreq',
						'fieldname'          => 'imgreq',
						'fieldtype'          => 'boolean',
						'fieldhook'          => 'between',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'module',
						'position'           => 100,
						'field_lock'         => 0,
					),
				10 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'maxitems',
						'fieldname'          => 'maxitems',
						'fieldtype'          => 'intunsigned',
						'fieldhook'          => 'between',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'module',
						'position'           => 101,
						'field_lock'         => 0,
					),
				11 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'usecache',
						'fieldname'          => 'usecache',
						'fieldtype'          => 'boolean',
						'fieldhook'          => 'between',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'module',
						'position'           => 102,
						'field_lock'         => 0,
					),
				12 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'sortorder',
						'fieldname'          => 'sortorder',
						'fieldtype'          => 'intunsigned',
						'fieldhook'          => 'between',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => 'NULL',
						'condition_operator' => 'NULL',
						'condition_value'    => 'NULL',
						'position'           => 103,
						'field_lock'         => 0,
					),
				13 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'template',
						'fieldname'          => 'template',
						'fieldtype'          => 'string',
						'fieldhook'          => 'between',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 104,
						'field_lock'         => 0,
					),
				14 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'isglobal',
						'fieldname'          => 'isglobal',
						'fieldtype'          => 'boolean',
						'fieldhook'          => 'before',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabled',
						'condition_field'    => null,
						'condition_operator' => null,
						'condition_value'    => null,
						'position'           => 105,
						'field_lock'         => 0,
					),
				15 =>
					array(
						'entity_id'          => $entity->id,
						'fieldtitle'         => 'customtags',
						'fieldname'          => 'customtags',
						'fieldtype'          => 'string',
						'fieldhook'          => 'after',
						'fielddata'          => '',
						'primary'            => 0,
						'required'           => 0,
						'fieldstate'         => 'enabledif',
						'condition_field'    => 'type',
						'condition_operator' => 'isequal',
						'condition_value'    => 'feature || featuresmall',
						'position'           => 106,
						'field_lock'         => 0,
					),
			));

			$this->builderCheckFieldColumns($entity, 'lara_blocks_larawidgets');
		}

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

				// check cgroup (for Lara < 5.4)
				$cgroup = $translation->cgroup;
				$entity = Entity::where('entity_key', $cgroup)->first();
				if ($entity) {
					$egroupkey = $entity->egroup->key;
					if (in_array($egroupkey, ['entity', 'form'])) {
						// overrule module
						$module = 'eve';
					}
				}

				// add prefix
				$newModule = 'lara-' . $module;
				$translation->module = $newModule;
				$translation->save();
			}
		}

	}

	/**
	 * @return void
	 */
	private function importFiles()
	{

		$ds = DIRECTORY_SEPARATOR;

		$basePath = explode('/', base_path());
		$vhost = array_slice($basePath, 0, 5);
		$importBase = implode('/', $vhost);

		$importfiles = config('lara-common.import_on_update.files');
		$importdirs = config('lara-common.import_on_update.dirs');

		if ($importdirs) {

			foreach ($importdirs as $importdir) {

				$folder = $importdir['folder'];

				if ($folder) {
					$src = $importBase . $ds . $importdir['src'];
					$dest = $importBase . $ds . $importdir['dest'];

					if (file_exists($dest . $folder)) {
						$backup = $importdir['backup'];
						if ($backup) {
							// check for old backups
							if (file_exists($dest . '_' . $folder)) {
								// old backup found, skip backup
							} else {
								// backup folder
								rename($dest . $folder, $dest . '_' . $folder);
							}
						} else {
							rmdir($dest . $folder);
						}
					}

					recurse_copy($src . $folder, $dest . $folder);
				}

			}

		}

		if ($importfiles) {

			foreach ($importfiles as $importfile) {

				$file = $importfile['file'];

				if ($file) {

					$src = $importBase . $ds . $importfile['src'];
					$dest = $importBase . $ds . $importfile['dest'];

					if (file_exists($dest . $file)) {
						$backup = $importfile['backup'];
						if ($backup) {
							// check for old backups
							if (file_exists($dest . '_' . $file)) {
								// old backup found, skip backup
							} else {
								// backup file
								rename($dest . $file, $dest . '_' . $file);
							}
						} else {
							unlink($dest . $file);
						}
					}

					copy($src . $file, $dest . $file);

				}

			}

		}

	}


	/**
	 * @return void
	 */
	private function setNewVersion()
	{

		DB::table('lara_sys_settings')->where('key', 'lara_db_version')->update([
			'value' => $this->laraVersion,
		]);

	}

	/**
	 * @return void
	 */
	private function setEnvironmentValue()
	{

		$envFile = app()->environmentFilePath();
		$str = file_get_contents($envFile);

		$str = str_replace("LARA_NEEDS_SETUP=true", "LARA_NEEDS_SETUP=false", $str);

		$fp = fopen($envFile, 'w');
		fwrite($fp, $str);
		fclose($fp);

	}

	/**
	 * @return void
	 */
	private function clearAllCache()
	{

		Artisan::call('cache:clear');
		Artisan::call('config:clear');
		Artisan::call('view:clear');

	}

}