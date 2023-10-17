<?php

namespace Lara\Admin\Http\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait AdminDbTrait
{

	/**
	 * Get the real DB column type for custom field types
	 *
	 * Examples:
	 * - mcefull = text
	 * - email = string
	 * - selectone = text
	 *
	 * @param string $coltype
	 * @return string
	 */
	private function getRealColumnType(string $coltype)
	{

		// get supported field types from config
		$fieldTypes = json_decode(json_encode(config('lara-admin.fieldTypes')), false);

		// get real column type for this type
		$realcoltype = $fieldTypes->$coltype->type;

		return $realcoltype;

	}

	/**
	 * @param string $dbname
	 * @param string|null $connection
	 * @param bool $includeManagedTables
	 * @return mixed
	 */
	private function getDatabaseStructure(string $dbname, $connection = null, $includeManagedTables = false)
	{

		$unmanagedTables = ['lara_auth', 'lara_ent', 'lara_menu', 'lara_object', 'lara_sys'];

		if ($connection) {
			$tables = DB::connection($connection)->select('SHOW TABLES');
		} else {
			$tables = DB::select('SHOW TABLES');
		}
		$varname = 'Tables_in_' . $dbname;

		$objects = array();

		foreach ($tables as $table) {

			$tablename = $table->$varname;

			foreach ($unmanagedTables as $unmanagedTable) {

				if (strpos($tablename, $unmanagedTable) !== false || $includeManagedTables) {

					$objects[$tablename] = [
						'tablename' => $tablename,
					];

					if ($connection) {
						$columns = Schema::connection($connection)->getColumnListing($tablename);
					} else {
						$columns = Schema::getColumnListing($tablename);
					}

					foreach ($columns as $column) {

						if ($connection) {
							$coltype = Schema::connection($connection)->getColumnType($tablename, $column);
						} else {
							$coltype = Schema::getColumnType($tablename, $column);
						}

						$columnLength = $this->getMaxColumnLength($dbname, $tablename, $column, $connection);

						$objects[$tablename]['columns'][$column] = [
							'columnname'   => $column,
							'columntype'   => $coltype,
							'columnlength' => $columnLength,
						];
					}

					break;
				}

			}

		}

		// convert to object
		$objects = json_decode(json_encode($objects), false);

		return $objects;
	}

	/**
	 * @param string $database
	 * @param string $table
	 * @param string $column
	 * @param string|null $connection
	 * @return int|null
	 */
	private function getMaxColumnLength(string $database, string $table, string $column, $connection = null)
	{

		$typeQuery = "SELECT DATA_TYPE
				FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = '$database'
				AND TABLE_NAME = '$table'
				AND COLUMN_NAME = '$column'";

		if ($connection) {
			$types = DB::connection($connection)->select($typeQuery);
		} else {
			$types = DB::select($typeQuery);
		}

		if (!empty($types)) {

			$type = $types[0]->DATA_TYPE;

			if ($type == 'int' || $type == 'tinyint' || $type == 'bigint') {
				$lengthfield = 'NUMERIC_PRECISION';
			} elseif ($type == 'varchar') {
				$lengthfield = 'CHARACTER_MAXIMUM_LENGTH';
			} else {
				return null;
			}

			$lengthQuery = "SELECT $lengthfield
				FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = '$database'
				AND TABLE_NAME = '$table'
				AND COLUMN_NAME = '$column'";

			if ($connection) {
				$result = DB::connection($connection)->select($lengthQuery);
			} else {
				$result = DB::select($lengthQuery);
			}

			if (!empty($result)) {
				return $result[0]->$lengthfield;
			} else {
				return null;
			}

		} else {
			return null;
		}

	}

	/**
	 * check all tables that are NOT managed by the Builder.
	 *
	 * @param string $dbnamesrc
	 * @param string $dbnamedest
	 * @param string|null $connsrc
	 * @param string|null $conndest
	 * @return object
	 * @throws BindingResolutionException
	 */
	private function compareDatabaseStructure(string $dbnamesrc, string $dbnamedest, $connsrc = null, $conndest = null)
	{

		$objects = array();

		$sourceTables = $this->getDatabaseStructure($dbnamesrc, $connsrc, false);
		$destTables = $this->getDatabaseStructure($dbnamedest, $conndest, false);

		$errorcount = 0;

		foreach ($sourceTables as $srcTable) {

			$tablename = $srcTable->tablename;

			// add table to result object
			$objects[$tablename] = [
				'tablename' => $tablename,
			];

			// check if table exists in Dest
			if (property_exists($destTables, $tablename)) {

				$destTable = $destTables->$tablename;

				foreach ($srcTable->columns as $srcColumn) {

					// check if column exists

					$srcColumnName = $srcColumn->columnname;
					$srcColumnType = $srcColumn->columntype;
					$srcColumnLength = $srcColumn->columnlength;

					if (property_exists($destTable->columns, $srcColumnName)) {

						$destColumn = $destTable->columns->$srcColumnName;
						$destColumnType = $destColumn->columntype;
						$destColumnLength = $destColumn->columnlength;

						$typeError = $srcColumnType != $destColumnType;
						$lengthError = $srcColumnLength != $destColumnLength;

						$objects[$tablename]['columns'][] = [
							'columnname'    => $srcColumnName,
							'coltypesrc'    => $srcColumnType,
							'collengthsrc'  => $srcColumnLength,
							'coltypedest'   => $destColumnType,
							'collengthdest' => $destColumnLength,
							'columnerror'   => false,
							'typeerror'     => $typeError,
							'lengtherror'   => $lengthError,
						];

						if ($typeError) {
							$errorcount++;
						}

					} else {

						// column not found in DEST

						$objects[$tablename]['columns'][] = [
							'columnname'    => $srcColumnName,
							'coltypesrc'    => $srcColumnType,
							'collengthsrc'  => $srcColumnLength,
							'coltypedest'   => null,
							'collengthdest' => null,
							'columnerror'   => true,
							'typeerror'     => false,
						];

						$errorcount++;
					}

				}

				$objects[$tablename]['tableerror'] = false;

			} else {

				// table not found in DEST

				foreach ($srcTable->columns as $srcColumn) {

					$srcColumnName = $srcColumn->columnname;
					$srcColumnType = $srcColumn->columntype;
					$srcColumnLength = $srcColumn->columnlength;

					$objects[$tablename]['columns'][] = [
						'columnname'   => $srcColumnName,
						'coltypesrc'   => $srcColumnType,
						'collengthsrc' => $srcColumnLength,
					];
				}

				$objects[$tablename]['tableerror'] = true;

				$errorcount++;
			}
		}

		$app = app();
		$result = $app->make('stdClass');

		// convert to object
		$result->objects = json_decode(json_encode($objects), false);

		// errors
		$result->error = $app->make('stdClass');
		$result->error->error = $errorcount > 0;
		$result->error->errorcount = $errorcount;

		return $result;

	}



}

