<?php

namespace WPCOM_VIP;

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
use WPCOM_VIP\Illuminate\Support\Facades\Broadcast;
\WPCOM_VIP\Illuminate\Support\Facades\Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
