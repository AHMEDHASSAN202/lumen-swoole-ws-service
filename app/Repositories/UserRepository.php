<?php 

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository {

    /**
     * Get User By user_token
     *
     * @param $userToken
     * @return mixed
     */
    public function getUserByToken($userToken)
    {
        return User::select('user_id', 'fk_role_id', 'user_name', 'user_token', 'role_id', 'permissions')
                    ->leftJoin('roles', 'role_id', '=', 'fk_role_id')
                    ->where('user_token', $userToken)
                    ->first();
    }

    public function getUsersById($ids=[])
    {
        if (empty($ids)) return [];

        return DB::table(User::getTableName())->select('user_id', 'user_name', 'user_email', 'user_avatar')->whereIn('user_id', $ids)->get();
    }
}