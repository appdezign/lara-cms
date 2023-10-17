<?php

namespace Lara\Admin\Http\Controllers\Media;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

use Lara\Common\Models\MediaImage;

use App\Http\Controllers\Controller;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

use Config;

class ImageController extends Controller
{

	use AdminTrait;
	use AdminEntityTrait;
	use AdminViewTrait;

	/**
	 * @var string
	 */
	protected $modelClass = MediaImage::class;

	/**
	 * @var string|null
	 */
	protected $routename;

	/**
	 * @var object
	 */
	protected $entity;

	/**
	 * @var object
	 */
	protected $data;

	/**
	 * @var bool
	 */
	protected $ismobile;

	/**
	 * @var bool
	 */
	protected $isbuilder = false;


	public function __construct()
	{

		// create an empty Laravel object to hold all the data
		$this->data = $this->makeNewObject();

		if (!App::runningInConsole()) {

			// get route name
			$this->routename = Route::current()->getName();

			// get basic entity
			$this->entity = $this->getLaraEntityByRoute($this->routename);

			// get agent
			$agent = new Agent();
			$this->ismobile = $agent->isMobile();

			// share data with all views, see: https://goo.gl/Aqxquw
			$this->middleware(function ($request, $next) {
				view()->share('isbuilder', $this->isbuilder);
				view()->share('entity', $this->entity);
				view()->share('clanguage', $this->getContentLanguage($request, $this->entity));
				view()->share('ismobile', $this->ismobile);

				return $next($request);
			});

		}

	}

	/**
	 * Reorder the specified resource
	 *
	 * @param Request $request
	 * @param string $type
	 * @param int $id
	 * @return Application|Factory|Response|View
	 */
	public function reorder(Request $request, string $type, int $id)
	{

		$laraKey = $this->getEntityVarByKey($type);
		$this->data->relatedEntity = new $laraKey;

		if ($this->data->relatedEntity) {

			// get related Object
			$modelClass = $this->data->relatedEntity->getEntityModelClass();
			$this->data->object = $modelClass::find($id);

			if($this->data->object) {

				// get images
				$this->data->objects = $this->data->object->media()->where('featured', 0)->where('ishero', 0)->get();

				// check if the position field is already set
				$featured = $this->data->object->featured;
				if($featured) {
					if(empty($featured->position)) {
						$base = $this->getImagePositionBase($this->data->relatedEntity, $this->data->object, $type = 'featured');
						$featured->position = $base + 1;
						$featured->save();
					}
				}

				$hero = $this->data->object->hero;
				if($hero) {
					if(empty($hero->position)) {
						$base = $this->getImagePositionBase($this->data->relatedEntity, $this->data->object, $type = 'hero');
						$hero->position = $base + 1;
						$hero->save();
					}
				}

				$itemsCount = $this->data->objects->count();
				$uniqueItemsCount = $this->data->objects->unique('position')->count();
				$i = 1;
				if($itemsCount != $uniqueItemsCount) {
					$base = $this->getImagePositionBase($this->data->relatedEntity, $this->data->object);
					foreach($this->data->objects as $image) {
						$image->position = $base + $i;
						$image->save();
						$i++;
					}
				}




			} else {
				return response()->view('lara-admin::errors.404', [], 405);
			}
		} else {
			return response()->view('lara-admin::errors.404', [], 405);
		}

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);

	}



}
