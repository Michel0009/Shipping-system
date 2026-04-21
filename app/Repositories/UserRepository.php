<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Refresh_token;

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

    public function find_by_email(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    public function find_user($id): ?User
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
    public function get_last_user()
    {
        return $this->user->max('id');
    }
    public function exists_by_user_number(string $userNumber): bool
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

    public function create_refresh_token($userId, $token, $expiresAt)
    {
        return Refresh_token::create([
            'user_id' => $userId,
            'token' => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);
    }

    public function find_refresh_token($token)
    {
        return Refresh_token::where('token', hash('sha256', $token))->first();
    }

    public function revoke_user_tokens($userId)
    {
        Refresh_token::where('user_id', $userId)
            ->update(['is_revoked' => true]);
    }

    public function revoke_token($token)
    {
        $token->update(['is_revoked' => true]);
    }
    public function update($userId, array $data)
    {
        $user = $this->user->find($userId);
        $user->update($data);
    }
}
