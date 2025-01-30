<?php

namespace App\Services;

use App\Events\CollaboratorAdded;
use App\Events\CollaboratorRemoved;
use App\Events\CollaboratorRoleUpdated;
use App\Repositories\Interfaces\FileCollaboratorRepository;
use App\Repositories\Interfaces\FileRepository;
use App\Services\Interfaces\FileCollaborationServiceInterface;

class FileCollaborationService implements FileCollaborationServiceInterface

{
    public function __construct(
        private FileCollaboratorRepository $collaboratorRepository,
        private FileRepository $fileRepository
    ) {}

    public function addCollaborator(int $fileId, int $userId, string $role = 'viewer')
    {
        try {
            // Check if user is already a collaborator
            if ($this->collaboratorRepository->isCollaborator($fileId, $userId)) {
                throw new \InvalidArgumentException('User is already a collaborator');
            }

            $collaborator = $this->collaboratorRepository->addCollaborator($fileId, $userId, $role);

            event(new CollaboratorAdded($collaborator));

            return $collaborator;
        } catch (\Exception $e) {
            \Log::error('Error adding collaborator:', [
                'message' => $e->getMessage(),
                'file_id' => $fileId,
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    public function revokeAccess(int $fileId, int $userId)
    {
        try {
            $collaborator = $this->collaboratorRepository->revokeAccess($fileId, $userId);

            event(new CollaboratorRemoved($fileId, $userId));

            return $collaborator;
        } catch (\Exception $e) {
            \Log::error('Error revoking access:', [
                'message' => $e->getMessage(),
                'file_id' => $fileId,
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    public function updateRole(int $fileId, int $userId, string $role)
    {
        try {
            $collaborator = $this->collaboratorRepository->updateRole($fileId, $userId, $role);

            event(new CollaboratorRoleUpdated($fileId, $userId, $role));

            return $collaborator;
        } catch (\Exception $e) {
            \Log::error('Error updating role:', [
                'message' => $e->getMessage(),
                'file_id' => $fileId,
                'user_id' => $userId,
                'role' => $role
            ]);
            throw $e;
        }
    }

    public function getFileCollaborators(int $fileId)
    {
        return $this->collaboratorRepository->getFileCollaborators($fileId);
    }
}
