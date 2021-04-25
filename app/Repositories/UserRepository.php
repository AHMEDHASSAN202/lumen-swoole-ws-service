<?php 

namespace App\Repositories;

use App\Models\User;

class UserRepository {

    public function getUserByToken($userToken)
    {
        return User::select('user_id', 'fk_role_id', 'user_name', 'user_token', 'role_id', 'permissions')
                ->leftJoin('roles', 'role_id', '=', 'user_id')
                ->where('user_token', $userToken)
                ->first();
    }

}