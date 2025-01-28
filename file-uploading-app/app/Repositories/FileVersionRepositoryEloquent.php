<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Interfaces\FileVersionRepository;
use App\Models\FileVersion;
use App\Validators\FileVersionValidator;

/**
 * Class FileVersionRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class FileVersionRepositoryEloquent extends BaseRepository implements FileVersionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return FileVersion::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function findByFile($fileId)
    {
        return $this->model
            ->where('file_id', $fileId)
            ->withCount('comments')
            ->get();
    }

    public function createVersion($fileId, array $data)
    {
        $data['file_id'] = $fileId;
        $data['version_number'] = $this->getLatestVersionNumber($fileId) + 1;

        return $this->create($data);
    }

    public function getLatestVersionNumber(int $fileId): int
    {
        return $this->model->where('file_id', $fileId)->max('version_number') ?? 0;
    }

    public function getWithComments(int $versionId)
    {
        return $this->model->with(['comments', 'file'])->findOrFail($versionId);
    }
}
