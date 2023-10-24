<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Schema\Blueprint;

use Illuminate\Http\Request;

use Lara\Common\Models\Entity;
use Lara\Common\Models\Entitygroup;
use Lara\Common\Models\EntityRelation;
use Lara\Common\Models\Userability;
use Lara\Common\Models\Related;

use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;

use Bouncer;

use Config;

trait AdminBuilderTrait
{

	/**
	 * @param string $entity_key
	 * @param bool $force
	 * @return void
	 */
	private function builderCheckEntityHealth(string $entity_key, $force = false)
	{

		if (config('app.env') == 'production' || $force == true) {

			$entity = Entity::where('entity_key', $entity_key)->first();

			if ($entity && $entity->egroup->group_has_managedtable == 1) {

				$modelClass = $entity->getEntityModelClass();
				$tablename = $modelClass::getTableName();

				// check table
				$this->builderCheckEntityTable($entity, $tablename);

				// check Custom Fields
				$this->builderCheckFieldColumns($entity, $tablename);

				// check related columns (foreign key)
				$this->builderCheckRelatedColumns($entity, $tablename);

				// check Abandoned Columns
				$this->builderCheckAbandonedColumns($entity, $tablename);

				// check Views
				$this->builderCheckViews($entity);

				// update model
				$this->builderUpdateModel($entity);

				// update entity
				$this->builderUpdateEntity($entity);

				$this->builderCheckMediaFolder($entity);

				// check if permissions exist in table
				$this->builderCheckAbilitiesExist($entity);

				// flash('All module checks passed')->success();
			}

		}

	}

	/**
	 * @param object $entity
	 * @param string $tablename
	 * @return void
	 */
	private function builderCheckEntityTable(object $entity, string $tablename)
	{

		$tablenames = config('lara-common.database');

		/*
		 * As of version 7.5 we use BIGINTs for our primary keys, and for the associated foreign keys.
		 * We only use this on new applications with NEW databases.
		 * We do not convert the existing ones, because of complications with foreign keys.
		 * Therefor this function is backwards compatible
		 */
		$useBigInt = Schema::getColumnType($tablenames['auth']['users'], 'id') == 'bigint';

		if (Schema::hasTable($tablename)) {

			// check optional columns

			/**
			 * USER
			 */
			if ($entity->columns->has_user) {
				if (!Schema::hasColumn($tablename, 'user_id')) {
					$after = $this->builderGetColumnPosition($entity, 'user_id', 1);
					Schema::table($tablename, function ($table) use ($tablenames, $after, $useBigInt) {
						if ($useBigInt) {
							$table->bigInteger('user_id')->unsigned()->after($after);
						} else {
							$table->integer('user_id')->unsigned()->after($after);
						}
						$table->foreign('user_id')
							->references('id')
							->on($tablenames['auth']['users'])
							->onDelete('cascade');
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'user_id')) {
					$this->builderDropEntityColumn($tablename, 'user_id', true, true);
				}
			}

			/**
			 * LANGUAGE
			 */
			if ($entity->columns->has_lang) {
				if (!Schema::hasColumn($tablename, 'language')) {
					$after = $this->builderGetColumnPosition($entity, 'language', 1);
					Schema::table($tablename, function ($table) use ($after) {
						$table->string('language')->nullable()->after($after);
					});
				}
				if (!Schema::hasColumn($tablename, 'language_parent')) {
					$after = $this->builderGetColumnPosition($entity, 'language', 1);
					Schema::table($tablename, function ($table) use ($after, $useBigInt) {
						if ($useBigInt) {
							$table->bigInteger('language_parent')->unsigned()->nullable()->after($after);
						} else {
							$table->integer('language_parent')->unsigned()->nullable()->after($after);
						}
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'language')) {
					$this->builderDropEntityColumn($tablename, 'language', true);
				}
				if (Schema::hasColumn($tablename, 'language_parent')) {
					$this->builderDropEntityColumn($tablename, 'language_parent', true);
				}
			}

			/**
			 * SLUG
			 */
			if ($entity->columns->has_slug) {
				if (!Schema::hasColumn($tablename, 'slug')) {
					$after = $this->builderGetColumnPosition($entity, 'slug', 2);
					Schema::table($tablename, function ($table) use ($after) {
						$table->string('slug')->nullable()->after($after);
						$table->index('slug');
					});
				}
				if (!Schema::hasColumn($tablename, 'slug_lock')) {
					$after = $this->builderGetColumnPosition($entity, 'slug_lock', 2);
					Schema::table($tablename, function ($table) use ($after) {
						$table->boolean('slug_lock')->default(0)->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'slug')) {
					$this->builderDropEntityColumn($tablename, 'slug', true);
				}
				if (Schema::hasColumn($tablename, 'slug_lock')) {
					$this->builderDropEntityColumn($tablename, 'slug_lock', true);
				}
			}

			/**
			 * LEAD
			 */
			if ($entity->columns->has_lead) {
				if (!Schema::hasColumn($tablename, 'lead')) {
					$after = $this->builderGetColumnPosition($entity, 'lead', 2);
					Schema::table($tablename, function ($table) use ($after) {
						$table->text('lead')->nullable()->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'lead')) {
					$this->builderDropEntityColumn($tablename, 'lead', true);
				}
			}

			/**
			 * BODY
			 */
			if ($entity->columns->has_body) {
				if (!Schema::hasColumn($tablename, 'body')) {
					$after = $this->builderGetColumnPosition($entity, 'body', 2);
					Schema::table($tablename, function ($table) use ($after) {
						$table->text('body')->nullable()->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'body')) {
					$this->builderDropEntityColumn($tablename, 'body', true);
				}
			}

			/**
			 * STATUS
			 */
			if ($entity->columns->has_status) {
				$after = $this->builderGetColumnPosition($entity, 'publish', 3);
				if (!Schema::hasColumn($tablename, 'publish')) {
					Schema::table($tablename, function ($table) use ($after) {
						$table->boolean('publish')->default(0)->after($after);
					});
				}
				if (!Schema::hasColumn($tablename, 'publish_from')) {
					$after = $this->builderGetColumnPosition($entity, 'publish_from', 3);
					Schema::table($tablename, function ($table) use ($after) {
						$table->timestamp('publish_from')->nullable()->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'publish')) {
					$this->builderDropEntityColumn($tablename, 'publish', true);
				}
				if (Schema::hasColumn($tablename, 'publish_from')) {
					$this->builderDropEntityColumn($tablename, 'publish_from', true);
				}
			}

			/**
			 * HIDE IN LIST
			 */
			if ($entity->columns->has_hideinlist) {
				if (!Schema::hasColumn($tablename, 'publish_hide')) {
					$after = $this->builderGetColumnPosition($entity, 'publish_hide', 3);
					Schema::table($tablename, function ($table) use ($after) {
						$table->boolean('publish_hide')->default(0)->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'publish_hide')) {
					$this->builderDropEntityColumn($tablename, 'publish_hide', true);
				}
			}

			/**
			 * EXPIRATION
			 */
			if ($entity->columns->has_expiration) {
				if (!Schema::hasColumn($tablename, 'publish_to')) {
					$after = $this->builderGetColumnPosition($entity, 'publish_to', 3);
					Schema::table($tablename, function ($table) use ($after) {
						$table->timestamp('publish_to')->nullable()->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'publish_to')) {
					$this->builderDropEntityColumn($tablename, 'publish_to', true);
				}
			}

			/**
			 * APP
			 */
			if ($entity->columns->has_app) {
				if (!Schema::hasColumn($tablename, 'show_in_app')) {
					$after = $this->builderGetColumnPosition($entity, 'show_in_app', 3);
					Schema::table($tablename, function ($table) use ($after) {
						$table->boolean('show_in_app')->default(0)->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'show_in_app')) {
					$this->builderDropEntityColumn($tablename, 'show_in_app', true);
				}
			}

			/**
			 * CGROUP
			 */
			if ($entity->columns->has_groups) {
				if (!Schema::hasColumn($tablename, 'cgroup')) {
					$after = $this->builderGetColumnPosition($entity, 'cgroup', 4);
					Schema::table($tablename, function ($table) use ($after) {
						$table->string('cgroup')->nullable()->after($after);
					});
				}
			} else {
				if (Schema::hasColumn($tablename, 'cgroup')) {
					$this->builderDropEntityColumn($tablename, 'cgroup', true);
				}
			}

			/**
			 * SORTABLE
			 */
			if ($entity->columns->is_sortable) {
				if (!Schema::hasColumn($tablename, 'position')) {
					$after = $this->builderGetColumnPosition($entity, 'position', 4);
					Schema::table($tablename, function ($table) use ($after) {
						$table->integer('position')->unsigned()->nullable()->index()->after($after);
					});

					// fill the column with default order
					$modelClass = $entity->getEntityModelClass();
					$objects = $modelClass::orderBy('id')->get();
					$i = 1;
					foreach ($objects as $object) {
						$object->position = $i;
						$object->save();
						$i++;
					}

				}
			} else {
				if (Schema::hasColumn($tablename, 'position')) {
					$this->builderDropEntityColumn($tablename, 'position', true);
				}
			}

			/**
			 * IP ADDRESS
			 */
			if ($entity->egroup->key == 'form') {
				if (!Schema::hasColumn($tablename, 'ipaddress')) {
					Schema::table($tablename, function ($table) {
						$table->string('ipaddress')->nullable();
					});
				}
			}

			/**
			 * Drop deprecated columns
			 */
			if (Schema::hasColumn($tablename, 'seo_focus')) {
				$this->builderDropEntityColumn($tablename, 'seo_focus', true);
			}
			if (Schema::hasColumn($tablename, 'seo_title')) {
				$this->builderDropEntityColumn($tablename, 'seo_title', true);
			}
			if (Schema::hasColumn($tablename, 'seo_description')) {
				$this->builderDropEntityColumn($tablename, 'seo_description', true);
			}
			if (Schema::hasColumn($tablename, 'seo_keywords')) {
				$this->builderDropEntityColumn($tablename, 'seo_keywords', true);
			}

		} else {

			// table does not exist yet, create it
			Schema::create($tablename, function (Blueprint $table) use ($entity, $tablename, $tablenames, $useBigInt) {

				/*
				 * ID
				 */
				if ($useBigInt) {
					$table->bigIncrements('id');
				} else {
					$table->increments('id');
				}

				/*
				 * USER (optional)
				 */
				if ($entity->columns->has_user) {
					if ($useBigInt) {
						$table->bigInteger('user_id')->unsigned();
					} elseif ($userIdColtype == 'integer') {
						$table->integer('user_id')->unsigned();
					}
					$table->foreign('user_id')
						->references('id')
						->on($tablenames['auth']['users'])
						->onDelete('cascade');
				}

				/**
				 * LANGUAGE (optional)
				 */
				if ($entity->columns->has_lang) {
					$table->string('language')->nullable();
					if ($useBigInt) {
						$table->bigInteger('language_parent')->unsigned()->nullable();
					} else {
						$table->integer('language_parent')->unsigned()->nullable();
					}
				}

				/**
				 * CONTENT
				 */
				$table->string('title')->nullable();

				/**
				 * SLUG (optional)
				 */
				if ($entity->columns->has_slug) {
					$table->string('slug')->nullable();
					$table->boolean('slug_lock')->default(0);
				}

				/**
				 * LEAD (optional)
				 */
				if ($entity->columns->has_lead) {
					$table->text('lead')->nullable();
				}

				/**
				 * BODY (optional)
				 */
				if ($entity->columns->has_body) {
					$table->text('body')->nullable();
				}

				/**
				 * TIMESTAMPS
				 */
				$table->timestamps();
				$table->timestamp('deleted_at')->nullable();

				/**
				 * STATUS (optional)
				 */
				if ($entity->columns->has_status) {
					$table->boolean('publish')->default(0);
					$table->timestamp('publish_from')->nullable();
				}

				/**
				 * HIDE IN LIST (optional)
				 */
				if ($entity->columns->has_hideinlist) {
					$table->boolean('publish_hide')->default(0);
				}

				/**
				 * EXPIRATION (optional)
				 */
				if ($entity->columns->has_expiration) {
					$table->timestamp('publish_to')->nullable();
				}

				/**
				 * APP (optional)
				 */
				if ($entity->columns->has_app) {
					$table->boolean('show_in_app')->default(0);
				}

				/**
				 * RECORD LOCK
				 */
				$table->timestamp('locked_at')->nullable();
				if ($useBigInt) {
					$table->bigInteger('locked_by')->nullable()->unsigned();
				} else {
					$table->integer('locked_by')->nullable()->unsigned();
				}

				$table->foreign('locked_by')
					->references('id')
					->on($tablenames['auth']['users'])
					->onDelete('cascade');

				/**
				 * CGROUP (optional)
				 */
				if ($entity->columns->has_groups) {
					$table->string('cgroup')->nullable();
				}

				/**
				 * SORTABLE (optional)
				 */
				if ($entity->columns->is_sortable) {
					$table->integer('position')->unsigned()->nullable()->index();
				}

				// patch 6.2.23 - start
				if ($entity->egroup->key == 'form') {
					$table->string('ipaddress')->nullable();
				}
				// patch 6.2.23 - end

			});

		}

	}

	/**
	 * @param object $entity
	 * @param string $column
	 * @param int $position
	 * @return string|null
	 */
	private function builderGetColumnPosition(object $entity, string $column, int $position)
	{

		$after = null;

		if ($position == 1) {

			// default position
			$default = 'id';

			// user_id
			if ($column == 'user_id') {
				$after = $default;
			}

			// language
			if ($column == 'language') {
				if ($entity->columns->has_user) {
					$after = 'user_id';
				} else {
					$after = $default;
				}
			}
			// language
			if ($column == 'language_parent') {
				$after = 'language';
			}

		} elseif ($position == 2) {

			// default position
			$default = 'title';

			// slug
			if ($column == 'slug') {
				$after = $default;
			}
			// slug_lock
			if ($column == 'slug_lock') {
				$after = 'slug';
			}

			// lead
			if ($column == 'lead') {
				if ($entity->columns->has_slug) {
					$after = 'slug_lock';
				} else {
					$after = $default;
				}
			}

			// body
			if ($column == 'body') {
				if ($entity->columns->has_lead) {
					$after = 'lead';
				} elseif ($entity->columns->has_slug) {
					$after = 'slug_lock';
				} else {
					$after = $default;
				}
			}

		} elseif ($position == 3) {

			// default position
			$default = 'deleted_at';

			//publish
			if ($column == 'publish') {
				$after = $default;
			}

			// publish_hide
			if ($column == 'publish_hide') {
				$after = 'publish';
			}

			// publish_from
			if ($column == 'publish_from') {
				$after = 'publish';
			}

			// publish_to
			if ($column == 'publish_to') {
				$after = 'publish_from';
			}

			// show_in_app
			if ($column == 'show_in_app') {
				if ($entity->columns->has_expiration) {
					$after = 'publish_to';
				} elseif ($entity->columns->has_status) {
					$after = 'publish_from';
				} else {
					$after = $default;
				}
			}

		} elseif ($position == 4) {

			// default position
			$default = 'locked_by';

			// cgroup
			if ($column == 'cgroup') {
				$after = $default;
			}

			// position
			if ($column == 'position') {
				if ($entity->columns->has_groups) {
					$after = 'cgroup';
				} else {
					$after = $default;
				}
			}

		}

		return $after;

	}

	/**
	 * @param object $entity
	 * @param string $tablename
	 * @return void
	 */
	private function builderCheckFieldColumns(object $entity, string $tablename)
	{

		// get entity fields from database
		$fields = $entity->customcolumns()->get();

		// get supported field types from config
		$fieldTypes = json_decode(json_encode(config('lara-admin.fieldTypes')), false);

		foreach ($fields as $field) {

			$colname = $field['fieldname'];
			$coltype = $field['fieldtype'];

			// get real field type
			$realcoltype = $fieldTypes->$coltype->type;
			$needs_check = $fieldTypes->$coltype->check;

			if ($needs_check) {

				if (Schema::hasColumn($tablename, $colname)) {

					// check column type
					$db_coltype = Schema::getColumnType($tablename, $colname);

					if ($db_coltype != $realcoltype) {

						// drop old column
						$this->builderDropEntityColumn($tablename, $colname);

						// add new column
						$this->builderAddEntityColumn($tablename, $colname, $coltype);

					}

				} else {

					// add new column
					$this->builderAddEntityColumn($tablename, $colname, $coltype);

				}

			}

			if ($coltype == 'geolocation') {

				// check the necessary columns for Geo Location
				$this->builderCheckGeoColumns($entity, $tablename);

			}

		}

	}

	/**
	 * @param object $entity
	 * @param string $tablename
	 * @return void
	 */
	private function builderCheckGeoColumns(object $entity, string $tablename)
	{

		// check LATITUDE
		if (!Schema::hasColumn($tablename, 'latitude')) {

			$entity->customcolumns()->create([
				'fieldtitle'         => 'Latitude',
				'fieldname'          => 'latitude',
				'fieldtype'          => 'decimal108',
				'fieldhook'          => 'after',
				'fielddata'          => '',
				'fieldstate'         => 'enabledif',
				'condition_field'    => 'geolocation',
				'condition_operator' => 'isequal',
				'condition_value'    => 'manual',
				'field_lock'         => 1,
			]);

			$this->builderAddEntityColumn($tablename, 'latitude', 'decimal108');
		}

		// check LONGITUDE
		if (!Schema::hasColumn($tablename, 'longitude')) {

			$entity->customcolumns()->create([
				'fieldtitle'         => 'Longitude',
				'fieldname'          => 'longitude',
				'fieldtype'          => 'decimal118',
				'fieldhook'          => 'after',
				'fielddata'          => '',
				'fieldstate'         => 'enabledif',
				'condition_field'    => 'geolocation',
				'condition_operator' => 'isequal',
				'condition_value'    => 'manual',
				'field_lock'         => 1,
			]);

			$this->builderAddEntityColumn($tablename, 'longitude', 'decimal118');

		}

		// check ADDRESS
		if (!Schema::hasColumn($tablename, 'address')) {

			$entity->customcolumns()->create([
				'fieldtitle' => 'Address',
				'fieldname'  => 'address',
				'fieldtype'  => 'string',
				'fieldhook'  => 'after',
				'fielddata'  => '',
				'fieldstate' => 'enabled',
				'field_lock' => 1,
			]);

			$this->builderAddEntityColumn($tablename, 'address', 'string');
		}

		// check PCODE
		if (!Schema::hasColumn($tablename, 'pcode')) {

			$entity->customcolumns()->create([
				'fieldtitle' => 'Postal Code',
				'fieldname'  => 'pcode',
				'fieldtype'  => 'string',
				'fieldhook'  => 'after',
				'fielddata'  => '',
				'fieldstate' => 'enabled',
				'field_lock' => 1,
			]);

			$this->builderAddEntityColumn($tablename, 'pcode', 'string');
		}

		// check CITY
		if (!Schema::hasColumn($tablename, 'city')) {

			$entity->customcolumns()->create([
				'fieldtitle' => 'City',
				'fieldname'  => 'city',
				'fieldtype'  => 'string',
				'fieldhook'  => 'after',
				'fielddata'  => '',
				'fieldstate' => 'enabled',
				'field_lock' => 1,
			]);

			$this->builderAddEntityColumn($tablename, 'city', 'string');
		}

		// check COUNTRY
		if (!Schema::hasColumn($tablename, 'country')) {

			$entity->customcolumns()->create([
				'fieldtitle' => 'Country',
				'fieldname'  => 'country',
				'fieldtype'  => 'string',
				'fieldhook'  => 'after',
				'fielddata'  => '',
				'fieldstate' => 'enabled',
				'field_lock' => 1,
			]);

			$this->builderAddEntityColumn($tablename, 'country', 'string');
		}

	}

	/**
	 * @param object $entity
	 * @param string $tablename
	 * @return void
	 */
	private function builderCheckRelatedColumns(object $entity, string $tablename)
	{

		$relations = $entity->relations;

		foreach ($relations as $relation) {

			if ($relation->type == 'belongsTo') {

				$related_entity_id = $relation->related_entity_id;
				$relatedEntity = Entity::find($related_entity_id);
				$colname = $relation->foreign_key;

				$prefix = $relatedEntity->egroup->key . '_prefix';
				$reltablename = config('lara-common.database.entity.' . $prefix) . str_plural($relatedEntity->entity_key);

				if (!Schema::hasColumn($tablename, $colname)) {

					// add column (with foreign key)
					$this->builderAddColumnWithForeignKey($tablename, $colname, $reltablename);

				}

			}

		}

	}

	/**
	 * @param object $entity
	 * @param string $tablename
	 * @return void
	 */
	private function builderCheckAbandonedColumns(object $entity, string $tablename)
	{

		$columns = Schema::getColumnListing($tablename);

		// get default columns
		$defaultColumns = array_keys(config('lara-admin.defaultColumns'));

		// get optional columns
		$optionalColumns = array_keys(config('lara-admin.optionalColumns'));

		// get fields
		$fields = $entity->customcolumns()->get();
		$fieldColumns = $fields->pluck('fieldname')->toArray();

		// get related columns
		$relatedCols = $entity->relations->unique('foreign_key')->pluck('foreign_key')->toArray();

		foreach ($columns as $column) {
			if (!in_array($column, $defaultColumns) && !in_array($column, $optionalColumns) && !in_array($column, $fieldColumns) && !in_array($column, $relatedCols)) {
				$this->builderDropEntityColumn($tablename, $column);
			}
		}

	}

	/**
	 * @param object $entity
	 * @return void
	 */
	private function builderCheckViews(object $entity)
	{

		$views = $entity->views;

		foreach ($views as $view) {

			$viewfile = 'content.' . $entity->entity_key . '.' . $view->filename;

			if (!View::exists($viewfile)) {
				$this->builderMakeView($entity, $view);
			}

		}

	}

	/**
	 * @param object $entity_key
	 * @return void
	 */
	private function builderCheckMediaFolder(object $entity)
	{

		// Images
		$entityDiskForImages = $entity->objectrelations->disk_images;
		$entityImagePath = Storage::disk($entityDiskForImages)->path($entity->entity_key);

		if (!File::isDirectory($entityImagePath)) {
			File::makeDirectory($entityImagePath);
		}

		// Videos
		$entityDiskForVideos = $entity->objectrelations->disk_videos;
		$entityVideoPath = Storage::disk($entityDiskForVideos)->path($entity->entity_key);

		if (!File::isDirectory($entityVideoPath)) {
			File::makeDirectory($entityVideoPath);
		}

		// Files
		$entityDiskForFiles = $entity->objectrelations->disk_files;
		$entityFilePath = Storage::disk($entityDiskForFiles)->path($entity->entity_key);

		if (!File::isDirectory($entityFilePath)) {
			File::makeDirectory($entityFilePath);
		}

	}

	/**
	 * @param Request $request
	 * @param string $entity_key
	 * @return mixed
	 */
	private function builderCreateNewEntity(Request $request, string $entity_key)
	{

		$entity_ucf = ucfirst($entity_key); // Page, Blog
		$entity_ucf_plural = ucfirst(str_plural($entity_key)); // Pages, Blogs

		$entitygroup = Entitygroup::find($request->input('group_id'));

		if ($entitygroup->path == 'Eve') {
			$entity_model_class = 'Eve\\Models\\' . $entity_ucf;
		} elseif ($entitygroup->path == 'Lara') {
			// page, module page
			$entity_model_class = 'Lara\\Common\\Models\\' . $entity_ucf;
		} else {
			return false;
		}
		$entity_controller = $entity_ucf_plural . 'Controller';

		$request->merge(['title' => $entity_ucf_plural]);
		$request->merge(['entity_model_class' => $entity_model_class]);
		$request->merge(['entity_controller' => $entity_controller]);

		$request->merge(['resource_routes' => 1]);

		/*
		$request->merge(['is_sortable' => 0]);
		$request->merge(['sort_field' => 'id']);
		$request->merge(['sort_order' => 'asc']);
		*/

		// create dynamic entity in database
		$entity = Entity::create($request->all());
		if (in_array($entity->egroup->key, ['entity', 'page', 'block'])) {
			$entity->columns()->create([
				'has_user' => 1,
				'has_lang' => 1,
				'has_slug' => 1,
			]);
		} else {
			$entity->columns()->create([
				'has_user' => 0,
				'has_lang' => 0,
				'has_slug' => 0,
			]);
		}
		$entity->objectrelations()->create([]);
		$entity->panels()->create([]);

		// create new Admin controller
		$this->builderMakeAdminController($entity);

		// create new Front controller
		$this->builderMakeFrontController($entity);

		// create new model
		$this->builderMakeModel($entity);

		// create new entity
		$this->builderMakeEntity($entity);

		// check health
		$this->builderCheckEntityHealth($entity_key, true);

		// forms
		if ($entity->egroup->key == 'form') {

			$columns = $entity->columns;
			$columns->has_fields = 1;
			$columns->save();

			$entity->views()->create([
				'title'    => $entity_ucf,
				'method'   => 'form',
				'filename' => 'form',
				'type'     => '_form',
				'showtags' => 'none',
				'paginate' => 0,
				'infinite' => 0,
				'prevnext' => 0,
				'publish'  => 1,
			]);

			$this->builderMakeEmailView($entity);

			// check health again
			$this->builderCheckEntityHealth($entity_key, true);

		}

		// Clear caches
		Artisan::call('cache:clear');
		Artisan::call('config:clear');
		Artisan::call('view:clear');

		$request->session()->put('routecacheclear', true);

		return $entity;

	}

	/**
	 * @param string $tablename
	 * @param string $colname
	 * @param string $coltype
	 * @return void
	 */
	private function builderAddEntityColumn(string $tablename, string $colname, string $coltype)
	{

		// determine real column type
		$realcoltype = $this->getRealBuilderColumnType($coltype);

		Schema::table($tablename, function ($table) use ($colname, $coltype, $realcoltype) {

			if ($realcoltype == 'string') {
				$table->string($colname)->after('title')->nullable();
			}
			if ($realcoltype == 'text') {
				$table->text($colname)->nullable()->after('title');
			}
			if ($realcoltype == 'email') {
				$table->email($colname)->after('title');
			}
			if ($realcoltype == 'datetime') {
				$table->timestamp($colname)->nullable()->after('title');
			}
			if ($realcoltype == 'date') {
				$table->date($colname)->nullable()->after('title');
			}
			if ($realcoltype == 'time') {
				$table->time($colname)->nullable()->after('title');
			}
			if ($realcoltype == 'boolean') {
				$table->boolean($colname)->default(0)->after('title');
			}
			if ($realcoltype == 'integer') {
				if ($coltype == 'integer') {
					$table->integer($colname)->default(0)->after('title');
				}
				if ($coltype == 'intunsigned') {
					$table->integer($colname)->unsigned()->default(0)->after('title');
				}
			}
			if ($realcoltype == 'decimal') {
				if ($coltype == 'decimal101') {
					$table->decimal($colname, 10, 1)->after('title');
				}
				if ($coltype == 'decimal142') {
					$table->decimal($colname, 14, 2)->after('title');
				}
				if ($coltype == 'decimal164') {
					$table->decimal($colname, 16, 4)->after('title');
				}
				if ($coltype == 'decimal108') {
					$table->decimal($colname, 10, 8)->after('title');
				}
				if ($coltype == 'decimal118') {
					$table->decimal($colname, 11, 8)->after('title');
				}
			}

		});

	}

	/**
	 * @param string $tablename
	 * @param string $colname
	 * @param string $reltablename
	 * @return void
	 */
	private function builderAddColumnWithForeignKey(string $tablename, string $colname, string $reltablename)
	{

		$tablenames = config('lara-common.database');
		$useBigInt = Schema::getColumnType($tablenames['auth']['users'], 'id') == 'bigint';

		// add column (with foreign key)
		Schema::table($tablename, function ($table) use ($colname, $reltablename, $useBigInt) {
			if($useBigInt) {
				$table->bigInteger($colname)->unsigned()->after('id');
			} else {
				$table->integer($colname)->unsigned()->after('id');
			}
			$table->foreign($colname)
				->references('id')
				->on($reltablename)
				->onDelete('cascade');
		});

	}

	/**
	 * @param string $tablename
	 * @param string $colname
	 * @param bool $force
	 * @param bool $hasforeignkey
	 * @return void
	 */
	private function builderDropEntityColumn(string $tablename, string $colname, $force = false, $hasforeignkey = false)
	{

		if (Schema::hasColumn($tablename, $colname)) {

			Schema::disableForeignKeyConstraints();

			Schema::table($tablename, function ($table) use ($colname, $force, $hasforeignkey) {

				if ($hasforeignkey) {
					$table->dropForeign([$colname]);
				}

				if ($force) {
					$table->dropColumn($colname);
				} else {
					if (!starts_with($colname, '_')) {
						$table->renameColumn($colname, '_' . $colname);
						flash()->overlay('Please, delete column [' . $colname . '] manually!', 'Database Alert!');
					}
				}

			});

			Schema::enableForeignKeyConstraints();

		}

	}

	/**
	 * @param object $entity
	 * @return void
	 */
	private function builderCheckAbilitiesExist(object $entity)
	{

		$abilities = config('lara-admin.abilities');

		foreach ($abilities as $ability) {

			if (!Ability::where('name', $ability)
				->where('entity_key', $entity->entity_key)
				->exists()) {

				$data = [
					'name'        => $ability,
					'title'       => ucfirst($entity->entity_key) . ' ' . $ability,
					'entity_type' => $entity->getEntityModelClass(),
					'entity_key'  => $entity->entity_key,

				];

				$newAbility = new Userability();
				$newAbility->forceFill($data);
				$newAbility->save();

				// find roles with level 100 and assign every possible ability
				$roles = Role::where('level', 100)->pluck('name')->toArray();
				foreach ($roles as $role) {
					Bouncer::allow($role)->to($ability, $entity->getEntityModelClass());
				}

			}

		}

		Bouncer::refresh();

	}

	/**
	 * @param object $entity
	 * @param string $colname
	 * @return bool
	 */
	private function builderIsFieldValid(object $entity, string $colname)
	{

		$prefix = $entity->egroup->key . '_prefix';
		$tablename = config('lara-common.database.entity.' . $prefix) . str_plural($entity->entity_key);

		if (Schema::hasColumn($tablename, $colname)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @param bool $force
	 * @return void
	 */
	private function builderProcessRelated(Request $request, object $entity, bool $force)
	{

		$prefix = $entity->egroup->key . '_prefix';
		$tablename = config('lara-common.database.entity.' . $prefix) . str_plural($entity->entity_key);

		$relations = $entity->relations()->get();

		$entityModelClass = $entity->getEntityModelClass();
		$count = $entityModelClass::count();

		if ($count == 0 || $force == true) {

			$filterfound = false;

			foreach ($relations as $relation) {

				$rid = $relation->id;

				if ($request->input('_rdelete_' . $rid) == 'DELETE') {

					if ($relation->type == 'belongsTo') {

						// define fieldname
						$colname = $relation->foreign_key;

						// define foreign key
						$foreignkey = $tablename . '_' . $colname . '_foreign';

						// drop column
						Schema::table($tablename, function ($table) use ($colname, $foreignkey) {
							$table->dropForeign($foreignkey);
							$table->dropColumn($colname);
						});
					}

					// delete relation
					$relation->delete();

				} else {

					// update filter field

					$is_filter = $request->input('_mfilt_' . $rid);

					// only one relation can be a filter
					if ($filterfound === false) {
						$relation->update([
							'is_filter' => $is_filter,
						]);
					}

					if ($is_filter == true) {
						$filterfound = true;
					}

				}

			}

		}

		if (!empty($request->input('_new_relation_type'))) {

			if (is_numeric($request->input('_new_relation_relid'))) {

				$entity_id = $entity->id;
				$related_entity_id = $request->input('_new_relation_relid');
				$relation_type = $request->input('_new_relation_type');

				// get related Entity
				$relatedEntity = Entity::find($related_entity_id);

				// define column name (foreign key)
				if (!empty($request->input('_new_relation_foreignkey'))) {
					$colname = $request->input('_new_relation_foreignkey');
				} else {
					$colname = $entity->entity_key . '_id';
				}

				// create relation (hasOne/hasMany)
				$entity->relations()->create([
					'type'              => $relation_type,
					'related_entity_id' => $related_entity_id,
					'foreign_key'       => $colname,
					'is_filter'         => 0,
				]);

				// create inverse (belongsTo)
				$relatedEntity->relations()->create([
					'type'              => 'belongsTo',
					'related_entity_id' => $entity_id,
					'foreign_key'       => $colname,
					'is_filter'         => 0,
				]);

				// related table name
				$relprefix = $relatedEntity->egroup->key . '_prefix';
				$reltablename = config('lara-common.database.entity.' . $relprefix) . str_plural($relatedEntity->entity_key);

				// add column (with foreign key)
				$this->builderAddColumnWithForeignKey($reltablename, $colname, $tablename);

			} else {
				flash('Error: no entity selected')->error();
			}

		}

	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @param bool $force
	 * @return void
	 */
	private function builderProcessCustomColumns(Request $request, object $entity, bool $force)
	{

		$prefix = $entity->egroup->key . '_prefix';
		$tablename = config('lara-common.database.entity.' . $prefix) . str_plural($entity->entity_key);

		// get all form fields
		$fields = $entity->customcolumns()->get();

		$entityModelClass = $entity->getEntityModelClass();
		$count = $entityModelClass::count();

		if ($count == 0 || $force == true) {

			// model has no data yet, so it's safe to process existing fields
			// or: force is true
			foreach ($fields as $field) {

				$mid = $field->id;

				if ($request->input('_mdelete_' . $mid) == 'DELETE') {

					$field->delete();

					// drop column from database
					$this->builderDropEntityColumn($tablename, $field['fieldname']);

				} else {

					$reqFieldname = $request->input('_mname_' . $mid);
					$newFieldname = str_slug($reqFieldname, '_');

					// set fixed fieldname for type geolocation
					if ($request->input('_mtype_' . $mid) == 'geolocation') {
						$newFieldname = 'geolocation';
					}

					if ($reqFieldname != $field->getOriginal('fieldname')) {

						// fieldname has changed, check if fieldname is valid
						if ($this->builderIsFieldValid($entity, $newFieldname)) {

							$field->update([
								'fieldtitle'         => $request->input('_mtitle_' . $mid),
								'fieldname'          => $newFieldname,
								'fieldtype'          => $request->input('_mtype_' . $mid),
								'fieldhook'          => $request->input('_mhook_' . $mid),
								'fielddata'          => $request->input('_mdata_' . $mid),
								'primary'            => $request->input('_mprim_' . $mid),
								'required'           => $request->input('_mreq_' . $mid),
								'fieldstate'         => $request->input('_mstate_' . $mid),
								'condition_field'    => $request->input('_mcondfield_' . $mid),
								'condition_operator' => $request->input('_mcondop_' . $mid),
								'condition_value'    => $request->input('_mcondval_' . $mid),
							]);

							$this->builderCheckFieldColumns($entity, $tablename);

						} else {
							flash('Fieldname not valid')->error();
						}

					} else {

						// fieldname has not changed, update model
						$field->update([
							'fieldtitle'         => $request->input('_mtitle_' . $mid),
							'fieldname'          => $newFieldname,
							'fieldtype'          => $request->input('_mtype_' . $mid),
							'fieldhook'          => $request->input('_mhook_' . $mid),
							'fielddata'          => $request->input('_mdata_' . $mid),
							'primary'            => $request->input('_mprim_' . $mid),
							'required'           => $request->input('_mreq_' . $mid),
							'fieldstate'         => $request->input('_mstate_' . $mid),
							'condition_field'    => $request->input('_mcondfield_' . $mid),
							'condition_operator' => $request->input('_mcondop_' . $mid),
							'condition_value'    => $request->input('_mcondval_' . $mid),
						]);

						$this->builderCheckFieldColumns($entity, $tablename);

					}
				}
			}

		}

		if (!empty($request->input('_new_fieldname'))) {

			$reqFieldname = $request->input('_new_fieldname');
			$newFieldname = str_slug($reqFieldname, '_');

			// set fixed fieldname for type geolocation
			if ($request->input('_new_fieldtype') == 'geolocation') {
				$newFieldname = 'geolocation';
			}

			if ($this->builderIsFieldValid($entity, $newFieldname)) {

				$field = $entity->customcolumns()->create([
					'fieldtitle'         => $request->input('_new_fieldtitle'),
					'fieldname'          => $newFieldname,
					'fieldtype'          => $request->input('_new_fieldtype'),
					'fieldhook'          => $request->input('_new_fieldhook'),
					'fielddata'          => '',
					'primary'            => 0,
					'required'           => 0,
					'fieldstate'         => 'enabled',
					'condition_field'    => null,
					'condition_operator' => null,
					'condition_value'    => null,
					'field_lock'         => 0,
				]);

				// add column to database
				$this->builderAddEntityColumn($tablename, $field->fieldname, $field->fieldtype);

			} else {
				flash('Error: Fieldname not valid')->error();
			}

		}

	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @return void
	 */
	private function builderProcessViews(Request $request, object $entity)
	{

		$views = $entity->views()->get();

		foreach ($views as $view) {

			$vid = $view['id'];

			if ($request->input('_vdelete_' . $vid) == 'DELETE') {
				$view->delete();
			} else {
				$view->update([
					'title'    => $request->input('_vtitle_' . $vid),
					'type'     => $request->input('_vtype_' . $vid),
					'showtags' => $request->input('_vtag_' . $vid),
					'paginate' => $request->input('_vpaginate_' . $vid),
					'prevnext' => $request->input('_vprevnext_' . $vid),
					'publish'  => $request->input('_vpublish_' . $vid),
				]);
			}

		}

		if (!empty($request->input('_new_view_method'))) {

			if ($request->input('_new_view_method') == 'custom') {
				$newmethod = str_slug($request->input('_new_custom_method'), '');
			} else {
				$newmethod = str_slug($request->input('_new_view_method'), '');
			}

			$newfilename = $newmethod;
			$newtitle = $request->input('_new_view_title');

			if ($newmethod == 'show') {
				$type = '_single';
			} else {
				$type = 'list';
			}

			$newview = $entity->views()->create([
				'method'   => $newmethod,
				'filename' => $newfilename,
				'title'    => $newtitle,
				'type'     => $type,
				'showtags' => 'none',
				'paginate' => 0,
				'infinite' => 0,
				'prevnext' => 0,
				'publish'  => 1,
			]);

			// create the actual view file
			$this->builderMakeView($entity, $newview);

			// add custom method to the Front Controller
			if ($request->input('_new_view_method') == 'custom') {
				$this->builderUpdateFrontController($entity, $newmethod);
			}

		}

	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @return void
	 */
	private function builderProcessColumns(Request $request, object $entity)
	{

		// reset group values
		if (!$request->input('_has_groups')) {
			$request->merge(['_group_values', '']);
			$request->merge(['_group_default', '']);
		}

		// reset sort field
		if ($request->input('_is_sortable')) {
			$request->merge(['_sort_field' => 'position']);
			$request->merge(['_sort_order' => 'asc']);
			$request->merge(['_sort2_field' => null]);
			$request->merge(['_sort2_order' => null]);
		}

		$entity->columns->update([
			'has_user'       => $request->input('_has_user'),
			'has_lang'       => $request->input('_has_lang'),
			'has_slug'       => $request->input('_has_slug'),
			'has_lead'       => $request->input('_has_lead'),
			'has_body'       => $request->input('_has_body'),
			'has_status'     => $request->input('_has_status'),
			'has_hideinlist' => $request->input('_has_hideinlist'),
			'has_expiration' => $request->input('_has_expiration'),
			'has_app'        => $request->input('_has_app'),
			'has_groups'     => $request->input('_has_groups'),
			'group_values'   => $request->input('_group_values'),
			'group_default'  => $request->input('_group_default'),
			'is_sortable'    => $request->input('_is_sortable'),
			'sort_field'     => $request->input('_sort_field'),
			'sort_order'     => $request->input('_sort_order'),
			'sort2_field'    => $request->input('_sort2_field'),
			'sort2_order'    => $request->input('_sort2_order'),
			'has_fields'     => $request->input('_has_fields'),
		]);

	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @return void
	 */
	private function builderProcessObjectRelations(Request $request, object $entity)
	{

		if (!$request->input('_has_tags')) {
			$request->merge(['_tag_default' => '']);
		}

		if (!$request->has('_max_images')) {
			$request->merge(['_max_images', 1]);
		}

		if (!$request->has('_max_videos')) {
			$request->merge(['_max_videos', 1]);
		}

		if (!$request->has('_max_videofiles')) {
			$request->merge(['_max_videofiles', 1]);
		}

		if (!$request->has('_max_files')) {
			$request->merge(['_max_files', 1]);
		}

		$entity->objectrelations->update([
			'has_seo'        => $request->input('_has_seo'),
			'has_opengraph'  => $request->input('_has_opengraph'),
			'has_layout'     => $request->input('_has_layout'),
			'has_related'    => $request->input('_has_related'),
			'is_relatable'   => $request->input('_is_relatable'),
			'has_tags'       => $request->input('_has_tags'),
			'tag_default'    => $request->input('_tag_default'),
			'has_sync'       => $request->input('_has_sync'),
			'has_images'     => $request->input('_has_images'),
			'has_videos'     => $request->input('_has_videos'),
			'has_videofiles' => $request->input('_has_videofiles'),
			'has_files'      => $request->input('_has_files'),
			'max_images'     => $request->input('_max_images'),
			'max_videos'     => $request->input('_max_videos'),
			'max_videofiles' => $request->input('_max_videofiles'),
			'max_files'      => $request->input('_max_files'),
			'disk_images'    => $request->input('_disk_images'),
			'disk_videos'    => $request->input('_disk_videos'),
			'disk_files'     => $request->input('_disk_files'),
		]);

	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @return void
	 */
	private function builderProcessPanels(Request $request, object $entity)
	{

		$entity->panels->update([
			'has_search'    => $request->input('_has_search'),
			'has_batch'     => $request->input('_has_batch'),
			'has_filters'   => $request->input('_has_filters'),
			'show_author'   => $request->input('_show_author'),
			'show_status'   => $request->input('_show_status'),
			'has_tiny_lead' => $request->input('_has_tiny_lead'),
			'has_tiny_body' => $request->input('_has_tiny_body'),
		]);

	}

	/**
	 * @param object $entity
	 * @param bool $force
	 * @return bool
	 */
	private function builderMakeAdminController(object $entity, $force = false)
	{
		$eGroupPath = $entity->egroup->path;

		if ($eGroupPath == 'Eve') {

			$ds = DIRECTORY_SEPARATOR;

			$laraPath = config('lara.lara_path');
			$evePath = config('lara.eve_path');

			$egroup = $entity->egroup->key;
			$egroupDir = ucfirst($egroup);

			$entityName = ucfirst($entity->entity_key); // Page, Blog, etc
			$class = str_plural($entityName) . 'Controller';

			if ($egroup == 'entity' || $egroup == 'form') {

				$namespace = 'Eve\\Http\\Controllers\\Admin\\' . $egroupDir;

				$tPath = $evePath . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Admin' . $ds . 'Templates';
				$cPath = $evePath . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Admin' . $ds . $egroupDir;

			} else {

				$namespace = 'Lara\\Admin\\Http\\Controllers\\' . $egroupDir;

				$tPath = $laraPath . $ds . 'admin' . $ds . 'src' . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Templates';
				$cPath = $laraPath . $ds . 'admin' . $ds . 'src' . $ds . 'Http' . $ds . 'Controllers' . $ds . $egroupDir;

			}

			$templateFile = $tPath . $ds . 'controller';

			$template = (string)$this->builderLoadTemplate($templateFile);
			$template = $this->buildController($template, $namespace, $entityName, $class);

			$newFilePath = $cPath . $ds . str_plural($entityName) . 'Controller.php';

			$this->unlockFilesInDir($cPath);

			if (!File::exists($newFilePath) || $force == true) {
				$this->builderWriteFile($newFilePath, $template);
			}

			$this->lockFilesInDir($cPath);

			return true;

		} else {

			return false;

		}

	}

	/**
	 * @param object $entity
	 * @param bool $force
	 * @return bool
	 */
	private function builderMakeFrontController(object $entity, $force = false)
	{

		$egroup = $entity->egroup->key;
		$eGroupPath = $entity->egroup->path;

		if ($eGroupPath == 'Eve') {

			$ds = DIRECTORY_SEPARATOR;

			$evePath = config('lara.eve_path');

			$egroupDir = ucfirst($egroup);

			$entityName = ucfirst($entity->entity_key); // Page, Blog, etc
			$class = str_plural($entityName) . 'Controller';

			$namespace = 'Eve\\Http\\Controllers\\Front\\' . $egroupDir;

			$tPath = $evePath . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Front' . $ds . 'Templates';
			$cPath = $evePath . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Front' . $ds . $egroupDir;

			if ($egroup == 'form') {
				$templateFile = $tPath . $ds . 'formcontroller';
			} else {
				$templateFile = $tPath . $ds . 'controller';
			}

			$template = (string)$this->builderLoadTemplate($templateFile);
			$template = $this->buildController($template, $namespace, $entityName, $class);

			$newFilePath = $cPath . $ds . str_plural($entityName) . 'Controller.php';

			$this->unlockFilesInDir($cPath);

			if (!File::exists($newFilePath) || $force == true) {
				$this->builderWriteFile($newFilePath, $template);
			}

			$this->lockFilesInDir($cPath);

			return true;

		} else {

			return false;

		}
	}

	/**
	 * @param object $entity
	 * @param string $newmethod
	 * @return void
	 */
	private function builderUpdateFrontController(object $entity, string $newmethod)
	{

		$egroup = $entity->egroup->key;
		$eGroupPath = $entity->egroup->path;

		if ($eGroupPath == 'Eve') {

			$locked = config('lara-admin.locked_front_controllers');

			if (!in_array($entity->entity_key, $locked)) {

				$ds = DIRECTORY_SEPARATOR;

				$evePath = config('lara.eve_path');

				$egroupDir = ucfirst($egroup);

				$tPath = $evePath . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Front' . $ds . 'Templates';
				$cPath = $evePath . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Front' . $ds . $egroupDir;

				$controllerFile = $cPath . $ds . $entity->getEntityController() . '.php';

				// read current controller
				$controller = (string)$this->builderLoadTemplate($controllerFile);

				// check if method already exists in controller
				if (strpos($controller, 'function ' . $newmethod . '(') === false) {

					// add new method to controller
					$methodTemplateFile = $tPath . $ds . 'method';

					$meth = (string)$this->builderLoadTemplate($methodTemplateFile);
					$meth = $this->buildMethod($meth, $newmethod);

					$pos = strrpos($controller, "}");

					$controller = substr($controller, 0, $pos) . $meth . '}' . "\n\n";

					$this->unlockFilesInDir($cPath);

					$this->builderWriteFile($controllerFile, $controller);

					$this->lockFilesInDir($cPath);

				}

			}

		}

	}

	/**
	 * @param object $entity
	 * @param object $view
	 * @param bool $force
	 * @return void
	 */
	private function builderMakeView(object $entity, object $view, $force = false)
	{

		$ds = DIRECTORY_SEPARATOR;

		$egroup = $entity->egroup->key;

		$themespath = config('theme.base_path');

		$templatebase = $themespath . $ds . config('theme.parent') . $ds . 'views';

		$templateclient = $themespath . $ds . config('theme.active') . $ds . 'views';

		$methodpath = $templatebase . $ds . 'content' . $ds . '_templates' . $ds . $egroup . $ds . $view->method;
		$template = $methodpath . '.blade.php';

		if (!File::exists($template)) {
			// use default index template
			$methodpath = $templatebase . $ds . 'content' . $ds . '_templates' . $ds . $egroup . $ds . 'index';
			$template = $methodpath . '.blade.php';
		}

		// create view directory in client theme
		$destdir = $templateclient . $ds . 'content' . $ds . $entity->entity_key;

		if (!File::isDirectory($destdir)) {
			File::makeDirectory($destdir);
		}

		// copy view
		$dest = $destdir . $ds . $view->filename . '.blade.php';
		if (File::exists($template)) {
			File::copy($template, $dest);
		}

		// create partials directory
		$destpartialdir = $destdir . $ds . $view->method;
		if (!File::isDirectory($destpartialdir)) {
			File::makeDirectory($destpartialdir);
		}

		// copy partials
		if (File::isDirectory($methodpath)) {
			File::copyDirectory($methodpath, $destpartialdir);
		}

	}

	/**
	 * @param object $entity
	 * @return void
	 */
	private function builderMakeEmailView(object $entity)
	{

		$ds = DIRECTORY_SEPARATOR;

		$themespath = config('theme.base_path');
		$templatebase = $themespath . $ds . config('theme.parent') . $ds . 'views';
		$templateclient = $themespath . $ds . config('theme.active') . $ds . 'views';

		$methodpath = $templatebase . $ds . 'email' . $ds . '_templates' . $ds . 'form';

		$destdir = $templateclient . $ds . 'email' . $ds . $entity->entity_key;

		if (File::isDirectory($methodpath)) {
			File::copyDirectory($methodpath, $destdir);
		}

	}

	/**
	 * @param object $entity
	 * @param bool $force
	 * @return bool
	 */
	private function builderMakeModel(object $entity, $force = false)
	{

		$egroup = $entity->egroup->key;
		$eGroupPath = $entity->egroup->path;

		if ($eGroupPath == 'Eve') {

			$ds = DIRECTORY_SEPARATOR;

			$evePath = config('lara.eve_path');

			$entityName = ucfirst($entity->entity_key); // Page, Blog, etc
			$class = $entityName;
			$tableslug = str_plural(strtolower($entityName));

			$tPath = $evePath . $ds . 'Models' . $ds . 'Templates';
			$mPath = $evePath . $ds . 'Models';

			$modelFile = 'model';
			if ($entity->columns->has_lang) {
				$modelFile = $modelFile . '_lang';
			}
			if ($entity->columns->has_slug) {
				$modelFile = $modelFile . '_slug';
			}
			if ($entity->columns->is_sortable) {
				$modelFile = $modelFile . '_sort';
			}

			$templateFile = $tPath . $ds . $modelFile;

			$template = (string)$this->builderLoadTemplate($templateFile);
			$template = $this->buildModel($template, $class, $tableslug, $egroup);

			$newFilePath = $mPath . $ds . $entityName . '.php';

			$this->unlockFilesInDir($mPath);

			if (!File::exists($newFilePath) || $force == true) {
				$this->builderWriteFile($newFilePath, $template);
			}

			$this->unlockFilesInDir($mPath);

			return true;

		} else {

			return false;

		}

	}

	/**
	 * @param object $entity
	 * @return bool
	 */
	private function builderUpdateModel(object $entity)
	{

		$egroup = $entity->egroup->key;
		$eGroupPath = $entity->egroup->path;

		if ($eGroupPath == 'Eve') {

			// check if Model is locked
			// (locked models contain custom code)
			$locked = config('lara-admin.locked_models');

			if (!in_array($entity->entity_key, $locked)) {

				$ds = DIRECTORY_SEPARATOR;

				$evePath = config('lara.eve_path');

				$entityName = ucfirst($entity->entity_key);
				$class = $entityName;
				$tableslug = str_plural(strtolower($entityName));

				$tPath = $evePath . $ds . 'Models' . $ds . 'Templates';
				$mPath = $evePath . $ds . 'Models';

				$modelFile = 'model';
				if ($entity->columns->has_lang) {
					$modelFile = $modelFile . '_lang';
				}
				if ($entity->columns->has_slug) {
					$modelFile = $modelFile . '_slug';
				}
				if ($entity->columns->is_sortable) {
					$modelFile = $modelFile . '_sort';
				}

				$templateFile = $tPath . $ds . $modelFile;

				$template = (string)$this->builderLoadTemplate($templateFile);
				$template = $this->buildModel($template, $class, $tableslug, $egroup);

				// relations
				$relTemplateFile = $tPath . $ds . 'relation';

				$relations = $entity->relations;

				foreach ($relations as $relation) {

					$relationType = $relation->type;

					$relatedEntity = $relation->relatedEntity;

					$foreignkey = $relation->foreign_key;

					$modelclass = $relatedEntity->getEntityModelClass();

					if (str_contains($relationType, 'Many')) {
						$method = str_plural($relatedEntity->entity_key);
					} else {
						$method = $relatedEntity->entity_key;
					}

					$rel = (string)$this->builderLoadTemplate($relTemplateFile);
					$rel = $this->buildRelation($rel, $modelclass, $relationType, $method, $foreignkey);

					// add relation to model template

					$pos = strrpos($template, "}");

					$template = substr($template, 0, $pos) . $rel . '}' . "\n\n";
				}

				// mutators
				$mutTemplateFile = $tPath . $ds . 'mutator';

				$fields = $entity->customcolumns;

				foreach ($fields as $field) {

					$fieldtype = $field->fieldtype;
					$fieldname = $field->fieldname;
					$attribute = ucfirst(camel_case($fieldname));

					if ($fieldtype == 'datetime' || $fieldtype == 'date' || $fieldtype == 'time') {

						$mut = (string)$this->builderLoadTemplate($mutTemplateFile);
						$mut = $this->buildDateMutator($mut, $fieldname, $attribute);

						$pos = strrpos($template, "}");

						$template = substr($template, 0, $pos) . $mut . '}' . "\n\n";

					}

				}

				$newFilePath = $mPath . $ds . $entityName . '.php';

				$this->unlockFilesInDir($mPath);

				$this->builderWriteFile($newFilePath, $template);

				$this->lockFilesInDir($mPath);

				return true;

			} else {

				return false;
			}

		} else {

			return false;

		}

	}

	/**
	 * @param object $entity
	 * @param bool $force
	 * @return bool
	 */
	private function builderMakeEntity(object $entity, $force = false)
	{

		$eGroupPath = $entity->egroup->path;

		if ($eGroupPath == 'Eve') {

			$ds = DIRECTORY_SEPARATOR;

			$laraPath = config('lara.lara_path');
			$evePath = config('lara.eve_path');

			$entityName = ucfirst($entity->entity_key) . 'Entity';
			$class = $entityName;
			$path = $entity->egroup->path;

			if ($path == 'Eve') {
				$tPath = $evePath . $ds . 'Lara' . $ds . 'Templates';
				$mPath = $evePath . $ds . 'Lara';
			} elseif ($path == 'Lara') {
				$tPath = $laraPath . $ds . 'common' . $ds . 'src' . $ds . 'Lara' . $ds . 'Templates';
				$mPath = $laraPath . $ds . 'common' . $ds . 'src' . $ds . 'Lara';
			} else {
				return false;
			}

			$templateFile = $tPath . $ds . 'entity';

			$template = (string)$this->builderLoadTemplate($templateFile);
			$template = $this->buildEntity($template, $class, $entity->entity_key);

			$newFilePath = $mPath . $ds . $entityName . '.php';

			$this->unlockFilesInDir($mPath);

			if (!File::exists($newFilePath) || $force == true) {
				$this->builderWriteFile($newFilePath, $template);
			}

			$this->unlockFilesInDir($mPath);

			return true;

		} else {

			return false;
		}

	}

	/**
	 * @param object $entity
	 * @return bool
	 */
	private function builderUpdateEntity(object $entity)
	{

		$eGroupPath = $entity->egroup->path;

		if ($eGroupPath == 'Eve') {

			// check if Model is locked
			// (locked models contain custom code)
			$locked = config('lara-admin.locked_entities');

			if (!in_array($entity->entity_key, $locked)) {

				$ds = DIRECTORY_SEPARATOR;

				$laraPath = config('lara.lara_path');
				$evePath = config('lara.eve_path');

				$entityName = ucfirst($entity->entity_key) . 'Entity';
				$class = $entityName;
				$path = $entity->egroup->path;

				if ($path == 'Eve') {
					$tPath = $evePath . $ds . 'Lara' . $ds . 'Templates';
					$mPath = $evePath . $ds . 'Lara';
				} elseif ($path == 'Lara') {
					$tPath = $laraPath . $ds . 'common' . $ds . 'src' . $ds . 'Lara' . $ds . 'Templates';
					$mPath = $laraPath . $ds . 'common' . $ds . 'src' . $ds . 'Lara';
				} else {
					return false;
				}

				$templateFile = $tPath . $ds . 'entity';

				$template = (string)$this->builderLoadTemplate($templateFile);
				$template = $this->buildEntity($template, $class, $entity->entity_key);

				$newFilePath = $mPath . $ds . $entityName . '.php';

				$this->unlockFilesInDir($mPath);

				$this->builderWriteFile($newFilePath, $template);

				$this->lockFilesInDir($mPath);

				return true;

			} else {

				return false;
			}

		} else {

			return false;

		}

	}

	/**
	 * @param string $template
	 * @param string $namespace
	 * @param string $model
	 * @param string $class
	 * @return string
	 */
	private function buildController(string $template, string $namespace, string $model, string $class)
	{
		$template = str_replace([
			'$NAMESPACE$',
			'$MODEL$',
			'$CLASS$',
		], [
			$namespace,
			$model,
			$class,
		], $template);

		return $template;
	}

	/**
	 * @param string $template
	 * @param string $class
	 * @param string $tableslug
	 * @param string $group
	 * @return string
	 */
	private function buildModel(string $template, string $class, string $tableslug, string $group)
	{

		$tablename = config('lara-common.database.entity.' . $group . '_prefix') . $tableslug;

		$template = str_replace([
			'$CLASS$',
			'$TABLENAME$',
		], [
			$class,
			$tablename,
		], $template);

		return $template;
	}

	/**
	 * @param string $template
	 * @param string $class
	 * @param string $entity_key
	 * @return string
	 */
	private function buildEntity(string $template, string $class, $entity_key)
	{
		$template = str_replace([
			'$CLASS$',
			'$KEY$',
		], [
			$class,
			$entity_key,
		], $template);

		return $template;
	}

	/**
	 * @param string $template
	 * @param string $modelclass
	 * @param string $type
	 * @param string $method
	 * @param string $foreignkey
	 * @return string
	 */
	private function buildRelation(string $template, string $modelclass, string $type, string $method, string $foreignkey)
	{
		$template = str_replace([
			'$MODELCLASS$',
			'$TYPE$',
			'$METHOD$',
			'$FOREIGNKEY$',
		], [
			$modelclass,
			$type,
			$method,
			$foreignkey,
		], $template);

		return $template;
	}

	/**
	 * @param string $template
	 * @param string $colname
	 * @param string $attribute
	 * @return string
	 */
	private function buildDateMutator(string $template, string $colname, string $attribute)
	{
		$template = str_replace([
			'$COLNAME$',
			'$ATTRIBUTE$',
		], [
			$colname,
			$attribute,
		], $template);

		return $template;
	}

	/**
	 * @param string $template
	 * @param string $method
	 * @return string
	 */
	private function buildMethod(string $template, string $method)
	{
		$template = str_replace([
			'$METHOD$',
		], [
			$method,
		], $template);

		return $template;
	}

	/**
	 * @param string $templateFilePath
	 * @return false|string
	 */
	private function builderLoadTemplate(string $templateFilePath)
	{
		return file_get_contents($templateFilePath);
	}

	/**
	 * @param string $filepath
	 * @param string $template
	 * @return void
	 */
	private function builderWriteFile(string $filepath, string $template)
	{
		file_put_contents($filepath, $template);

	}

	/**
	 * @param object $entity
	 * @return array
	 */
	private function builderGetSortFields(object $entity)
	{

		// get all columns from table
		$prefix = $entity->egroup->key . '_prefix';
		$tablename = config('lara-common.database.entity.' . $prefix) . str_plural($entity->entity_key);
		$sortcolumns = Schema::getColumnListing($tablename);

		$sortfields = array();
		foreach ($sortcolumns as $sortcolumn) {
			$sortfields[$sortcolumn] = $sortcolumn;
		}

		return $sortfields;

	}

	/**
	 * @param array $activeRelations
	 * @return array
	 */
	private function builderGetRelatableEntities(array $activeRelations)
	{

		$entities = Entity::EntityGroupIsOneOf(['entity', 'form'])->whereNotIn('id', $activeRelations)->get();

		$relatableEntities = array();

		foreach ($entities as $entity) {

			$entityModelClass = $entity->getEntityModelClass();
			$entcount = $entityModelClass::count();

			if ($entcount == 0) {

				$relatableEntities[$entity->id] = $entity->entity_key;

			}
		}

		return $relatableEntities;

	}

	/**
	 * @param array $activeMethods
	 * @param bool $isformbuilder
	 * @return array
	 */
	private function builderGetAvailableMethods(array $activeMethods, bool $isformbuilder)
	{
		if ($isformbuilder) {
			$methods = config('lara-admin.entityViewFormMethods');
		} else {
			$methods = config('lara-admin.entityViewMethods');
		}

		$availableMethods = array();

		foreach ($methods as $method) {

			if (!in_array($method, $activeMethods)) {

				$availableMethods[$method] = $method;

			}
		}

		return $availableMethods;
	}

	/**
	 * @param bool $isformbuilder
	 * @return array
	 */
	private function builderGetFieldTypes(bool $isformbuilder)
	{
		if ($isformbuilder) {
			$ftypes = config('lara-admin.fieldFormTypes');
		} else {
			$ftypes = config('lara-admin.fieldTypes');
		}

		$fieldTypes = array();

		foreach ($ftypes as $fkey => $fval) {

			$fieldTypes[$fkey] = $fval['name'];

		}

		return $fieldTypes;
	}

	/**
	 * @param bool $isformbuilder
	 * @return array
	 */
	private function builderGetEntityViewTypes(bool $isformbuilder)
	{
		if ($isformbuilder) {
			$entityViewTypes = config('lara-admin.entityViewFormTypes');
		} else {
			$entityViewTypes = config('lara-admin.entityViewTypes');
		}

		return $entityViewTypes;

	}

	/**
	 * @param bool $isform
	 * @return array
	 */
	private function builderGetAdminMenuGroups($isform = false)
	{

		$groups = config('lara-admin.admin_menu_groups');

		$menuGroups = [
			'' => '[none]',
		];

		foreach ($groups as $groupkey => $groupval) {
			if ($isform) {
				if ($groupkey == 'forms') {
					$menuGroups[$groupkey] = $groupkey;
				}
			} else {
				if ($groupkey != 'forms') {
					$menuGroups[$groupkey] = $groupkey;
				}
			}
		}

		return $menuGroups;

	}

	/**
	 * @param Request $request
	 * @param object $entity
	 * @return bool
	 */
	private function builderDestroyEntity(Request $request, object $entity)
	{

		// check if other entities have a relation with this entity
		$rel1 = EntityRelation::where('related_entity_id', $entity->id)->get();
		$rel2 = EntityRelation::where('entity_id', $entity->id)->get();

		if ($rel1->count() || $rel2->count()) {

			flash('Error: Entity has relation(s). Entity has not been deleted.')->error();

			return false;

		} else {

			$module = $entity->egroup->path;

			if ($module == 'Eve') {

				$ds = DIRECTORY_SEPARATOR;

				$eve_base = config('lara.eve_path');
				$base_assets = public_path('assets');

				// Delete Admin controller
				$controller = ucfirst($entity->egroup->key) . $ds . $entity->getEntityController() . '.php';
				$controller_path = $eve_base . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Admin' . $ds . $controller;
				File::delete($controller_path);

				// Delete entity admin views
				$viewfolder = $entity->entity_key;
				$viewfolder_path = $eve_base . $ds . 'Resources' . $ds . 'Views' . $ds . $viewfolder;
				File::deleteDirectory($viewfolder_path);

				// Delete entity
				$ent = ucfirst($entity->entity_key) . 'Entity.php';
				$ent_path = $eve_base . $ds . 'Lara' . $ds . $ent;
				File::delete($ent_path);

				// Delete model
				$model = ucfirst($entity->entity_key) . '.php';
				$model_path = $eve_base . $ds . 'Models' . $ds . $model;
				File::delete($model_path);

				// Delete Front controller
				$controller = ucfirst($entity->egroup->key) . $ds . $entity->getEntityController() . '.php';
				$fcontroller_path = $eve_base . $ds . 'Http' . $ds . 'Controllers' . $ds . 'Front' . $ds . $controller;
				File::delete($fcontroller_path);

				// Delete Front assets
				$mediafolder = $entity->entity_key;
				$mediafolder_path = $base_assets . $ds . 'media' . $ds . $mediafolder;
				File::deleteDirectory($mediafolder_path);

				// Delete entity table
				$prefix = $entity->egroup->key . '_prefix';
				$tablename = config('lara-common.database.entity.' . $prefix) . str_plural($entity->entity_key);
				Schema::dropIfExists($tablename);

				// Delete entity abilities
				DB::table(config('lara-common.database.auth.abilities'))->where('entity_key', $entity->entity_key)->delete();

				// Delete entity translations
				DB::table(config('lara-common.database.sys.translations'))->where('cgroup', $entity->entity_key)->delete();

				// delete media images
				DB::table(config('lara-common.database.object.images'))
					->where('entity_id', $entity->id)
					->delete();

				// delete media files
				DB::table(config('lara-common.database.object.files'))
					->where('entity_id', $entity->id)
					->delete();

				// delete layout
				DB::table(config('lara-common.database.object.layout'))
					->where('entity_id', $entity->id)
					->delete();

				// delete entity tags
				DB::table(config('lara-common.database.object.taggables'))
					->where('entity_id', $entity->id)
					->delete();
				DB::table(config('lara-common.database.object.tags'))
					->where('entity_key', $entity->entity_key)
					->delete();

				// delete related
				$relatedIds = Related::where('entity_key', $entity->entity_key)
					->orWhere('related_entity_key', $entity->entity_key)
					->pluck('id')->toArray();
				Related::destroy($relatedIds);

				// Finally, delete entity itself from Entity table
				$entity->delete();

				// Clear cache
				Artisan::call('cache:clear');
				Artisan::call('config:clear');
				$request->session()->put('routecacheclear', true);

				flash('Entity deleted successfully')->success();

				return true;

			} else {

				flash('Error: You can not delete core entities')->error();

				return false;

			}

		}

	}

	/**
	 * @return void
	 */
	private function builderExportTablesToIseed()
	{

		$seedpath = base_path('database/seeders');
		File::delete(File::glob($seedpath . '/Lara*'));

		// get all tables
		$tables = DB::select('SHOW TABLES');
		$dbname = config('lara-common.database.db_database');
		$varname = 'Tables_in_' . $dbname;

		foreach ($tables as $table) {

			$tablename = $table->$varname;

			// skip migration table
			if ($tablename != config('database.migrations')) {

				Artisan::call('iseed', [
					'tables'  => $tablename,
					'--force' => true,
					'--clean' => true,
				]);
			}
		}

	}

	/**
	 * Get the real DB column type for custom field types
	 *
	 * Examples:
	 * - mcefull = text
	 * - email = string
	 * - selectone = text
	 *
	 * @param string $coltype
	 * @return string
	 */
	private function getRealBuilderColumnType(string $coltype)
	{

		// get supported field types from config
		$fieldTypes = json_decode(json_encode(config('lara-admin.fieldTypes')), false);

		// get real column type for this type
		$realcoltype = $fieldTypes->$coltype->type;

		return $realcoltype;

	}

	/**
	 * Lock files (chmod 0444) in directories that the Builder writes to.
	 * This way we prevent local-to-remote sync overwrite updated configs, language files, etc.
	 *
	 * @param string $dirpath
	 * @param string|null $pattern
	 * @return void
	 */
	private function lockFilesInDir(string $dirpath, $pattern = null)
	{

		$files = File::allFiles($dirpath);

		foreach ($files as $file) {

			if (!empty($pattern)) {

				$filename = $file->getFilename();

				if (substr($filename, 0, strlen($pattern)) == $pattern) {

					chmod($file->getPathname(), 0444);

				}

			} else {

				chmod($file->getPathname(), 0444);

			}

		}

	}

	/**
	 * Unlock files in a directory temporarily,
	 * so the Builder can write to it
	 *
	 * @param string $dirpath
	 * @param string|null $pattern
	 * @return void
	 */
	private function unlockFilesInDir(string $dirpath, $pattern = null)
	{

		$files = File::allFiles($dirpath);

		foreach ($files as $file) {

			if (!empty($pattern)) {

				$filename = $file->getFilename();

				if (substr($filename, 0, strlen($pattern)) == $pattern) {

					chmod($file->getPathname(), 0644);

				}

			} else {

				chmod($file->getPathname(), 0644);

			}

		}

		sleep(1);

	}


}
