<?php

use Illuminate\Support\Facades\Broadcast;

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

// Define a channel for push notifications specific to clients, merchants, and admins
Broadcast::channel('notifs.client.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('notifs.merchant.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('notifs.admin.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
