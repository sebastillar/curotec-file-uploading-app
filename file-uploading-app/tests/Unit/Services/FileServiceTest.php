<?php

namespace Tests\Unit\Services;

use App\Models\File;
use App\Models\FileVersion;
use App\Models\FileVersionComment;
use App\Repositories\Interfaces\FileRepository;
use App\Repositories\Interfaces\FileVersionRepository;
use App\Repositories\Interfaces\FileVersionCommentRepository;
use App\Services\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use App\Events\FileUploaded;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\Services\Traits\WithTestUser;

class FileServiceTest extends TestCase
{
    use RefreshDatabase, WithTestUser;

    private FileService $fileService;
    private $fileRepository;
    private $versionRepository;
    private $commentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks
        $this->fileRepository = Mockery::mock(FileRepository::class);
        $this->versionRepository = Mockery::mock(FileVersionRepository::class);
        $this->commentRepository = Mockery::mock(FileVersionCommentRepository::class);

        // Create service instance with mocks
        $this->fileService = new FileService(
            $this->fileRepository,
            $this->versionRepository,
            $this->commentRepository
        );

        // Create fake storage disk
        Storage::fake('public');

        $this->setUpTestUser();
    }

    protected function createTestFile(array $attributes = []): File
    {
        return new File(array_merge([
            'name' => 'document.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size' => 1048576,
            'author_id' => $this->testUser->id
        ], $attributes));
    }

    /** @test */
    public function it_can_upload_a_file()
    {
        // Arrange
        Storage::fake('public');
        Event::fake();

        $uploadedFile = UploadedFile::fake()->create('document.pdf', 1024);

        // Create models
        $file = $this->createTestFile();

        $file->forceFill(['id' => 1]);
        $file->exists = true;

        $version = new FileVersion([
            'id' => 1,
            'file_id' => 1,
            'version_number' => 1,
            'path' => 'files/1/version_1.pdf'
        ]);

        // Set up the relationship
        $fileWithVersion = (new File($file->toArray()))->forceFill(['id' => 1]);
        $fileWithVersion->setRelation('versions', collect([$version]));

        // Mock repository responses
        $this->fileRepository->shouldReceive('create')
            ->once()
            ->andReturnUsing(function($attributes) use ($file) {
                return $file;
            });

        $this->versionRepository->shouldReceive('createVersion')
            ->once()
            ->andReturn($version);

        $this->fileRepository->shouldReceive('find')
            ->with(1)
            ->andReturn($fileWithVersion);

        // Act
        $result = $this->fileService->uploadFile($uploadedFile);

        // Assert
        $this->assertEquals(1, $result->id);
        $this->assertEquals('document.pdf', $result->name);
        $this->assertTrue($result->relationLoaded('versions'));
        $this->assertCount(1, $result->versions);
        $this->assertInstanceOf(FileVersion::class, $result->versions->first());

        // Assert event was dispatched
        Event::assertDispatched(FileUploaded::class, function ($event) use ($file, $version) {
            return $event->file->id === $file->id && $event->version->id === $version->id;
        });
    }

    /** @test */
    public function it_can_upload_multiple_files()
    {
        // Arrange
        Storage::fake('public');
        Event::fake();

        $uploadedFiles = [
            UploadedFile::fake()->create('doc1.pdf', 1024),
            UploadedFile::fake()->create('doc2.pdf', 1024)
        ];

        // Create proper model instances
        $files = [
            tap($this->createTestFile(['name' => 'doc1.pdf']), function($file) {
                $file->id = 1;
                $file->exists = true;
            }),
            tap($this->createTestFile(['name' => 'doc2.pdf']), function($file) {
                $file->id = 2;
                $file->exists = true;
            })
        ];

        // Track created files and debug calls
        $createdFiles = [];
        $debugLog = [];

        // Mock file creation
        $this->fileRepository->shouldReceive('create')
            ->twice()
            ->andReturnUsing(function($data) use ($files, &$createdFiles, &$debugLog) {
                static $count = 0;
                $file = clone $files[$count];

                $debugLog[] = "Creating file {$file->id}";
                $debugLog[] = "File data: " . json_encode($data);

                // Store for find mock
                $createdFiles[$file->id] = $file;
                $debugLog[] = "Stored file {$file->id} in createdFiles";
                $debugLog[] = "Current createdFiles keys: " . implode(', ', array_keys($createdFiles));

                $count++;
                return $file;
            });

        // Mock version creation
        $this->versionRepository->shouldReceive('createVersion')
            ->twice()
            ->andReturnUsing(function($fileId, $data) use (&$createdFiles, &$debugLog) {
                $debugLog[] = "Creating version for file {$fileId}";
                $debugLog[] = "Version data: " . json_encode($data);

                Storage::disk('public')->put($data['path'], 'test content');

                $version = new FileVersion([
                    'id' => $fileId,
                    'file_id' => $fileId,
                    'version_number' => 1,
                    'path' => $data['path']
                ]);

                // Add version to the file
                if (isset($createdFiles[$fileId])) {
                    $createdFiles[$fileId]->setRelation('versions', collect([$version]));
                    $debugLog[] = "Added version to file {$fileId}";
                } else {
                    $debugLog[] = "WARNING: File {$fileId} not found when adding version!";
                }

                return $version;
            });

        // Mock find to return the created file
        $this->fileRepository->shouldReceive('find')
            ->times(2)
            ->andReturnUsing(function($id) use (&$createdFiles, &$debugLog) {
                $debugLog[] = "Finding file {$id}";
                $debugLog[] = "Available files: " . implode(', ', array_keys($createdFiles));

                if (!isset($createdFiles[$id])) {
                    $debugLog[] = "ERROR: File {$id} not found in createdFiles!";
                    $debugLog[] = "createdFiles dump: " . print_r($createdFiles, true);
                    $debugLog[] = "Backtrace: " . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
                    throw new ModelNotFoundException("No query results for model [App\Models\File] {$id}");
                }

                $debugLog[] = "Found file {$id}";
                return clone $createdFiles[$id];
            });

        try {
            $debugLog[] = "\n=== Starting uploadMultipleFiles ===";

            // Act
            foreach ($uploadedFiles as $index => $uploadedFile) {
                $debugLog[] = "Processing file {$index}";
                $debugLog[] = "Current createdFiles: " . implode(', ', array_keys($createdFiles));
            }

            $result = $this->fileService->uploadMultipleFiles($uploadedFiles);

            $debugLog[] = "=== Finished uploadMultipleFiles ===\n";

            // Assert
            $this->assertCount(2, $result);

            foreach ($result as $index => $file) {
                $this->assertEquals($files[$index]->id, $file->id);
                $this->assertEquals($files[$index]->name, $file->name);
                $this->assertTrue($file->relationLoaded('versions'));
                $this->assertCount(1, $file->versions);
            }

            // Assert events were dispatched
            Event::assertDispatched(FileUploaded::class, function ($event) use ($files) {
                return $event->file->id === 1;
            });
            Event::assertDispatched(FileUploaded::class, function ($event) use ($files) {
                return $event->file->id === 2;
            });

            // Verify files were stored
            $this->assertNotEmpty(Storage::disk('public')->allFiles("files/1"));
            $this->assertNotEmpty(Storage::disk('public')->allFiles("files/2"));

        } catch (\Exception $e) {
            // Output debug log if test fails
            echo "\nDebug Log at failure:\n";
            echo implode("\n", $debugLog);

            // Add additional error context
            echo "\n\nException occurred in: " . get_class($e);
            echo "\nMessage: " . $e->getMessage();
            echo "\nStack trace:\n" . $e->getTraceAsString();

            throw $e;
        }
    }


    /** @test */
    public function it_can_add_new_version()
    {
        // Arrange
        Storage::fake('public');
        Event::fake();

        $fileId = 1;
        $uploadedFile = UploadedFile::fake()->create('document_v2.pdf', 1024);

        $file = new File([
            'id' => $fileId,
            'name' => 'document.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024
        ]);

        // Mock the file repository
        $this->fileRepository->shouldReceive('find')
            ->once()
            ->with($fileId)
            ->andReturn($file);

        // Capture the path used for storage
        $storedPath = null;

        // Mock the version repository
        $this->versionRepository->shouldReceive('createVersion')
            ->once()
            ->withArgs(function($actualFileId, $data) use ($fileId, &$storedPath) {
                $storedPath = $data['path'];
                return $actualFileId === $fileId;
            })
            ->andReturnUsing(function() use ($fileId, &$storedPath, $file) {
                $version = new FileVersion([
                    'id' => 2,
                    'file_id' => $fileId,
                    'version_number' => 2,
                    'path' => $storedPath
                ]);
                $version->setRelation('file', $file);
                $version->exists = true;
                $version->setAttribute('id', 2);
                return $version;
            });

        // Act
        $result = $this->fileService->addNewVersion($fileId, $uploadedFile);

        // Assert
        $this->assertNotNull($result, 'Result should not be null');
        $this->assertInstanceOf(FileVersion::class, $result);
        $this->assertEquals(2, $result->id);
        $this->assertEquals($fileId, $result->file_id);
        $this->assertTrue(Storage::disk('public')->exists($result->path), 'File should exist at the stored path');

        Event::assertDispatched(\App\Events\FileUploaded::class, function ($event) use ($file, $result) {
            return $event->file->id === $file->id && $event->version->id === $result->id;
        });
    }

    /** @test */
    public function it_can_add_version_comment()
    {
        Event::fake();
        // Arrange
        $versionId = 1;
        $commentText = 'Test comment';

        $comment = new FileVersionComment([
            'id' => 1,
            'file_version_id' => $versionId,
            'comment' => $commentText
        ]);

        // Expectations
        $this->commentRepository->shouldReceive('createComment')
            ->once()
            ->with($versionId, ['comment' => $commentText])
            ->andReturn($comment);

        // Act
        $result = $this->fileService->addVersionComment($versionId, $commentText);

        // Assert
        $this->assertEquals($comment->id, $result->id);
        $this->assertEquals($commentText, $result->comment);
    }

    /** @test */
    public function it_can_get_file_versions()
    {
        // Arrange
        $fileId = 1;
        $versions = collect([
            new FileVersion(['id' => 1, 'version_number' => 1]),
            new FileVersion(['id' => 2, 'version_number' => 2])
        ]);

        // Expectations
        $this->versionRepository->shouldReceive('findByFile')
            ->once()
            ->with($fileId)
            ->andReturn($versions);

        // Act
        $result = $this->fileService->getFileVersions($fileId);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals($versions[0]->id, $result[0]->id);
    }

    /** @test */
    public function it_can_download_version()
    {
        // Arrange
        $versionId = 1;
        $version = new FileVersion([
            'id' => $versionId,
            'path' => 'files/1/test.pdf'
        ]);
        $version->file = new File([
            'name' => 'test.pdf',
            'mime_type' => 'application/pdf'
        ]);

        // Create test file
        Storage::disk('public')->put($version->path, 'test content');

        // Expectations
        $this->versionRepository->shouldReceive('getWithComments')  // Changed this line
            ->once()
            ->with($versionId)
            ->andReturn($version);

        // Act
        $response = $this->fileService->downloadVersion($versionId);

        // Assert
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        Storage::disk('public')->assertExists($version->path);
    }

    /** @test */
    public function it_can_get_latest_uploads()
    {
        // Arrange
        $limit = 5;
        $files = collect([
            new File([
                'id' => 1,
                'name' => 'doc1.pdf',
                'created_at' => now()
            ]),
            new File([
                'id' => 2,
                'name' => 'doc2.pdf',
                'created_at' => now()->subDay()
            ])
        ]);

        // Create a query builder mock
        $queryBuilder = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $queryBuilder->shouldReceive('paginate')
            ->once()
            ->with($limit)
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator(
                $files,
                $files->count(),
                $limit,
                1
            ));

        // Expectations
        $this->fileRepository->shouldReceive('getLatestWithVersions')
            ->once()
            ->andReturn($queryBuilder);

        // Act
        $result = $this->fileService->getLatestUploads($limit);

        // Assert
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
        $this->assertEquals($files[0]->id, $result->items()[0]->id);
        $this->assertEquals($files[1]->id, $result->items()[1]->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
