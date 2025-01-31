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

Broadcast::channel('files.{fileId}', function ($user, $fileId) {
    $file = \App\Models\File::find($fileId);

    if (!$file) {
        return false;
    }

    // Allow if user is author
    if ($file->author_id === $user->id) {
        return true;
    }

    // Allow if user is an active collaborator
    return $file->collaborators()
        ->where('user_id', $user->id)
        ->whereNull('revoked_at')
        ->exists();
});
