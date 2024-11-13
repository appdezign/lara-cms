<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Kalnoy\Nestedset\NestedSet;

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

		Schema::create($tablenames['menu']['menus'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			// content
			$table->string('title')->nullable();
			$table->string('slug')->nullable();
			$table->boolean('slug_lock')->default(0);

			// timestamp
			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// sortable
			// $table->integer('position')->unsigned()->nullable()->index();

			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['menu']['menuitems'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->string('language')->nullable();
			$table->bigInteger('menu_id')->unsigned();

			// content
			$table->string('title')->nullable();
			$table->string('slug')->nullable();
			$table->boolean('slug_lock')->default(0);

			$table->string('type')->nullable();
			$table->bigInteger('tag_id')->unsigned()->nullable();
			$table->string('route')->nullable();
			$table->string('routename')->nullable();
			$table->boolean('route_has_auth')->default(0);

			$table->bigInteger('entity_id')->unsigned()->nullable();
			$table->bigInteger('entity_view_id')->unsigned()->nullable();
			$table->bigInteger('object_id')->unsigned()->nullable();

			$table->string('url')->nullable();

			$table->boolean('locked_by_admin')->default(0);

			// timestamp
			$table->timestamps();

			// publish
			$table->boolean('publish')->default(0);

			// nested sets
			$table->bigInteger('parent_id')->unsigned()->nullable()->index();
			$table->bigInteger('lft')->unsigned()->nullable()->index();
			$table->bigInteger('rgt')->unsigned()->nullable()->index();
			$table->bigInteger('depth')->unsigned()->nullable();

			// foreign keys
			$table->foreign('menu_id')
				->references('id')
				->on($tablenames['menu']['menus'])
				->onDelete('cascade');

			$table->foreign('entity_id')
				->references('id')
				->on($tablenames['ent']['entities'])
				->onDelete('cascade');

			$table->foreign('entity_view_id')
				->references('id')
				->on($tablenames['ent']['entityviews'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['menu']['redirects'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->string('language')->nullable();

			$table->string('title')->nullable();
			$table->string('redirectfrom')->nullable();
			$table->string('redirectto')->nullable();
			$table->string('redirecttype')->nullable();

			$table->boolean('auto_generated')->default(0);
			$table->boolean('locked_by_admin')->default(0);
			$table->boolean('has_error')->default(0);

			// timestamp
			$table->timestamps();

			// publish
			$table->boolean('publish')->default(0);

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// foreign keys
			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
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

		Schema::dropIfExists($tablenames['menu']['menus']);
		Schema::dropIfExists($tablenames['menu']['menuitems']);

		Schema::enableForeignKeyConstraints();

	}

};
