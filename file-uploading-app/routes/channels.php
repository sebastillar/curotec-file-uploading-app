<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('files', function ($user) {
    return true;
});

Broadcast::channel('file-comments', function ($user) {
    return true;
});
