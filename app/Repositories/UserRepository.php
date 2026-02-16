<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function create(array $data): User
    {
        return $this->user->create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    public function findByID($id): ?User
    {
        return $this->user->where('id', $id)->first();
    }

    public function save(User $user): bool
    {
        return $user->save();
    }

    public function get_users()
    {
        return $this->user->where('role_id', 3)->get();
    }

}