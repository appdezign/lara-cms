<?php

namespace Lara\Admin\Http\ViewComposers;

use Illuminate\View\View;

use Lara\Common\Models\Language;

class ContentLanguageComposer {

	/**
	 * @var object
	 */
	protected $clanguages;

	public function __construct() {

		// get all active content language
		$this->clanguages = Language::isPublished()->orderBy('position')->get();

	}

	/**
	 * @param View $view
	 * @return void
	 */
	public function compose(View $view) {

		$data = array(
			'clanguages'  => $this->clanguages,
		);

		$view->with($data);

	}
}