<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Lara\Admin\Http\Traits\LaraBuilder;
use Lara\Admin\Http\Traits\LaraAdminHelpers;

use Lara\Common\Models\Entity;

class CreateFormsTable extends Migration
{

	use LaraBuilder;
	use LaraAdminHelpers;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		$tablenames = config('lara-common.database');

		$entities = Entity::entityGroupIs('form')->get();

		foreach ($entities as $entity) {

			$prefix = $entity->egroup->key . '_prefix';

			$tablename = $tablenames['entity'][$prefix] . str_plural($entity->entity_key);

			Schema::create($tablename, function (Blueprint $table) use ($tablenames) {

				// ID's
				$table->bigIncrements('id');

				// content
				$table->string('title')->nullable();

				// timestamp
				$table->timestamps();
				$table->timestamp('deleted_at')->nullable();

				// record lock
				$table->timestamp('locked_at')->nullable();
				$table->bigInteger('locked_by')->nullable()->unsigned();

				$table->foreign('locked_by')
					->references('id')
					->on($tablenames['auth']['users'])
					->onDelete('cascade');

			});

		}

		foreach ($entities as $entity) {

			$prefix = $entity->egroup->key . '_prefix';
			$tablename = $tablenames['entity'][$prefix] . str_plural($entity->entity_key);

			// check optional fields
			$this->builderCheckEntityTable($entity, $tablename);

			// check Custom Fields
			$this->builderCheckFieldColumns($entity, $tablename);

			// create related columns (foreign key)
			$this->builderCheckRelatedColumns($entity, $tablename);

		}

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

		$entities = Entity::entityGroupIs('form')->get();

		foreach ($entities as $entity) {

			$prefix = $entity->egroup->key . '_prefix';

			$tablename = $tablenames['entity'][$prefix] . str_plural($entity->entity_key);

			Schema::dropIfExists($tablename);

		}

		Schema::enableForeignKeyConstraints();

	}
}
