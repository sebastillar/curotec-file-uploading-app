<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepository;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    public function model()
    {
        return User::class;
    }

    public function findByEmail(string $email)
    {
        return $this->findByField('email', $email)->first();
    }
}
