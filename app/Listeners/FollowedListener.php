<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\FollowedNotification;
use Overtrue\LaravelFollow\Events\Followed;

class FollowedListener
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
     * @param  Followed  $event
     * @return int
     */
    public function handle(Followed $event)
    {
        $user = User::findOrFail($event->followable_id);

        $user->notify(new FollowedNotification($event->followable_id, $event->follower_id));
    }
}
