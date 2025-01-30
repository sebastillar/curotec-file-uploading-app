<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\FileVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class FileManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function user_can_upload_a_single_file()
    {
        // Arrange
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        // Act
        $response = $this->postJson('/files/upload', [
            'file' => $file
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'file' => [
                    'id',
                    'name',
                    'extension',
                    'mime_type',
                    'size',
                    'size_formatted',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('files', [
            'name' => 'document.pdf',
            'extension' => 'pdf'
        ]);

        $this->assertDatabaseHas('file_versions', [
            'version_number' => 1,
            'file_id' => $response->json('file.id')
        ]);
    }

    /** @test */
    public function user_can_upload_multiple_files()
    {
        // Arrange
        $files = [
            UploadedFile::fake()->create('doc1.pdf', 1024),
            UploadedFile::fake()->create('doc2.pdf', 1024)
        ];

        // Act
        $response = $this->postJson('/files/upload/multiple', [
            'files' => $files
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'files' => [
                    '*' => [
                        'id',
                        'name',
                        'extension',
                        'size_formatted'
                    ]
                ]
            ]);

        $this->assertDatabaseCount('files', 2);
        $this->assertDatabaseCount('file_versions', 2);
    }

    /** @test */
    public function user_can_add_new_version_to_existing_file()
    {
        // Arrange
        $file = File::factory()->create();
        $version = FileVersion::factory()->create([
            'file_id' => $file->id,
            'version_number' => 1
        ]);

        $newVersionFile = UploadedFile::fake()->create('document_v2.pdf', 1024);

        // Act
        $response = $this->postJson("/files/{$file->id}/versions", [
            'file' => $newVersionFile
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'version' => [
                    'id',
                    'version_number',
                    'path',
                    'download_url',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('file_versions', [
            'file_id' => $file->id,
            'version_number' => 2
        ]);
    }

    /** @test */
    public function user_can_get_file_versions()
    {
        // Arrange
        $file = File::factory()->create();
        $versions = FileVersion::factory()->count(3)->create([
            'file_id' => $file->id
        ]);

        // Verify route exists
        $this->assertTrue(Route::has('files.versions.download'), 'Download route not found');

        // Act
        $response = $this->getJson("/files/{$file->id}/versions");

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'version_number',
                        'path',
                        'download_url',
                        'comments_count',
                        'created_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_add_comment_to_version()
    {
        // Arrange
        $version = FileVersion::factory()->create();
        $comment = $this->faker->sentence();

        // Act
        $response = $this->postJson("/files/versions/{$version->id}/comments", [
            'comment' => $comment
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'comment' => [
                    'id',
                    'comment',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('file_version_comments', [  // Changed from version_comments
            'file_version_id' => $version->id,
            'comment' => $comment
        ]);
    }

    /** @test */
    public function user_can_download_version()
    {
        // Arrange
        Storage::fake('public');

        $file = File::factory()->create([
            'name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 1024
        ]);

        $version = FileVersion::factory()->create([
            'file_id' => $file->id,
            'path' => "files/{$file->id}/test.pdf",
            'version_number' => 1
        ]);

        // Create a test PDF file
        $content = str_repeat('a', 1024);
        $stored = Storage::disk('public')->put($version->path, $content);

        // Verify file was stored
        $this->assertTrue($stored);
        $this->assertTrue(Storage::disk('public')->exists($version->path));

        // Act
        $response = $this->get("files/versions/{$version->id}/download");

        // Assert
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="test.pdf"');
    }

    /** @test */
    public function user_can_get_latest_uploads()
    {
        // Arrange
        Storage::fake('public');

        $files = File::factory()->count(5)->create([
            'size' => 1024 // Set a specific size
        ]);

        foreach ($files as $file) {
            $fakePath = "files/{$file->id}/test.{$file->extension}";
            Storage::disk('public')->put($fakePath, str_repeat('a', 1024));

            FileVersion::factory()->create([
                'file_id' => $file->id,
                'path' => $fakePath
            ]);
        }

        // Act
        $response = $this->getJson('/files/latest?limit=3');

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'extension',
                        'size_formatted',
                        'created_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    /** @test */
    public function it_validates_file_size()
    {
        // Arrange
        $largeFile = UploadedFile::fake()->create('large.pdf', 11264); // 11MB

        // Act
        $response = $this->postJson('/files/upload', [
            'file' => $largeFile
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_validates_file_type()
    {
        // Arrange
        $invalidFile = UploadedFile::fake()->create('test.exe', 1024);

        // Act
        $response = $this->postJson('/files/upload', [
            'file' => $invalidFile
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_validates_comment_length()
    {
        // Arrange
        $version = FileVersion::factory()->create();
        $longComment = str_repeat('a', 1001);

        // Act
        $response = $this->postJson("/files/versions/{$version->id}/comments", [
            'comment' => $longComment
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['comment']);
    }
}
