<?php

namespace App\Repositories;

use App\Models\Ban;
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
        $statusLabels = $this->getStatusLabels();
        $users = $this->user->where('role_id', 3)
            ->select('id', 'user_number', 'first_name', 'last_name', 'email', 'phone_number', 'status', 'created_at')
            ->paginate(5);
        return $users->through(function ($user) use ($statusLabels) {
            $user->status_label = $statusLabels[$user->status] ?? null;
            return [
                'id' => $user->id,
                'user_number' => $user->user_number,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'status' => $user->status_label,
                'created_at' => $user->created_at,
            ];;
        });
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
    public function get_sub_admins()
    {
        $statusLabels = $this->getStatusLabels();
        return $this->user->where('role_id', 2)->select('id', 'user_number', 'first_name', 'last_name', 'phone_number', 'status')->get()->map(function ($user) use ($statusLabels) {
            $user->status_label = $statusLabels[$user->status] ?? null;
            return [
                'id' => $user->id,
                'user_number' => $user->user_number,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number' => $user->phone_number,
                'status' => $user->status_label,
            ];;
        });
    }
    public function getStatusLabels(): array
    {
        return $this->user->getStatusLabels();
    }
    public function create_ban(array $data)
    {
        return Ban::create($data);
    }
    public function get_latest_ban($userId)
    {
        return Ban::where('user_id', $userId)->latest()->first();
    }
    public function get_expired_bans()
    {
        return Ban::whereNotNull('end_date')
            ->where('end_date', '<=', now())
            ->get();
    }

    public function get_all_app_users()
    {
        return $this->user->whereIn('role_id', [3, 4])->get();
    }
    public function find_by_user_number($user_number)
    {
        $statusLabels = User::getStatusLabels();
        $user = $this->user
            ->where('role_id', 3)
            ->where('user_number', $user_number)->select('id', 'user_number', 'first_name', 'last_name', 'email', 'phone_number', 'status', 'created_at')
            ->first();
        if (!$user) {
            return false;
        }
        return [
            'id'           => $user->id,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'email'        => $user->email,
            'phone_number' => $user->phone_number,
            'user_number'  => $user->user_number,
            'status'       => $statusLabels[$user->status] ?? null,
            'created_at'   => $user->created_at,
        ];
    }
    public function get_drivers_user()
    {
        return $this->user->where('role_id', 4)->get();
    }
    public function find_client_user($id)
    {
        $statusLabels = User::getStatusLabels();
        $user = $this->user->where('role_id', 3)->where('id', $id)->select('id', 'user_number', 'first_name', 'last_name', 'email', 'phone_number', 'status', 'created_at')->first();
        if (!$user) {
            return false;
        }
        return [
            'id' => $user->id,
            'user_number' => $user->user_number,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'status' => $statusLabels[$user->status] ?? null,
            'created_at' => $user->created_at,
        ];
    }
    public function get_blocked_users()
    {
        $users = $this->user->where('status', 3)
            ->where('role_id', 3)
            ->with('latestBan')
            ->select('id', 'user_number', 'first_name', 'last_name', 'email', 'phone_number', 'status', 'created_at')
            ->paginate(5);
        return $users->through(function ($user) {
            return [
                'id' => $user->id,
                'user_number' => $user->user_number,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'status' => "محظور",
                'ban_end_date' => $user->latestBan ? $user->latestBan->end_date : null,
                'ban_explanation' => $user->latestBan ? $user->latestBan->explaination : null,
                'created_at' => $user->created_at,
            ];
        });
    }
    public function get_blocked_sub_admins()
    {
        $users = $this->user->where('status', 3)
            ->where('role_id', 2)
            ->with('latestBan')
            ->select('id', 'user_number', 'first_name', 'last_name', 'email', 'phone_number', 'status', 'created_at')
            ->paginate(5);
        return $users->through(function ($user) {
            return [
                'id' => $user->id,
                'user_number' => $user->user_number,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'status' => "محظور",
                'ban_end_date' => $user->latestBan ? $user->latestBan->end_date : null,
                'ban_explanation' => $user->latestBan ? $user->latestBan->explaination : null,
                'created_at' => $user->created_at,
            ];
        });
    }
    public function get_user_statistics()
    {
        $users = $this->user->whereIn('role_id', [3, 4])
            ->get(['role_id', 'status']);

        $totalUsers = $users->count();

        $clientsCount        = $users->where('role_id', 3)->where('status', 0)->count();
        $driversCount        = $users->where('role_id', 4)->whereIn('status', [0, 1])->count();
        $frozenDriversCount  = $users->where('role_id', 4)->where('status', 2)->count();
        $blockedClientsCount = $users->where('role_id', 3)->where('status', 3)->count();
        $blockedDriversCount = $users->where('role_id', 4)->where('status', 3)->count();

        $calculatePercentage = function ($count) use ($totalUsers) {
            return $totalUsers > 0 ? round(($count / $totalUsers) * 100, 2) : 0;
        };

        return [
            'clients_count'      => $clientsCount,
            'clients_percentage' => $calculatePercentage($clientsCount),

            'drivers_count'      => $driversCount,
            'drivers_percentage' => $calculatePercentage($driversCount),

            'frozen_drivers_count'      => $frozenDriversCount,
            'frozen_drivers_percentage' => $calculatePercentage($frozenDriversCount),

            'blocked_clients_count'      => $blockedClientsCount,
            'blocked_clients_percentage' => $calculatePercentage($blockedClientsCount),

            'blocked_drivers_count'      => $blockedDriversCount,
            'blocked_drivers_percentage' => $calculatePercentage($blockedDriversCount),
        ];
    }
    public function get_clients_count()
    {
       return $this->user->where('role_id', 3)->count();
    }
    public function get_drivers_count(){
        return $this->user->where('role_id', 4)->count();
    }
}
