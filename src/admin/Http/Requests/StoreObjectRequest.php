<?php

namespace Lara\Admin\Http\Requests;


use Lara\Admin\Http\Traits\LaraAdminHelpers;

use Illuminate\Foundation\Http\FormRequest;

use Carbon\Carbon;

class StoreObjectRequest extends FormRequest
{

	use LaraAdminHelpers;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{

		$this->sanitize();

		return [
			'title' => 'required',
		];
	}

	/**
	 * @return void
	 */
	public function sanitize()
	{

		$input = $this->all();

		// default columns
		$defcols = config('lara-admin.defaultColumns');
		$defcols = json_decode(json_encode($defcols), false);

		foreach ($defcols as $defcol) {

			$name = $defcol->name;
			$realtype = $defcol->type;

			if ($defcol->validate) {

				$value = $input[$name];

				// fix empty values
				if ($realtype == 'string' || $realtype == 'text') {
					if (empty($value)) {
						$input[$name] = '';
					}
				}
				if ($realtype == 'integer' || $realtype == 'decimal') {
					if (empty($value)) {
						$input[$name] = 0;
					}
				}
				if ($realtype == 'boolean') {
					if (empty($value)) {
						$input[$name] = 0;
					}
				}
			}

		}

		// optional columns
		$optcols = config('lara-admin.optionalColumns');
		$optcols = json_decode(json_encode($optcols), false);

		foreach ($optcols as $optcol) {

			$name = $optcol->name;
			$realtype = $optcol->type;

			if ($optcol->validate) {

				if ($this->has($name)) {

					$value = $input[$name];

					// fix empty values
					if ($realtype == 'string' || $realtype == 'text') {
						if (empty($value)) {
							$input[$name] = '';
						}
					}
					if ($realtype == 'integer' || $realtype == 'decimal') {
						if (empty($value)) {
							$input[$name] = 0;
						}
					}
					if ($realtype == 'boolean') {
						if (empty($value)) {
							$input[$name] = 0;
						}
					}
					if ($optcol->name == 'publish_from') {
						if (empty($value)) {
							$input[$name] = Carbon::today();
						}
					}

				}

			}

		}

		// dynamic columns

		if (isset($input['_entity_key'])) {

			$entityKey = $input['_entity_key'];

			$laraClass = $this->getEntityVarByKey($entityKey);
			$entity = new $laraClass;

			foreach ($entity->getCustomColumns() as $field) {

				$name = $field->fieldname;
				$realtype = $this->getRealColumnType($field->fieldtype);
				$value = $input[$name];

				// fix empty values
				if ($realtype == 'string' || $realtype == 'text') {
					if (empty($value)) {
						$input[$name] = '';
					}
				}
				if ($realtype == 'integer' || $realtype == 'decimal') {
					if (empty($value)) {
						$input[$name] = 0;
					}
				}
				if ($realtype == 'boolean') {
					if (empty($value)) {
						$input[$name] = 0;
					}
				}

			}

		}

		$this->replace($input);

	}

}
