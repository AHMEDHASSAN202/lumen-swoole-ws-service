<?php 

namespace App\Repositories;


use SwooleTW\Http\Table\Facades\SwooleTable as Table;

class WebSocketRepository {

    const TABLE_NAME = 'onlineUsers';

    public function addToUsersTable($user, $fd)
    {
        $table = Table::get(self::TABLE_NAME);

        return $table->set($user->user_id, [
                'fd' => $fd,
                'role_id' => $user->role_id,
        ]);
    }

    public function removeFromUsersTable($userId)
    {
        if (!$userId) return null;
        
        $table = Table::get(self::TABLE_NAME);

        return $table->del($userId);
    }

    public function getUserFromTable($userId)
    {
        if (!$userId) return null;

        $table = Table::get(self::TABLE_NAME);

        return $table->get($userId);
    }

    public function userExistsTable($userId)
    {
        if (!$userId) return false;

        $table = Table::get(self::TABLE_NAME);

        return $table->exist($userId);
    }

    public function countUsersTable()
    {
        $table = Table::get(self::TABLE_NAME);

        return $table->count();
    }
}