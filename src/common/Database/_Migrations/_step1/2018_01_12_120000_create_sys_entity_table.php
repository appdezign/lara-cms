<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{

		$tablenames = config('lara-common.database');

		Schema::create($tablenames['ent']['entitygroups'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->string('title')->nullable();
			$table->string('key')->nullable();
			$table->string('path')->nullable();

			$table->boolean('group_has_columns')->default(0);
			$table->boolean('group_has_objectrelations')->default(0);
			$table->boolean('group_has_filters')->default(0);
			$table->boolean('group_has_panels')->default(0);
			$table->boolean('group_has_media')->default(0);
			$table->boolean('group_has_customcolumns')->default(0);
			$table->boolean('group_has_relations')->default(0);
			$table->boolean('group_has_views')->default(0);
			$table->boolean('group_has_widgets')->default(0);
			$table->boolean('group_has_sortable')->default(0);
			$table->boolean('group_has_managedtable')->default(0);

			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			$table->integer('position')->unsigned();

			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['ent']['entities'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->bigInteger('group_id')->unsigned();

			$table->string('title')->nullable();

			$table->string('entity_model_class')->nullable();
			$table->string('entity_key')->nullable();
			$table->string('entity_controller')->nullable();

			$table->boolean('resource_routes')->default(0);
			$table->boolean('has_front_auth')->default(0);

			$table->string('menu_parent')->nullable();
			$table->integer('menu_position')->unsigned()->nullable();
			$table->string('menu_icon')->nullable();

			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// foreign keys
			$table->foreign('group_id')
				->references('id')
				->on($tablenames['ent']['entitygroups'])
				->onDelete('cascade');

		});


		Schema::create($tablenames['ent']['entityobjectrelations'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->bigInteger('entity_id')->unsigned();

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
			$table->boolean('has_videofiles')->default(0);
			$table->boolean('has_files')->default(0);

			$table->integer('max_images')->unsigned()->default(1);
			$table->integer('max_videos')->unsigned()->default(1);
			$table->integer('max_videofiles')->unsigned()->default(1);
			$table->integer('max_files')->unsigned()->default(1);

			$table->string('disk_images')->nullable();
			$table->string('disk_videos')->nullable();
			$table->string('disk_files')->nullable();

			// foreign keys
			$table->foreign('entity_id')
				->references('id')
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['ent']['entitycolumns'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->bigInteger('entity_id')->unsigned();

			$table->boolean('has_user')->default(0);
			$table->boolean('has_lang')->default(0);

			$table->boolean('has_slug')->default(0);
			$table->boolean('has_lead')->default(0);
			$table->boolean('has_body')->default(0);

			$table->boolean('has_status')->default(0);
			$table->boolean('has_hideinlist')->default(0);
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
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['ent']['entitypanels'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->bigInteger('entity_id')->unsigned();

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
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

		});


		Schema::create($tablenames['ent']['entitycustomcolumns'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->bigInteger('entity_id')->unsigned();

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
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['ent']['entityrelations'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');
			$table->bigInteger('entity_id')->unsigned();
			$table->string('type')->nullable();
			$table->bigInteger('related_entity_id')->unsigned();
			$table->string('foreign_key')->nullable();
			$table->boolean('is_filter')->default(0);

			// foreign keys
			$table->foreign('entity_id')
				->references('id')
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

			// TODO
			$table->foreign('related_entity_id')
				->references('id')
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['ent']['entityviews'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->bigInteger('entity_id')->unsigned();

			$table->string('title')->nullable();
			$table->string('method')->nullable();
			$table->string('filename')->nullable();
			$table->string('type')->nullable();
			$table->string('showtags')->nullable();

			$table->integer('paginate')->default(0);
			$table->boolean('infinite')->default(0);
			$table->boolean('prevnext')->default(0);

			$table->boolean('publish')->default(0);

			$table->timestamps();

			// foreign keys
			$table->foreign('entity_id')
				->references('id')
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{

		$tablenames = config('lara-common.database');

		Schema::disableForeignKeyConstraints();

		Schema::dropIfExists($tablenames['ent']['entitygroups']);
		Schema::dropIfExists($tablenames['ent']['entities']);
		Schema::dropIfExists($tablenames['ent']['entityobjectrelations']);
		Schema::dropIfExists($tablenames['ent']['entitycolumns']);
		Schema::dropIfExists($tablenames['ent']['entitypanels']);
		Schema::dropIfExists($tablenames['ent']['entitycustomcolumns']);
		Schema::dropIfExists($tablenames['ent']['entityrelations']);
		Schema::dropIfExists($tablenames['ent']['entityviews']);

		Schema::enableForeignKeyConstraints();

	}

};
