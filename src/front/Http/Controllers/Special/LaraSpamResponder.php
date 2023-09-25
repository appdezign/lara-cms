<?php

namespace Lara\Front\Http\Controllers\Special;

use Spatie\Honeypot\SpamResponder\SpamResponder;

use Closure;
use Illuminate\Http\Request;

class LaraSpamResponder implements SpamResponder
{
    public function respond(Request $request, Closure $next)
    {
        return response('spam detected');
    }
}
