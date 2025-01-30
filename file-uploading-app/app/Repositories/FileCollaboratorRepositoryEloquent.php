<?php

namespace App\Repositories;

use App\Models\FileCollaborator;
use App\Repositories\Interfaces\FileCollaboratorRepository;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class FileCollaboratorRepositoryEloquent extends BaseRepository implements FileCollaboratorRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return FileCollaborator::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function addCollaborator(int $fileId, int $userId, string $role = 'viewer')
    {
        return $this->create([
            'file_id' => $fileId,
            'user_id' => $userId,
            'role' => $role
        ]);
    }

    public function revokeAccess(int $fileId, int $userId)
    {
        return $this->model
            ->where('file_id', $fileId)
            ->where('user_id', $userId)
            ->update(['revoked_at' => now()]);
    }

    public function removeCollaborator(int $fileId, int $userId): bool
    {
        return $this->model
            ->where('file_id', $fileId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function updateRole(int $fileId, int $userId, string $role)
    {
        return $this->model
            ->where('file_id', $fileId)
            ->where('user_id', $userId)
            ->where('revoked_at', null)
            ->update(['role' => $role]);
    }

    public function getFileCollaborators(int $fileId)
    {
        return $this->model
            ->where('file_id', $fileId)
            ->where('revoked_at', null)
            ->get();
    }

    public function isCollaborator(int $fileId, int $userId): bool
    {
        return $this->model
            ->where('file_id', $fileId)
            ->where('user_id', $userId)
            ->where('revoked_at', null)
            ->exists();
    }

    public function getCollaboratorRole(int $fileId, int $userId): ?string
    {
        $collaborator = $this->model
            ->where('file_id', $fileId)
            ->where('user_id', $userId)
            ->where('revoked_at', null)
            ->first();

        return $collaborator ? $collaborator->role : null;
    }
}
