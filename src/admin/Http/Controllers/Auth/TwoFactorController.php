<?php

namespace Lara\Admin\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use Lara\Admin\Http\Traits\AdminTrait;
use Lara\Admin\Http\Traits\AdminAuthTrait;
use Lara\Admin\Http\Traits\AdminEntityTrait;
use Lara\Admin\Http\Traits\AdminListTrait;
use Lara\Admin\Http\Traits\AdminObjectTrait;
use Lara\Admin\Http\Traits\AdminViewTrait;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use Google2FA;
use PragmaRX\Recovery\Recovery;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

use Lara\Common\Models\Setting;
use Lara\Common\Models\User;

use Jenssegers\Agent\Agent;

use LaravelLocalization;

use Bouncer;

class TwoFactorController extends Controller
{

	use AdminTrait;
	use AdminAuthTrait;
	use AdminEntityTrait;
	use AdminListTrait;
	use AdminObjectTrait;
	use AdminViewTrait;

	/**
	 * @var string
	 */
	protected $modelClass = User::class;

	/**
	 * @var string|null
	 */
	protected $routename = '';

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

			// get entity
			$this->entity = $this->getLaraEntity($this->routename, $this->modelClass);

			// get agent
			$agent = new Agent();
			$this->ismobile = $agent->isMobile();

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
	 * Show the form for editing the specified resource.
	 *
	 * @return Application|Factory|View
	 */
	public function edit()
	{

		$id = Auth::user()->id;

		// get record
		$this->data->object = User::findOrFail($id);

		// lock record
		$this->data->object->lockRecord();

		if ($this->data->object->hasTwoFactor()) {
			$this->data->qrCodeImage = $this->getQrCode($this->data->object->email, $this->data->object->two_factor_secret);
			$this->data->object->recoverCodes = json_decode($this->data->object->two_factor_recovery_codes);
		} else {
			$this->data->newSecretKey = $this->getNewSecretKey();
			$this->data->qrCodeImage = $this->getQrCode($this->data->object->email, $this->data->newSecretKey);
		}

		// get view file and partials
		$this->data->partials = $this->getPartials($this->entity);
		$viewfile = $this->getViewFile($this->entity);

		// pass all variables to the view
		return view($viewfile, [
			'data' => $this->data,
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function update(Request $request)
	{

		$id = Auth::user()->id;

		$object = $this->modelClass::findOrFail($id);

		if ($request->has('_activate_2fa')) {
			$object->two_factor_secret = $request->input('_new_secret_key');
			$object->two_factor_recovery_codes = $this->generateRecoveryCodes();
			$message = _lanq('lara-admin::2fa.message.is_enabled');
		} elseif ($request->has('_deactivate_2fa')) {
			$object->two_factor_secret = null;
			$object->two_factor_recovery_codes = null;
			$message = _lanq('lara-admin::2fa.message.is_disabled');
		} else {
			//
		}

		// save object
		$object->update($request->all());

		flash($message)->success();

		return redirect()->route('admin.user.2fa');

	}

	/**
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function unlock(int $id)
	{

		// get record
		$object = $this->modelClass::findOrFail($id);

		// unlock record
		$object->unlockRecord();

		// get last page (pagination) for redirect
		$lastpage = $this->getLastPage($this->entity->getEntityKey());

		// redirect
		if ($lastpage) {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index', ['page' => $lastpage]);
		} else {
			return redirect()->route($this->entity->getPrefix() . '.' . $this->entity->getEntityRouteKey() . '.index');
		}

	}

	private function getQrCode($userEmail, $userSecretKey)
	{

		$companyName = $this->GetCompanyAppName();

		$g2faUrl = Google2FA::getQRCodeUrl(
			$companyName,
			$userEmail,
			$userSecretKey,
		);

		$writer = new Writer(
			new ImageRenderer(
				new RendererStyle(400),
				new ImagickImageBackEnd()
			)
		);

		return base64_encode($writer->writeString($g2faUrl));

	}

	private function GetCompanyAppName() {

		$key = 'company_2fa_app_name';
		$title = 'Company 2FA App Name';
		$cgroup = 'company';
		$defaulValue = 'Lara CMS 8';

		$setting = Setting::where('cgroup', $cgroup)->where('key', $key)->first();
		if($setting) {
			return $setting->value;
		} else {
			// create setting
			$newSetting = Setting::create([
				'title' => $title,
				'cgroup' => $cgroup,
				'key' => $key,
				'value' => $defaulValue,
			]);

			return $newSetting->value;

		}

	}

	private function getNewSecretKey()
	{

		$sessionKey = 'lara_new_2fa_secret_key';

		if (session()->has($sessionKey)) {
			// Use secret key in current session
			return session()->get($sessionKey);
		} else {
			// generate new Secret Key
			$newSecretKey = Google2FA::generateSecretKey();

			// store the new Secret Key in the session
			session()->put($sessionKey, $newSecretKey);

			return $newSecretKey;
		}

	}

	private function generateRecoveryCodes()
	{
		$recovery = new Recovery();
		$recoveryCodes = $recovery
			->setCount(10)     // Generate 8 codes
			->setBlocks(4)    // Every code must have 7 blocks
			->setChars(16)    // Each block must have 16 chars
			->toArray();

		return json_encode($recoveryCodes, JSON_FORCE_OBJECT);

	}

}
