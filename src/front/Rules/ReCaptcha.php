<?php

namespace Lara\Front\Rules;

use Illuminate\Contracts\Validation\Rule;

use GuzzleHttp\Client;

class ReCaptcha implements Rule
{
	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		$client = new Client();

		$response = $client->post(
			'https://www.google.com/recaptcha/api/siteverify',
			[
				'form_params' =>
					[
						'secret'   => config('lara.google_recaptcha_secret_key'),
						'response' => $value,
					],
			]
		);

		$body = json_decode((string)$response->getBody());

		return $body->success;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return 'Je moet nog aanvinken dat je geen robot bent';
	}
}