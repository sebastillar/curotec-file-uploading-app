<?php

namespace Tests\Unit\Services;

use App\Events\CollaboratorAdded;
use App\Events\CollaboratorRemoved;
use App\Events\CollaboratorRoleUpdated;
use App\Models\FileCollaborator;
use App\Repositories\Interfaces\FileCollaboratorRepository;
use App\Repositories\Interfaces\FileRepository;
use App\Services\FileCollaborationService;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class FileCollaborationServiceTest extends TestCase
{
    private $collaboratorRepository;
    private $fileRepository;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collaboratorRepository = Mockery::mock(FileCollaboratorRepository::class);
        $this->fileRepository = Mockery::mock(FileRepository::class);

        $this->service = new FileCollaborationService(
            $this->collaboratorRepository,
            $this->fileRepository
        );

        Event::fake();
    }

    public function test_it_can_add_collaborator()
    {
        // Arrange
        $fileId = 1;
        $userId = 2;
        $role = 'viewer';

        $collaborator = new FileCollaborator([
            'file_id' => $fileId,
            'user_id' => $userId,
            'role' => $role
        ]);

        $this->collaboratorRepository->shouldReceive('isCollaborator')
            ->once()
            ->with($fileId, $userId)
            ->andReturn(false);

        $this->collaboratorRepository->shouldReceive('addCollaborator')
            ->once()
            ->with($fileId, $userId, $role)
            ->andReturn($collaborator);

        // Act
        $result = $this->service->addCollaborator($fileId, $userId, $role);

        // Assert
        $this->assertEquals($collaborator, $result);
        Event::assertDispatched(CollaboratorAdded::class);
    }

    public function test_it_cannot_add_existing_collaborator()
    {
        // Arrange
        $fileId = 1;
        $userId = 2;

        $this->collaboratorRepository->shouldReceive('isCollaborator')
            ->once()
            ->with($fileId, $userId)
            ->andReturn(true);

        // Assert & Act
        $this->expectException(InvalidArgumentException::class);
        $this->service->addCollaborator($fileId, $userId);
    }

    public function test_it_can_revoke_access()
    {
        // Arrange
        $fileId = 1;
        $userId = 2;

        $this->collaboratorRepository->shouldReceive('revokeAccess')
            ->once()
            ->with($fileId, $userId)
            ->andReturn(true);

        // Act
        $result = $this->service->revokeAccess($fileId, $userId);

        // Assert
        $this->assertTrue($result);
        Event::assertDispatched(CollaboratorRemoved::class);
    }

    public function test_it_can_update_role()
    {
        // Arrange
        $fileId = 1;
        $userId = 2;
        $newRole = 'editor';

        $collaborator = new FileCollaborator([
            'file_id' => $fileId,
            'user_id' => $userId,
            'role' => $newRole
        ]);

        $this->collaboratorRepository->shouldReceive('updateRole')
            ->once()
            ->with($fileId, $userId, $newRole)
            ->andReturn($collaborator);

        // Act
        $result = $this->service->updateRole($fileId, $userId, $newRole);

        // Assert
        $this->assertEquals($collaborator, $result);
        Event::assertDispatched(CollaboratorRoleUpdated::class);
    }

    public function test_it_can_get_file_collaborators()
    {
        // Arrange
        $fileId = 1;
        $collaborators = collect([
            new FileCollaborator(['file_id' => $fileId, 'user_id' => 2]),
            new FileCollaborator(['file_id' => $fileId, 'user_id' => 3])
        ]);

        $this->collaboratorRepository->shouldReceive('getFileCollaborators')
            ->once()
            ->with($fileId)
            ->andReturn($collaborators);

        // Act
        $result = $this->service->getFileCollaborators($fileId);

        // Assert
        $this->assertEquals($collaborators, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
