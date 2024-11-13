<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Silber\Bouncer\Database\Models;

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

		Schema::create($tablenames['auth']['users'], function (Blueprint $table) use ($tablenames) {
			$table->bigIncrements('id');
			$table->string('type')->nullable();
			$table->boolean('is_admin')->default(0);
			$table->string('name')->nullable();
			$table->string('firstname')->nullable();
			$table->string('middlename')->nullable();
			$table->string('lastname')->nullable();
			$table->string('username')->nullable()->unique();
			$table->string('email')->unique();
			$table->string('password')->nullable();
			$table->string('user_language')->nullable();

			$table->boolean('is_loggedin')->default(0);
			$table->timestamp('last_login')->nullable();

			$table->string('api_token')->nullable();
			$table->rememberToken();

			$table->timestamp('email_verified_at')->nullable();

			$table->timestamps();
			$table->timestamp('deleted_at')->nullable();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// foreign keys
			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['auth']['profiles'], function (Blueprint $table) use ($tablenames) {
			$table->bigIncrements('id');
			$table->bigInteger('user_id')->unsigned();
			$table->boolean('dark_mode')->default(0);

			$table->timestamps();

			// foreign keys
			$table->foreign('user_id')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['auth']['password_resets'], function (Blueprint $table) {
			$table->string('email')->index();
			$table->string('token')->nullable();
			$table->timestamp('created_at')->nullable();
		});

		Schema::create($tablenames['auth']['abilities'], function (Blueprint $table) use ($tablenames) {
			$table->bigIncrements('id');
			$table->string('name', 255)->nullable();
			$table->string('title')->nullable();
			$table->bigInteger('entity_id')->unsigned()->nullable();
			$table->string('entity_type', 255)->nullable();
			$table->string('entity_key', 255)->nullable();
			$table->boolean('only_owned')->default(false);
			$table->longText('options')->nullable();
			$table->integer('scope')->nullable()->index();
			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// foreign keys
			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

			$table->unique(
				['name', 'entity_id', 'entity_type', 'only_owned'],
				'abilities_unique_index'
			);
		});

		Schema::create($tablenames['auth']['roles'], function (Blueprint $table) use ($tablenames) {
			$table->bigIncrements('id');
			$table->string('name')->unique();
			$table->string('title')->nullable();
			$table->integer('level')->unsigned()->nullable();
			$table->integer('scope')->nullable()->index();
			$table->boolean('has_backend_access')->default(0);
			$table->timestamps();

			// record lock
			$table->timestamp('locked_at')->nullable();
			$table->bigInteger('locked_by')->nullable()->unsigned();

			// foreign keys
			$table->foreign('locked_by')
				->references('id')
				->on($tablenames['auth']['users'])
				->onDelete('cascade');

		});

		Schema::create($tablenames['auth']['has_abilities'], function (Blueprint $table) use ($tablenames) {
			$table->bigIncrements('id');
			$table->bigInteger('ability_id')->unsigned()->index();
			$table->morphs('entity');
			$table->boolean('forbidden')->default(false);
			$table->integer('scope')->nullable()->index();

			$table->foreign('ability_id')->references('id')->on($tablenames['auth']['abilities'])
				->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create($tablenames['auth']['has_roles'], function (Blueprint $table) use ($tablenames) {
			$table->bigInteger('role_id')->unsigned()->index();
			$table->morphs('entity');
			$table->bigInteger('restricted_to_id')->unsigned()->nullable();
			$table->string('restricted_to_type')->nullable();
			$table->integer('scope')->nullable()->index();

			$table->foreign('role_id')->references('id')->on($tablenames['auth']['roles'])
				->onUpdate('cascade')->onDelete('cascade');
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

		Schema::dropIfExists($tablenames['auth']['users']);
		Schema::dropIfExists($tablenames['auth']['password_resets']);

		Schema::dropIfExists($tablenames['auth']['abilities']);
		Schema::dropIfExists($tablenames['auth']['roles']);
		Schema::dropIfExists($tablenames['auth']['has_abilities']);
		Schema::dropIfExists($tablenames['auth']['has_roles']);

		Schema::enableForeignKeyConstraints();

	}

};
