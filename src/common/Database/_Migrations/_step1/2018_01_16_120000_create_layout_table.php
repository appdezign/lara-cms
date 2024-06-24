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

		Schema::create($tablenames['object']['layout'], function (Blueprint $table) {

			// ID's
			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('layout_key')->nullable();
			$table->string('layout_value')->nullable();

			// timestamp
			$table->timestamps();

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

		Schema::dropIfExists($tablenames['object']['layout']);

		Schema::enableForeignKeyConstraints();

	}

};
