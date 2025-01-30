<?php

namespace App\Repositories\Interfaces;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface FileRepository.
 *
 * @package namespace App\Repositories;
 */
interface FileRepository extends RepositoryInterface
{
    public function find($id, $columns = ['*']);
}
