<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		$tablenames = config('lara-common.database');

		Schema::create($tablenames['object']['images'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('title')->nullable();

			$table->text('filename')->nullable();
			$table->string('mimetype')->nullable();

			$table->boolean('featured')->default(0);
			$table->boolean('isicon')->default(0);
			$table->boolean('ishero')->default(0);
			$table->integer('herosize')->unsigned()->default(0);

			$table->boolean('hide_in_gallery')->default(0);

			$table->text('caption')->nullable();
			$table->string('image_title')->nullable();
			$table->string('image_alt')->nullable();

			$table->boolean('prevent_cropping')->default(0);

			$table->timestamps();

			$table->integer('position')->unsigned()->nullable();

		});

		Schema::create($tablenames['object']['videos'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('title')->nullable();

			$table->string('youtubecode')->nullable();
			$table->boolean('featured')->default(0);

			$table->timestamps();

		});

		Schema::create($tablenames['object']['files'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('title')->nullable();

			$table->text('filename')->nullable();
			$table->string('mimetype')->nullable();
			$table->date('docdate')->nullable();

			$table->timestamps();

		});

		Schema::create($tablenames['object']['videofiles'], function (Blueprint $table) {

			$table->bigIncrements('id');

			$table->string('entity_type')->nullable();
			$table->bigInteger('entity_id')->unsigned();

			$table->string('title')->nullable();

			$table->string('cfs_uid')->nullable();
			$table->boolean('cfs_ready')->default(0);
			$table->integer('cfs_thumb_offset')->unsigned()->default(0);

			$table->text('filename')->nullable();
			$table->string('mimetype')->nullable();

			$table->boolean('featured')->default(0);

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

		Schema::dropIfExists($tablenames['object']['images']);
		Schema::dropIfExists($tablenames['object']['videos']);
		Schema::dropIfExists($tablenames['object']['files']);

		Schema::enableForeignKeyConstraints();

	}
}
