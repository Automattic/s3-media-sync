<?php

namespace WPCOM_VIP\App;

use WPCOM_VIP\Illuminate\Notifications\Notifiable;
use WPCOM_VIP\Illuminate\Foundation\Auth\User as Authenticatable;
class User extends \WPCOM_VIP\Illuminate\Foundation\Auth\User
{
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
}
