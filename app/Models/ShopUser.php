<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ShopUser extends Authenticatable
{
    protected $table = 'users';

    public $timestamps = false;

    protected $guarded = ['id', 'role'];

    protected $hidden = ['password_hash', 'remember_token'];

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
