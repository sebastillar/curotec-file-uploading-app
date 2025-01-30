<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\Interfaces\FileServiceInterface;
use App\Http\Requests\File\UploadFileRequest;
use App\Http\Requests\File\UploadMultipleFilesRequest;
use App\Http\Requests\File\AddVersionRequest;
use App\Http\Requests\File\AddCommentRequest;
use App\Http\Requests\File\GetLatestRequest;
use App\Http\Resources\FileResource;
use App\Http\Resources\FileCollection;
use App\Http\Resources\FileVersionResource;
use App\Http\Resources\FileVersionCommentResource;

/**
 * @group File Management
 *
 * APIs for managing files, versions and comments
 */
class FileController extends Controller
{
    public function __construct(
        private FileServiceInterface $fileService
    ) {}

    /**
     * Upload Single File
     *
     * Upload a new file to the system.
     *
     * @bodyParam file file required The file to upload. Must not exceed 10MB. Example: document.pdf
     *
     * @response 201 {
     *   "message": "File uploaded successfully",
     *   "file": {
     *     "id": 1,
     *     "name": "document.pdf",
     *     "extension": "pdf",
     *     "mime_type": "application/pdf",
     *     "size": 1048576,
     *     "size_formatted": "1.00 MB",
     *     "created_at": "2024-01-27T12:00:00Z",
     *     "updated_at": "2024-01-27T12:00:00Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "file": [
     *       "The file size must not exceed 10MB."
     *     ]
     *   }
     * }
     */
    public function upload(UploadFileRequest $request)
    {
        $file = $this->fileService->uploadFile($request->file('file'));

        return response()->json([
            'message' => trans('files.messages.uploaded'),
            'file' => new FileResource($file)
        ], Response::HTTP_CREATED);
    }

    /**
     * Upload Multiple Files
     *
     * Upload multiple files simultaneously.
     *
     * @bodyParam files[] file required Array of files to upload. Each file must not exceed 10MB. Example: document1.pdf, document2.pdf
     *
     * @response 201 {
     *   "message": "Files uploaded successfully",
     *   "files": [
     *     {
     *       "id": 1,
     *       "name": "document1.pdf",
     *       "extension": "pdf",
     *       "mime_type": "application/pdf",
     *       "size": 1048576,
     *       "size_formatted": "1.00 MB",
     *       "created_at": "2024-01-27T12:00:00Z",
     *       "updated_at": "2024-01-27T12:00:00Z"
     *     }
     *   ]
     * }
     */
    public function uploadMultiple(UploadMultipleFilesRequest $request)
    {
        $files = $this->fileService->uploadMultipleFiles($request->file('files'));

        return response()->json([
            'message' => trans('files.messages.multiple_uploaded'),
            'files' => FileResource::collection($files),
        ], Response::HTTP_CREATED);
    }

    /**
     * Add New Version
     *
     * Add a new version to an existing file.
     *
     * @urlParam fileId integer required The ID of the file. Example: 1
     * @bodyParam file file required The new version file. Must not exceed 10MB.
     *
     * @response 201 {
     *   "message": "New version added successfully",
     *   "version": {
     *     "id": 1,
     *     "version_number": 2,
     *     "path": "files/1/version_2.pdf",
     *     "download_url": "http://example.com/api/files/versions/1/download",
     *     "created_at": "2024-01-27T12:00:00Z",
     *     "updated_at": "2024-01-27T12:00:00Z"
     *   }
     * }
     */
    public function addVersion(AddVersionRequest $request, int $fileId)
    {
        $version = $this->fileService->addNewVersion($fileId, $request->file('file'));

        return response()->json([
            'message' =>  trans('files.messages.version_added'),
            'version' => new FileVersionResource($version),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get File Versions
     *
     * Retrieve all versions of a specific file.
     *
     * @urlParam fileId integer required The ID of the file. Example: 1
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "version_number": 2,
     *       "path": "files/1/version_2.pdf",
     *       "download_url": "http://example.com/api/files/versions/1/download",
     *       "comments_count": 3,
     *       "created_at": "2024-01-27T12:00:00Z",
     *       "updated_at": "2024-01-27T12:00:00Z"
     *     }
     *   ]
     * }
     */
    public function versions(int $fileId)
    {
        $versions = $this->fileService->getFileVersions($fileId);

        return FileVersionResource::collection($versions);
    }

    /**
     * Add Version Comment
     *
     * Add a comment to a specific file version.
     *
     * @urlParam versionId integer required The ID of the version. Example: 1
     * @bodyParam comment string required The comment text. Must not exceed 1000 characters. Example: This version includes the requested changes.
     *
     * @response 201 {
     *   "message": "Comment added successfully",
     *   "comment": {
     *     "id": 1,
     *     "comment": "This version includes the requested changes.",
     *     "created_at": "2024-01-27T12:00:00Z",
     *     "updated_at": "2024-01-27T12:00:00Z"
     *   }
     * }
     */
    public function addComment(AddCommentRequest $request, int $versionId)
    {
        $comment = $this->fileService->addVersionComment(
            $versionId,
            $request->input('comment')
        );

        return response()->json([
            'message' => trans('files.messages.comment_added'),
            'comment' => new FileVersionCommentResource($comment),
        ], Response::HTTP_CREATED);
    }

    /**
     * Download Version File
     *
     * Download a specific version of a file.
     *
     * @urlParam versionId integer required The ID of the version to download. Example: 1
     *
     * @response file
     * @response 404 {
     *   "message": "Version not found"
     * }
     * @response 403 {
     *   "message": "You do not have permission to download this file"
     * }
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function download(int $versionId): StreamedResponse
    {
        return $this->fileService->downloadVersion($versionId);
    }

    /**
     * Get Latest Uploads
     *
     * Retrieve the latest file uploads with pagination.
     *
     * @queryParam limit integer The number of items per page. Default: 10 Example: 15
     * @queryParam page integer The page number. Default: 1 Example: 1
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "document.pdf",
     *       "extension": "pdf",
     *       "size_formatted": "1.00 MB",
     *       "created_at": "2024-01-27T12:00:00Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 10,
     *     "total": 50
     *   }
     * }
     */
    public function latest(GetLatestRequest $request)
    {
        $limit = $request->input('limit', config('files.pagination.default_limit'));
        $uploads = $this->fileService->getLatestUploads($limit);

        return new FileCollection($uploads);
    }
}
