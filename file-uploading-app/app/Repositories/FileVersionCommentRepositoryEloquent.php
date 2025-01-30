<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Interfaces\FileVersionCommentRepository;
use App\Models\FileVersionComment;
use App\Validators\FileVersionCommentValidator;

/**
 * Class FileVersionCommentRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class FileVersionCommentRepositoryEloquent extends BaseRepository implements FileVersionCommentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return FileVersionComment::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    public function findByVersion(int $versionId)
    {
        return $this->model->newQuery()
            ->where('file_version_id', $versionId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function createComment(int $versionId, array $data)
    {
        $data['file_version_id'] = $versionId;
        return $this->create($data);
    }

    public function getLatestComments(int $limit = 10)
    {
        return $this->with(['fileVersion.file'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
