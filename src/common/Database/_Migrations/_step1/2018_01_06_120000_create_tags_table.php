<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		$tablenames = config('lara-common.database');

		Schema::create($tablenames['object']['related'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');
			$table->string('entity_key')->nullable();
			$table->bigInteger('object_id')->unsigned();
			$table->string('related_entity_key')->nullable();
			$table->string('related_model_class')->nullable();
			$table->bigInteger('related_object_id')->unsigned();

		});

		Schema::create($tablenames['object']['taxonomy'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->string('title')->nullable();
			$table->string('slug')->nullable();
			$table->boolean('slug_lock')->default(0);

			$table->boolean('has_hierarchy')->default(0);
			$table->boolean('is_default')->default(0);

			// timestamp
			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// sortable
			$table->integer('position')->unsigned()->nullable()->index();

			// foreign keys
			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['object']['tags'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');
			$table->string('language')->nullable();
			$table->bigInteger('language_parent')->unsigned()->nullable();

			$table->bigInteger('taxonomy_id')->unsigned();

			$table->string('entity_key')->nullable();

			// content
			$table->string('title')->nullable();
			$table->string('slug')->nullable();
			$table->boolean('slug_lock')->default(0);
			$table->text('lead')->nullable();
			$table->text('body')->nullable();
			$table->string('route')->nullable();

			$table->boolean('locked_by_admin')->default(0);

			// timestamp
			$table->timestamps();

			// publish
			$table->boolean('publish')->default(0);
			$table->timestamp('publish_from')->nullable();
			$table->timestamp('publish_to')->nullable();

			// sort
			$table->bigInteger('parent_id')->unsigned()->nullable()->index();
			$table->bigInteger('lft')->unsigned()->nullable()->index();
			$table->bigInteger('rgt')->unsigned()->nullable()->index();
			$table->bigInteger('depth')->unsigned()->nullable()->index();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// foreign keys
			$table->foreign('taxonomy_id')
				->references('id')
				->on($tablenames['object']['taxonomy'])
				->onDelete('cascade');

			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['object']['taggables'], function (Blueprint $table) use ($tablenames) {

			$table->bigIncrements('id');
			$table->bigInteger('tag_id')->unsigned()->index();
			$table->morphs('entity');

		});

		Schema::create($tablenames['object']['pageables'], function (Blueprint $table) use ($tablenames) {

			$table->bigIncrements('id');
			$table->bigInteger('page_id')->unsigned()->index();
			$table->morphs('entity');

		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {

		$tablenames = config('lara-common.database');

		Schema::disableForeignKeyConstraints();

		Schema::dropIfExists($tablenames['object']['related']);
		Schema::dropIfExists($tablenames['object']['tags']);
		Schema::dropIfExists($tablenames['object']['taggables']);
		Schema::dropIfExists($tablenames['object']['taxonomy']);
		Schema::dropIfExists($tablenames['object']['pageables']);

		Schema::enableForeignKeyConstraints();

	}

}
