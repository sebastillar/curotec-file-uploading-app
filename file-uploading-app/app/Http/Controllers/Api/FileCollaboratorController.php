<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileCollaborator\StoreFileCollaboratorRequest;
use App\Http\Requests\FileCollaborator\UpdateFileCollaboratorRequest;
use App\Models\File;
use App\Models\User;
use App\Services\Interfaces\FileCollaborationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class FileCollaboratorController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private FileCollaborationServiceInterface $collaborationService
    ) {}

    /**
     * Get file collaborators
     */
    public function index(File $file): JsonResponse
    {
        $this->authorize('manageCollaborators', $file);

        $collaborators = $this->collaborationService->getFileCollaborators($file->id);

        return response()->json($collaborators);
    }

    /**
     * Add a new collaborator to the file
     */
    public function store(StoreFileCollaboratorRequest $request, File $file): JsonResponse
    {
        $this->authorize('manageCollaborators', $file);

        $collaborator = $this->collaborationService->addCollaborator(
            $file->id,
            $request->user_id,
            $request->role
        );

        return response()->json($collaborator, Response::HTTP_CREATED);
    }

    /**
     * Update collaborator role
     */
    public function update(
        UpdateFileCollaboratorRequest $request,
        File $file,
        User $user
    ): JsonResponse
    {
        $this->authorize('manageCollaborators', $file);

        $collaborator = $this->collaborationService->updateRole(
            $file->id,
            $user->id,
            $request->role
        );

        return response()->json($collaborator);
    }

    /**
     * Remove collaborator
     */
    public function destroy(File $file, User $user): JsonResponse
    {
        $this->authorize('manageCollaborators', $file);

        $this->collaborationService->revokeAccess($file->id, $user->id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
