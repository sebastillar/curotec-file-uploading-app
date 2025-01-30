<?php

namespace App\Services\Interfaces;

interface FileCollaborationServiceInterface
{
    public function addCollaborator(int $fileId, int $userId, string $role = 'viewer');
    public function revokeAccess(int $fileId, int $userId);
    public function updateRole(int $fileId, int $userId, string $role);
    public function getFileCollaborators(int $fileId);
}
