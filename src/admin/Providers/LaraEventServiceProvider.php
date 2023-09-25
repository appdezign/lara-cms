<?php

namespace Lara\Admin\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use UniSharp\LaravelFilemanager\Events\ImageWasUploaded;
use Lara\Admin\Listeners\HasUploadedImageListener;

class LaraEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

    	// listener for resizing image after upload
        ImageWasUploaded::class => [
	        HasUploadedImageListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
