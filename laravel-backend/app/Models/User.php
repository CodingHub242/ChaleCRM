<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'owner_id');
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'owner_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'owner_id');
    }
}
