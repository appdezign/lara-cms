<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		$tablenames = config('lara-common.database');

		Schema::create($tablenames['sys']['dashboard'], function (Blueprint $table) {

			// ID's
			$table->bigIncrements('id');

			$table->string('title')->nullable();
			$table->string('cgroup')->nullable();
			$table->string('key')->nullable();
			$table->text('value')->nullable();

			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// sortable
			$table->integer('position')->unsigned()->nullable()->index();

		});

		Schema::create($tablenames['sys']['jobs'], function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('queue')->index();
			$table->longText('payload');
			$table->unsignedTinyInteger('attempts');
			$table->unsignedInteger('reserved_at')->nullable();
			$table->unsignedInteger('available_at');
			$table->unsignedInteger('created_at');
		});

		Schema::create($tablenames['sys']['settings'], function (Blueprint $table) {

			// ID's
			$table->bigIncrements('id');

			$table->string('title')->nullable();
			$table->string('cgroup')->nullable();
			$table->string('key')->nullable();
			$table->text('value')->nullable();
			$table->boolean('locked_by_admin')->default(0);

			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// sortable
			$table->integer('position')->unsigned()->nullable()->index();

		});

		Schema::create($tablenames['sys']['uploads'], function (Blueprint $table) use ($tablenames) {

			// ID's
			$table->bigIncrements('id');

			$table->bigInteger('user_id')->unsigned();

			$table->string('entity_type')->nullable();
			$table->bigInteger('object_id')->unsigned();
			$table->string('token')->nullable();
			$table->string('dz_session_id')->nullable();

			$table->text('filename')->nullable();
			$table->string('filetype')->nullable();
			$table->string('mimetype')->nullable();

			// timestamp
			$table->timestamps();

			// foreign keys
			$table->foreign('user_id')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['sys']['blacklist'], function (Blueprint $table) use ($tablenames) {

			$table->bigIncrements('id');
			$table->string('ipaddress')->nullable();
			$table->timestamps();

		});

		Schema::create($tablenames['sys']['templatefiles'], function (Blueprint $table) {

			$table->bigIncrements('id');
			$table->string('template_file')->nullable();
			$table->string('type')->nullable();
			$table->timestamps();

		});

		Schema::create($tablenames['sys']['headertags'], function (Blueprint $table) use ($tablenames) {

			$table->bigIncrements('id');

			$table->string('title')->nullable();
			$table->string('cgroup')->nullable();

			$table->bigInteger('templatefile_id')->unsigned();
			$table->foreign('templatefile_id')
				->references('id')
				->on($tablenames['sys']['templatefiles'])
				->onDelete('cascade');

			$table->string('title_tag')->nullable();
			$table->string('subtitle_tag')->nullable();
			$table->string('list_tag')->nullable();

			$table->timestamps();

			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

		$tablenames = config('lara-common.database');

		Schema::disableForeignKeyConstraints();

		Schema::dropIfExists($tablenames['sys']['dashboard']);
		Schema::dropIfExists($tablenames['sys']['jobs']);
		Schema::dropIfExists($tablenames['sys']['settings']);
		Schema::dropIfExists($tablenames['sys']['uploads']);
		Schema::dropIfExists($tablenames['sys']['blacklist']);

		Schema::enableForeignKeyConstraints();

	}

}
