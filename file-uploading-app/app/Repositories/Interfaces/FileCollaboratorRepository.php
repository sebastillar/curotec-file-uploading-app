<?php

namespace App\Repositories\Interfaces;

use Prettus\Repository\Contracts\RepositoryInterface;

interface FileCollaboratorRepository extends RepositoryInterface
{
    /**
     * Add a collaborator to a file
     *
     * @param int $fileId
     * @param int $userId
     * @param string $role
     * @return mixed
     */
    public function addCollaborator(int $fileId, int $userId, string $role = 'viewer');

    /**
     * Revoke access for a collaborator
     *
     * @param int $fileId
     * @param int $userId
     * @return mixed
     */
    public function revokeAccess(int $fileId, int $userId);

    /**
     * Update collaborator's role
     *
     * @param int $fileId
     * @param int $userId
     * @param string $role
     * @return mixed
     */
    public function updateRole(int $fileId, int $userId, string $role);

    /**
     * Get all collaborators for a file
     *
     * @param int $fileId
     * @return mixed
     */
    public function getFileCollaborators(int $fileId);

    /**
     * Check if a user is a collaborator of a file
     *
     * @param int $fileId
     * @param int $userId
     * @return bool
     */
    public function isCollaborator(int $fileId, int $userId): bool;
}
