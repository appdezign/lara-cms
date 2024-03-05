<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysLanguageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		$tablenames = config('lara-common.database');

		Schema::create($tablenames['sys']['languages'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('code')->nullable();
			$table->text('name')->nullable();

			$table->boolean('default')->default(0);

			$table->boolean('backend')->default(0);
			$table->boolean('backend_default')->default(0);

			$table->boolean('publish')->default(0);

			$table->timestamps();

			// sortable
			$table->integer('position')->unsigned()->nullable()->index();

		});

		Schema::create($tablenames['sys']['translations'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('language')->nullable();
			$table->string('module')->nullable();
			$table->string('cgroup')->nullable();
			$table->string('tag')->nullable();
			$table->string('key')->nullable();
			$table->text('value')->nullable();

			// timestamp
			$table->timestamps();
			$table->timestamp('deleted_at')->nullable();


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

		Schema::dropIfExists($tablenames['sys']['languages']);
		Schema::dropIfExists($tablenames['sys']['translations']);

		Schema::enableForeignKeyConstraints();

	}

}
