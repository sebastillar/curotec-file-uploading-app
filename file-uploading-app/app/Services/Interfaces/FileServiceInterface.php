<?php

namespace App\Services\Interfaces;

use Illuminate\Http\UploadedFile;

interface FileServiceInterface
{
    public function uploadFile(UploadedFile $file);
    public function uploadMultipleFiles(array $files);
    public function addNewVersion(int $fileId, UploadedFile $file);
    public function getFileVersions(int $fileId);
    public function addVersionComment(int $versionId, string $comment);
    public function downloadVersion(int $versionId);
    public function getLatestUploads(int $limit = 10);
}
