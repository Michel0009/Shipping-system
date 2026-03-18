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
    public function get_last_user(){
        return $this->user->max('id');
    }
    public function existsByUserNumber(string $userNumber): bool
    {
        return User::where('user_number', $userNumber)->exists();
    }

    public function delete_unverified_users()
    {
        $this->user->whereNull('email_verified_at')
        ->where(function ($query) {

            $query->where(function ($q) {
                $q->where('role_id', 3)
                  ->where('created_at', '<=', now()->subDay());
            })
            ->orWhere(function ($q) {
                $q->where('role_id', 4)
                  ->where('created_at', '<=', now()->subDays(14));
            });

        })
        ->delete();
    }

}
