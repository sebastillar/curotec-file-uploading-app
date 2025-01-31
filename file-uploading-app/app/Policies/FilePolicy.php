<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Determine if user can view the file
     */
    public function view(User $user, File $file): bool
    {
        // File author can always view
        if ($file->author_id === $user->id) {
            return true;
        }

        // Check if user is an active collaborator with any role
        return $file->collaborators()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Determine if user can edit the file (upload new versions)
     */
    public function edit(User $user, File $file): bool
    {
        // File author can always edit
        if ($file->author_id === $user->id) {
            return true;
        }

        // Check if user is an active collaborator with editor role
        return $file->collaborators()
            ->where('user_id', $user->id)
            ->where('role', 'editor')
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Determine if user can comment on the file
     */
    public function comment(User $user, File $file): bool
    {
        // File author can always comment
        if ($file->author_id === $user->id) {
            return true;
        }

        // Check if user is an active collaborator with commenter or editor role
        return $file->collaborators()
            ->where('user_id', $user->id)
            ->whereIn('role', ['commenter', 'editor'])
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Determine if user can manage collaborators
     */
    public function manageCollaborators(User $user, File $file): bool
    {
        // Only file author can manage collaborators
        return $file->author_id === $user->id;
    }

    /**
     * Determine if user can view file list
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view file list
        return true;
    }

    /**
     * Determine if user can create files
     */
    public function create(User $user): bool
    {
        // All authenticated users can create files
        return true;
    }

    /**
     * Determine if user can delete the file
     */
    public function delete(User $user, File $file): bool
    {
        // Only file author can delete
        return $file->author_id === $user->id;
    }

        /**
     * Determine if user can download the file
     */
    public function download(User $user, File $file): bool
    {
        // File author can always download
        if ($file->author_id === $user->id) {
            return true;
        }

        // Check if user is an active collaborator (any role can download)
        return $file->collaborators()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->exists();
    }
}
