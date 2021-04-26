<?php
/**
 * Created by PhpStorm.
 * User: AQSSA
 */

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatRepository
{
    const USERS_TABLE = 'users';
    const MESSAGES_TABLE = 'messages';
    const MESSAGES_FILES_TABLE = 'messages_files';
    const GROUPS_TABLE = 'groups';
    const GROUPS_USERS_TABLE = 'groups_users';
    const ROLES_DESCRIPTIONS_TABLE = 'roles_description';
    const DEFAULT_LANGUAGE_ID = 1;
    const FAKE_USERS_GROUPS_PER_PAGE = 200;

    //Don't Forgot Ahmed Cached This Query
    public function getUsersAndGroups(Request $request) : Collection
    {
        $languageId = $request->get('lang', self::DEFAULT_LANGUAGE_ID);
        $userId = $request->user()->user_id;
        $limit = self::FAKE_USERS_GROUPS_PER_PAGE / 2;
        $offset = (((int)$request->get('page', 1)) - 1) * $limit;

        $users = DB::table(self::USERS_TABLE)
                    ->select(
                        'user_id', 'user_name', 'user_email', 'user_avatar', self::USERS_TABLE.'.fk_role_id',
                        'name', 'fk_language_id', DB::raw("'user' AS model_type"), self::MESSAGES_TABLE.'.*'
                    )
                    ->leftJoin(self::ROLES_DESCRIPTIONS_TABLE, function ($join) use ($languageId) {
                        $join->on(self::ROLES_DESCRIPTIONS_TABLE.'.fk_role_id', '=', self::USERS_TABLE.'.fk_role_id')
                             ->where('fk_language_id', $languageId);
                    })
                    ->leftJoin(self::MESSAGES_TABLE, function ($join) use ($userId) {
                        $lastMessageQuery = DB::raw('(SELECT message_id FROM ' . self::MESSAGES_TABLE . ' WHERE (fk_sender_id = user_id AND fk_receiver_id=' . $userId . ') OR (fk_sender_id='.$userId.' AND fk_receiver_id = user_id) ORDER BY (message_id) DESC LIMIT 1)');
                        $join->on('message_id', '=', $lastMessageQuery);
                    })
                    ->where('user_id', '!=', $userId)
                    ->offset($offset)
                    ->limit($limit)
                    ->get();

        $groups = DB::table(self::GROUPS_TABLE)
                    ->select('group_id', 'group_name', 'fk_created_by', 'user_name as created_by_name', DB::raw("'group' AS model_type"), self::MESSAGES_TABLE.'.*')
                    ->join(self::GROUPS_USERS_TABLE, function ($join) use ($userId) {
                        $join->on('group_id', '=', 'fk_group_id')->where('fk_user_id', '=', $userId);
                    })
                    ->leftJoin(self::USERS_TABLE, 'fk_created_by', '=', 'user_id')
                    ->leftJoin(self::MESSAGES_TABLE, function ($join) use ($userId) {
                        $lastMessageQuery = DB::raw('(SELECT message_id FROM ' . self::MESSAGES_TABLE . ' WHERE fk_group_id=group_id ORDER BY (message_id) DESC LIMIT 1)');
                        $join->on('message_id', '=', $lastMessageQuery);
                    })
                    ->offset($offset)
                    ->limit($limit)
                    ->get();

        /** @noinspection PhpLanguageLevelInspection */
        $result = collect([...$users, ...$groups])->sortBy(function ($obj) {
            return $obj->created_at ? strtotime($obj->created_at) : strtotime('2010-01-01 00:00:00');
        }, SORT_REGULAR, 'DESC');

        return $result->values();
    }

    public function getMessages(Request $request) : array
    {
        $userId = $request->get('user_id');
        $groupId = $request->get('group_id');
        $myId = $request->user()->user_id;

        if ($userId) {
            //get private messages
            return $this->getPrivateChatMessage($myId, $userId);
        }

        if ($groupId) {
            //get group messages
            return $this->getGroupChatMessages($myId, $groupId);
        }

        return [];
    }

    public function getPrivateChatMessage($myId, $userId)
    {
        return DB::table(self::MESSAGES_TABLE)
                    ->select(self::MESSAGES_TABLE.'.*', 'user_id', 'user_name', 'user_avatar', 'original_name', 'file_path')
                    ->leftJoin(self::MESSAGES_FILES_TABLE, function ($join) {
                        $join->on('fk_file_id', '=', 'file_id')->where('fk_file_id', '!=', null);
                    })
                    ->join(self::USERS_TABLE, 'user_id', '=', 'fk_sender_id')
                    ->where(function ($query) use ($myId, $userId) {
                        $query->where('fk_sender_id', $myId)->where('fk_receiver_id', $userId);
                    })->orWhere(function ($query) use ($myId, $userId) {
                        $query->where('fk_sender_id', $userId)->where('fk_receiver_id', $myId);
                    })
                    ->orderBy('message_id', 'ASC')
                    ->get()
                    ->toArray();
    }

    public function getGroupChatMessages($myId, $groupId)
    {
        return DB::table(self::MESSAGES_TABLE)
                    ->select(self::MESSAGES_TABLE.'.*', 'user_id', 'user_name', 'user_avatar', 'original_name', 'file_path')
                    ->leftJoin(self::MESSAGES_FILES_TABLE, function ($join) {
                        $join->on('fk_file_id', '=', 'file_id')->where('fk_file_id', '!=', null);
                    })
                    ->join(self::USERS_TABLE, 'user_id', '=', 'fk_sender_id')
                    ->join(self::GROUPS_USERS_TABLE, function ($join) use ($myId) {
                        $join->on(self::MESSAGES_TABLE.'.fk_group_id', '=', self::GROUPS_USERS_TABLE.'.fk_group_id')->where('fk_user_id', '=', $myId);
                    })
                    ->where(self::MESSAGES_TABLE.'.fk_group_id', $groupId)
                    ->orderBy('message_id', 'ASC')
                    ->get()
                    ->toArray();
    }
}
