<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    
    protected $guarded = [];
    public $appends = ['name'];

    const ROLE_CLIENT = 'client';
    const ROLE_WRITER = 'writer';
    const ROLE_REVIEWER = 'reviewer';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getNameAttribute()
    {
        return sprintf("%s %s", $this->first_name, $this->last_name);
    }

    public function website()
    {
        return $this->hasMany(Websites::class, 'created_by_id');
    }
	
	public function favoriteTopic()
    {
        return $this->hasMany(TopicFavorite::class, 'user_id');
    }
}
