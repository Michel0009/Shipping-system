<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'user_number',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'location',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function device_tokens()
    {
        return $this->hasMany(Device_token::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }
    public function bans()
    {
        return $this->hasMany(Ban::class);
    }
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }
    public function reports_made()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reports_received()
    {
        return $this->hasMany(Report::class, 'reported_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
