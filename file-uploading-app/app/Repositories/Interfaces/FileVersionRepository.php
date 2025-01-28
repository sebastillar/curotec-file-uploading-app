<?php

namespace App\Repositories\Interfaces;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface FileVersionRepository.
 *
 * @package namespace App\Repositories;
 */
interface FileVersionRepository extends RepositoryInterface
{
    public function findByFile(int $fileId);
    public function createVersion(int $fileId, array $data);
    public function getLatestVersionNumber(int $fileId): int;
    public function getWithComments(int $versionId);
}
