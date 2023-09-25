<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeoTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		$tablenames = config('lara-common.database');

		Schema::create($tablenames['object']['seo'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('seo_focus')->nullable();
			$table->string('seo_title')->nullable();
			$table->string('seo_description')->nullable();
			$table->string('seo_keywords')->nullable();

			$table->timestamps();

		});

		Schema::create($tablenames['object']['opengraph'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('og_title')->nullable();
			$table->text('og_description')->nullable();
			$table->text('og_image')->nullable();

			$table->timestamps();

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

		Schema::dropIfExists($tablenames['object']['seo']);
		Schema::dropIfExists($tablenames['object']['opengraph']);

		Schema::enableForeignKeyConstraints();

	}
}
