<?php

namespace Lara\Front\Http\Controllers\Api\Base;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;

use Lara\Front\Http\Traits\FrontEntityTrait;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use LaravelLocalization;

use ReflectionClass;
use ReflectionException;

class BaseApiController extends Controller
{

	use FrontEntityTrait;

	/**
	 * @var string
	 */
	protected $modelClass;

	/**
	 * @var string|null
	 */
	protected $routename;

	/**
	 * @var object
	 */
	protected $entity;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var array
	 */
	protected $columns;

	public function __construct()
	{

		// get model class from child controller
		$this->modelClass = $this->determineModelClass();

		$this->language = LaravelLocalization::getCurrentLocale();

		if (!App::runningInConsole()) {

			// get route name
			$this->routename = Route::current()->getName();

			// get entity
			$this->entity = $this->getFrontEntity($this->routename);

		}

		// default fields
		$this->columns = config('lara-front.lara_api_default_columns');

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request)
	{

		if ($request->has('limit')) {
			$limit = $request->limit;
		} else {
			$limit = config('lara-front.lara_api_object_limit');
		}

		// add standard fields to the default fields
		if ($this->entity->hasSlug()) {
			$this->columns[] = 'slug';
		}
		if ($this->entity->hasLead()) {
			$this->columns[] = 'lead';
		}
		if ($this->entity->hasBody()) {
			$this->columns[] = 'body';
		}

		// add custom fields to the default fields
		foreach ($this->entity->getCustomColumns() as $field) {
			$this->columns[] = $field->fieldname;
		}

		$collection = new $this->modelClass;

		$collection = $collection->langIs($this->language);
		$collection = $collection->isPublished();

		if ($this->entity->getEntityKey() == 'page') {
			$collection = $collection->where('cgroup', 'page');
		}

		$collection = $collection->orderBy($this->entity->getSortField(), $this->entity->getSortOrder());
		$collection = $collection->limit($limit);

		$objects = $collection->get($this->columns);

		foreach ($objects as $object) {
			// add featured image
			if ($object->media()->count()) {
				$object->image = _cimg($object->featured->filename, 1280, 960);
			} else {
				$object->image = null;
			}
			// add all images
			if ($object->media()->count()) {
				$gallery = array();
				foreach ($object->media as $img) {
					$gallery[] = _cimg($img->filename, 1280, 960);
				}
				$object->images = $gallery;
			} else {
				$object->images = null;
			}
			unset($object->media);
		}

		return response()->json($objects);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param Request $request
	 * @param string|int|null $id
	 * @return JsonResponse
	 */
	public function show(Request $request, $id = null)
	{

		$getFullImages = false;
		if ($request->has('image')) {
			$image = $request->get('image');
			if ($image == 'full') {
				$getFullImages = true;
			}
		}

		$getMedia = false;
		if ($request->has('media')) {
			$media = $request->get('media');
			if ($media == 'true') {
				$getMedia = true;
			}
		}

		$getFiles = false;
		if ($request->has('files')) {
			$files = $request->get('files');
			if ($files == 'true') {
				$getFiles = true;
			}
		}

		// add standard fields to the default fields
		if ($this->entity->hasSlug()) {
			$this->columns[] = 'slug';
		}
		if ($this->entity->hasLead()) {
			$this->columns[] = 'lead';
		}
		if ($this->entity->hasBody()) {
			$this->columns[] = 'body';
		}

		// add custom fields to the default fields
		foreach ($this->entity->getCustomColumns() as $field) {
			$this->columns[] = $field->fieldname;
		}

		if (is_numeric($id)) {
			$object = $this->modelClass::select($this->columns)->find($id);
		} else {
			$object = $this->modelClass::select($this->columns)->where('slug', $id)->first();
		}

		if ($object) {

			// add seo
			if ($this->entity->hasSeo()) {
				$seo = $object->seo;
				if ($seo) {
					$object->seo_title = $seo->seo_title;
					$object->seo_description = $seo->seo_description;
					$object->seo_keywords = $seo->seo_keywords;
				} else {
					$object->seo_title = null;
					$object->seo_description = null;
					$object->seo_keywords = null;
				}
				unset($object->seo);
			}

			// add featured image
			if ($object->media()->count()) {
				if ($getFullImages) {
					$object->image = $this->entity->getUrlForImages() . '/' . $object->featured->filename;
				} else {
					$object->image = _cimg($object->featured->filename, 1280, 960);
				}
			} else {
				$object->image = null;
			}

			// add all images
			if ($object->media()->count()) {
				$gallery = array();
				foreach ($object->media as $img) {
					if ($getFullImages) {
						$imagePath = $this->entity->getUrlForImages() . '/' . $img->filename;
					} else {
						$imagePath = _cimg($img->filename, 1280, 960);
					}
					$gallery[] = $imagePath;
					$img->image_url = $imagePath;
				}
				$object->images = $gallery;
			} else {
				$object->images = null;
			}
			if (!$getMedia) {
				unset($object->media);
			}

			// files
			if ($object->files()->count()) {
				$files = array();
				foreach ($object->files as $file) {
					$filePath = url('assets/media/' . $this->entity->getEntityKey() . '/' . $file->filename);
					$files[] = $filePath;
					$file->file_url = $filePath;
				}
				$object->attachments = $files;
			} else {
				$object->attachments = null;
			}
			if (!$getFiles) {
				unset($object->files);
			}

		}

		return response()->json($object);

	}

	/**
	 * Determine the model class name of the child controller
	 *
	 * @return string
	 * @throws ReflectionException
	 */
	protected function determineModelClass(): string
	{
		return (new ReflectionClass($this))
			->getMethod('make')
			->getReturnType()
			->getName();
	}

}
