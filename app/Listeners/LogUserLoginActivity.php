<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserLoginActivity
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // return response()->json([
        //     "data" => $event
        // ]);
        $user = $event->user;
        $date = $event->date;
        // dd($user->id);
        // dd(Log::channel('activity_log')->info("User with ID {$user->id} logged in at {$date}"));
        Log::channel('activity_log')->info("{$user->name} logged in at {$date}");
    }
}
