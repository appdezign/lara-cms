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

		Schema::create($tablenames['object']['sync'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('remote_url')->nullable();
			$table->string('remote_suffix')->nullable();
			$table->string('ent_key')->nullable();
			$table->string('slug')->nullable();

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

		Schema::dropIfExists($tablenames['object']['sync']);

		Schema::enableForeignKeyConstraints();

	}

};
