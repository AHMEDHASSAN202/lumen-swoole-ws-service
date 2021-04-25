<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $primaryKey = 'user_id';
    
    protected $hidden = [
        'password',
        'user_password',
        'user_token'
    ];

    public function hasPermissions($permission)
    {
        $permissions = is_array($permission) ? $permission : [$permission];
        $userPermissions = optional($this)->permissions;
        if (!$userPermissions) return false;
        $userPermissions = json_decode($userPermissions);
        foreach ($permissions as $per) {
            if (!in_array($per, $userPermissions)) {
                return false;
            }
        }
        return true;
    }
}