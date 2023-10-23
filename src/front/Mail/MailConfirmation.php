<?php

namespace Lara\Front\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Lara\Front\Http\Traits\FrontThemeTrait;

// use Theme;
use Qirolab\Theme\Theme;

class MailConfirmation extends Mailable {

	use Queueable, SerializesModels;

	use FrontThemeTrait;

	/**
	 * @var object
	 */
	public $maildata;

	/**
	 * MailConfirmation constructor.
	 *
	 * @param object $maildata
	 * @return void
	 */
	public function __construct(object $maildata) {

		// BS5
		$theme = $this->getFrontTheme();
		$parent = $this->getParentTheme();
		Theme::set($theme, $parent);

		$this->maildata = $maildata;

	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build() {

		return $this->from($this->maildata->from->email, $this->maildata->from->name)
			->subject($this->maildata->subject)
			->view($this->maildata->view);

	}
}
