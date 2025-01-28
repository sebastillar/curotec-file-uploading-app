<?php

namespace App\Repositories\Interfaces;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface FileVersionCommentRepository.
 *
 * @package namespace App\Repositories;
 */
interface FileVersionCommentRepository extends RepositoryInterface
{
    public function findByVersion(int $versionId);
    public function createComment(int $versionId, array $data);
    public function getLatestComments(int $limit = 10);
}
