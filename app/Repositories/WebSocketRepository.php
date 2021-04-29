<?php 

namespace App\Repositories;


use SwooleTW\Http\Table\Facades\SwooleTable as Table;

class WebSocketRepository {

    const TABLE_NAME = 'onlineUsers';

    /**
     * Add Current User To Swoole Online Users Table
     *
     * @param $user
     * @param $fd
     * @return mixed
     */
    public function addToUsersTable($user, $fd)
    {
        $table = Table::get(self::TABLE_NAME);

        return $table->set($user->user_id, [
                'fd' => $fd,
                'role_id' => $user->role_id,
        ]);
    }

    /**
     * Remove Current User From Swoole Online Users Table
     *
     * @param $userId
     * @return null
     */
    public function removeFromUsersTable($userId)
    {
        if (!$userId) return null;
        
        $table = Table::get(self::TABLE_NAME);

        return $table->del($userId);
    }

    /**
     * Get Current User Of Swoole Online Users Table
     *
     * @param $userId
     * @return null
     */
    public function getUserFromTable($userId)
    {
        if (!$userId) return null;

        $table = Table::get(self::TABLE_NAME);

        return $table->get($userId);
    }

    /**
     * Check Current User Exists In Swoole Online Users Table
     *
     * @param $userId
     * @return false
     */
    public function userExistsTable($userId)
    {
        if (!$userId) return false;

        $table = Table::get(self::TABLE_NAME);

        return $table->exist($userId);
    }

    /**
     * Count Swoole Online Users Table
     *
     * @return mixed
     */
    public function countUsersTable()
    {
        $table = Table::get(self::TABLE_NAME);

        return $table->count();
    }

    /**
     * Get All Online Users From Swoole Table
     *
     * @return array
     */
    public function getOnlineUsers()
    {
        $onlineUsers = [];
        $table = Table::get(self::TABLE_NAME);
        foreach ($table as $userId => $userData) {
            $onlineUsers[] = (int)$userId;
        }
        return $onlineUsers;
    }
}