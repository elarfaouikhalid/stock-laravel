<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;

class LogUserActivity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\UserLoggedIn  $event
     * @return void
     */
    public function handle(UserLoggedIn $event)
    {
        Log::channel('activity_log')->info("User with ID {$event->user->id} logged in at {$event->date}");
    }
}
