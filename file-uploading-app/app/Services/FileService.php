<?php

namespace App\Services;

use App\Events\FileUploaded;
use App\Events\NewVersionComment;
use App\Repositories\Interfaces\FileRepository;
use App\Repositories\Interfaces\FileVersionRepository;
use App\Repositories\Interfaces\FileVersionCommentRepository;
use App\Services\Interfaces\FileServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService implements FileServiceInterface
{
    public function __construct(
        private FileRepository $fileRepository,
        private FileVersionRepository $versionRepository,
        private FileVersionCommentRepository $commentRepository
    ) {}

    public function uploadFile(UploadedFile $uploadedFile)
    {
        $user = auth()->user();

        // Create a new file record
        $file = $this->fileRepository->create([
            'name' => $uploadedFile->getClientOriginalName(),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'author_id' => $user->id
        ]);

        // Ensure fileId is correctly passed and used
        $fileId = $file->id;
        if (!$fileId) {
            throw new \InvalidArgumentException('File ID must be provided');
        }

        // Store the file
        $path = $this->storeFile($fileId, $file->extension, $uploadedFile);

        // Create a new file version
        $version = $this->versionRepository->createVersion($fileId, [
            'version_number' => 1,
            'path' => $path,
        ]);

        // Set the relationship
        $file->setRelation('versions', collect([$version]));

        // Dispatch the FileUploaded event
        event(new FileUploaded($file, $version));

        // Return the file with versions loaded
        return $file;
    }

    public function uploadMultipleFiles(array $uploadedFiles)
    {
        $results = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $file = $this->uploadFile($uploadedFile);

            // Refresh the file model to get the latest data
            $results[] = $this->fileRepository->find($file->id);
        }

        return $results;
    }

    public function addNewVersion(int $fileId, UploadedFile $uploadedFile)
    {
        $file = $this->fileRepository->find($fileId);
        $path = $this->storeFile($fileId, $file->extension, $uploadedFile);

        $version = $this->versionRepository->createVersion($fileId, [
            'path' => $path
        ]);

        broadcast(new FileUploaded($file, $version))->toOthers();

        return $version->load('file');
    }

    public function getFileVersions(int $fileId)
    {
        return $this->versionRepository->findByFile($fileId);
    }

    public function addVersionComment(int $versionId, string $comment)
    {
        $comment = $this->commentRepository->createComment($versionId, [
            'comment' => $comment
        ]);

        broadcast(new NewVersionComment($comment))->toOthers();

        return $comment->load('fileVersion.file');
    }

    public function downloadVersion(int $versionId)
    {
        try {
            $version = $this->versionRepository->getWithComments($versionId);

            // Debug the path
            \Log::info('Attempting to download file:', [
                'version_id' => $versionId,
                'path' => $version->path,
                'exists' => Storage::disk('public')->exists($version->path)
            ]);

            if (!Storage::disk('public')->exists($version->path)) {
                return response()->json([
                    'message' => 'File not found',
                    'path' => $version->path
                ], 404);
            }

            return Storage::disk('public')->download(
                $version->path,
                $version->file->name,
                [
                    'Content-Type' => $version->file->mime_type,
                    'Content-Disposition' => 'attachment; filename="' . $version->file->name . '"'
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Download error:', [
                'message' => $e->getMessage(),
                'version_id' => $versionId
            ]);

            return response()->json([
                'message' => 'Error downloading file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getLatestUploads(int $limit = 10)
    {
        try {

            return $this->fileRepository->getLatestWithVersions($limit)->paginate($limit);

        } catch (\Exception $e) {
            \Log::error('Error in getLatestUploads:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $limit,
                1
            );
        }
    }

    private function storeFile(int $fileId, string $extension, UploadedFile $file): string
    {
        $uuid = Str::uuid();
        $path = "files/{$fileId}/{$uuid}/version.{$extension}";

        Storage::disk('public')->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        return $path;
    }
}
