<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use App\Models\FileCollaborator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileCollaborationTest extends TestCase
{
    use RefreshDatabase;

    private User $author;
    private User $collaborator;
    private File $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create();
        $this->collaborator = User::factory()->create();
        $this->file = File::factory()->create([
            'author_id' => $this->author->id
        ]);
    }

    public function test_author_can_add_collaborator()
    {
        $response = $this->actingAs($this->author)
            ->postJson("/api/files/{$this->file->id}/collaborators", [
                'user_id' => $this->collaborator->id,
                'role' => 'viewer'
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'file_id',
                'user_id',
                'role',
                'created_at',
                'updated_at'
            ]);

        $this->assertDatabaseHas('file_collaborators', [
            'file_id' => $this->file->id,
            'user_id' => $this->collaborator->id,
            'role' => 'viewer'
        ]);
    }

    public function test_non_author_cannot_add_collaborator()
    {
        $nonAuthor = User::factory()->create();

        $response = $this->actingAs($nonAuthor)
            ->postJson("/api/files/{$this->file->id}/collaborators", [
                'user_id' => $this->collaborator->id,
                'role' => 'viewer'
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('file_collaborators', [
            'file_id' => $this->file->id,
            'user_id' => $this->collaborator->id
        ]);
    }

    public function test_author_can_update_collaborator_role()
    {
        // Create initial collaboration
        FileCollaborator::factory()->create([
            'file_id' => $this->file->id,
            'user_id' => $this->collaborator->id,
            'role' => 'viewer'
        ]);

        $response = $this->actingAs($this->author)
            ->putJson("/api/files/{$this->file->id}/collaborators/{$this->collaborator->id}", [
                'role' => 'editor'
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('file_collaborators', [
            'file_id' => $this->file->id,
            'user_id' => $this->collaborator->id,
            'role' => 'editor'
        ]);
    }

    public function test_author_can_revoke_access()
    {
        // Create initial collaboration
        FileCollaborator::factory()->create([
            'file_id' => $this->file->id,
            'user_id' => $this->collaborator->id,
            'role' => 'viewer'
        ]);

        $response = $this->actingAs($this->author)
            ->deleteJson("/api/files/{$this->file->id}/collaborators/{$this->collaborator->id}");

        $response->assertNoContent();

        $this->assertNotNull(
            FileCollaborator::where('file_id', $this->file->id)
                ->where('user_id', $this->collaborator->id)
                ->value('revoked_at')
        );
    }

    public function test_cannot_add_duplicate_collaborator()
    {
        // Create initial collaboration
        FileCollaborator::factory()->create([
            'file_id' => $this->file->id,
            'user_id' => $this->collaborator->id,
            'role' => 'viewer'
        ]);

        $response = $this->actingAs($this->author)
            ->postJson("/api/files/{$this->file->id}/collaborators", [
                'user_id' => $this->collaborator->id,
                'role' => 'editor'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_author_can_list_collaborators()
    {
        // Create multiple collaborators
        FileCollaborator::factory()->count(3)->create([
            'file_id' => $this->file->id
        ]);

        $response = $this->actingAs($this->author)
            ->getJson("/api/files/{$this->file->id}/collaborators");

        $response->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'file_id',
                    'user_id',
                    'role',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_validation_rules_for_adding_collaborator()
    {
        $response = $this->actingAs($this->author)
            ->postJson("/api/files/{$this->file->id}/collaborators", [
                'user_id' => 999999, // Non-existent user
                'role' => 'invalid_role'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id', 'role']);
    }

    public function test_validation_rules_for_updating_role()
    {
        FileCollaborator::factory()->create([
            'file_id' => $this->file->id,
            'user_id' => $this->collaborator->id,
            'role' => 'viewer'
        ]);

        $response = $this->actingAs($this->author)
            ->putJson("/api/files/{$this->file->id}/collaborators/{$this->collaborator->id}", [
                'role' => 'invalid_role'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }
}
