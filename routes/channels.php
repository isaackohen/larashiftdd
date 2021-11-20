<?php

use Illuminate\Support\Facades\Broadcast;
use App\Broadcasting\Everyone;
use App\Broadcasting\Custom;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Common channel for live bets/chat/etc. Doesn't require authorization.
 */
 
Broadcast::channel('Everyone', Everyone::class);
Broadcast::channel('App.User.{id}', Custom::class);