<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\Interfaces\FileRepository;
use App\Models\File;
use App\Validators\FileValidator;
use App\Models\User;
/**
 * Class FileRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class FileRepositoryEloquent extends BaseRepository implements FileRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return File::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));

    }
    public function find($id, $columns = ['*'])
    {
        return $this->model->with('versions')->find($id);
    }

    public function getLatestWithVersions(User $user, $limit = 10)
    {
        return $this
            ->model
            ->latestWithVersions()
            ->where(function ($query) use ($user) {
                $query->where('author_id', $user->id)
                      ->orWhereHas('collaborators', function ($q) use ($user) {
                          $q->where('user_id', $user->id)
                            ->whereNull('revoked_at');
                      });
            })
            ->limit($limit);
    }
}
