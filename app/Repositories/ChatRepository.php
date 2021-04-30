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
use Illuminate\Support\Facades\Cache;
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
    const FAKE_USERS_GROUPS_PER_REQUEST = 200;
    const MESSAGES_PER_REQUEST = 100;

    /**
     * Get Users And My Joined Groups
     *
     * @param Request $request
     * @return Collection
     */
    public function getUsersAndGroups(Request $request) : Collection
    {
        $languageId = $request->get('lang', self::DEFAULT_LANGUAGE_ID);
        $userId = $request->user()->user_id;
        $limit = self::FAKE_USERS_GROUPS_PER_REQUEST / 2;
        $offset = (((int)$request->get('page', 1)) - 1) * $limit;

        //get users, user role, last message between current user and him user
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

        //get my joined groups, last message
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
        //handle latest chats in users and groups
        $result = collect([...$users, ...$groups])->sortBy(function ($obj) {
            return $obj->created_at ? strtotime($obj->created_at) : strtotime('2010-01-01 00:00:00');
        }, SORT_REGULAR, 'DESC');

        return $result->values();
    }

    /**
     * Get messages
     * Private messages || Group messages
     * private by (user_id)
     * group by (group_id)
     *
     * @param Request $request
     * @return array
     */
    public function getMessages(Request $request) : array
    {
        $offset = (((int)$request->get('page_messages', 1)) - 1) * self::MESSAGES_PER_REQUEST;
        $userId = $request->get('user_id');
        $groupId = $request->get('group_id');
        $myId = $request->user()->user_id;

        if ($userId) {
            //get private messages
            return $this->getPrivateChatMessage($myId, $userId, $offset);
        }

        if ($groupId) {
            //get group messages
            return $this->getGroupChatMessages($myId, $groupId, $offset);
        }

        return [];
    }

    /**
     * Get Private Message Between Users
     *
     * @param $myId
     * @param $userId
     * @param $offset
     * @return array
     */
    public function getPrivateChatMessage($myId, $userId, $offset)
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
                    ->offset($offset)
                    ->limit(self::MESSAGES_PER_REQUEST)
                    ->get()
                    ->toArray();
    }

    /**
     * Get Group Chat Messages
     *
     * @param $myId
     * @param $groupId
     * @param $offset
     * @return array
     */
    public function getGroupChatMessages($myId, $groupId, $offset)
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
                    ->offset($offset)
                    ->limit(self::MESSAGES_PER_REQUEST)
                    ->get()
                    ->toArray();
    }

    /**
     * Get All Groups
     *
     * @return Collection
     */
    public function getAllGroups()
    {
        return Cache::rememberForever(self::GROUPS_TABLE, function () {
                return DB::table(self::GROUPS_TABLE)
                    ->select('group_id', 'group_name', 'fk_created_by', 'user_name as created_by_name', DB::raw('COUNT(fk_user_id) as count_users'))
                    ->leftJoin(self::USERS_TABLE, 'fk_created_by', '=', 'user_id')
                    ->leftJoin(self::GROUPS_USERS_TABLE, 'group_id', '=', 'fk_group_id')
                    ->groupBy('group_id')
                    ->latest('group_id')
                    ->get();
        });
    }

    /**
     * Create New Group
     *
     * @param Request $request
     * @return bool
     */
    public function storeGroup(Request $request)
    {
        $group_name = $request->input('group_name');
        $members = $request->input('members', []);
        $me = $request->user();
        array_push($members, $me->user_id);

        if (!$group_name) {
            return false;
        }

        try {
            $group_id = DB::table(self::GROUPS_TABLE)->insertGetId([
                'group_name'    => $group_name,
                'fk_created_by' => $me->user_id,
                'group_token'   => random_bytes(50),
                'created_at'    => Carbon::now()
            ]);

            if (!$group_id) {
                return false;
            }

            $this->joinUsersToGroup($group_id, $members);

            $this->clearGroupsCache();

            return true;
        }catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Update Group
     *
     * @param $group_id
     * @param Request $request
     * @return bool
     */
    public function updateGroup($group_id, Request $request)
    {
        $group_name = $request->input('group_name');
        $members = $request->input('members', []);
        $me = $request->user();
        array_push($members, $me->user_id);

        if (!$this->checkGroupExists($group_id, $me->user_id)) {
            return false;
        }

        try {
            if ($group_name) {
                DB::table(self::GROUPS_TABLE)
                    ->where('group_id', $group_id)
                    ->where('fk_created_by', $me->user_id)
                    ->update(['group_name' => $group_name]);
            }

            $this->resetJoinedUsersGroups($group_id, $members);

            $this->clearGroupsCache();

            return true;
        }catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Delete Group
     *
     * @param Request $request
     * @param $groupId
     * @return int
     */
    public function deleteGroup(Request $request, $groupId)
    {
        $me = $request->user();

        $this->clearGroupsCache();

        return DB::table(self::GROUPS_TABLE)
                ->where('group_id', $groupId)
                ->where('fk_created_by', $me->user_id)
                ->delete();
    }

    /**
     * Joined Members To Groups
     *
     * @param $groupId
     * @param $membersIds
     */
    private function joinUsersToGroup($groupId, $membersIds)
    {
        $joinedArray = [];

        foreach ($membersIds as $userId) {
            $joinedArray[] = ['fk_group_id' => $groupId, 'fk_user_id' => $userId];
        }

        DB::table(self::GROUPS_USERS_TABLE)->insert($joinedArray);
    }

    /**
     * Delete Old Joined Members When Joined New Users
     * And Joined All New Members
     *
     * @param $groupId
     * @param $membersIds
     */
    private function resetJoinedUsersGroups($groupId, $membersIds)
    {
        DB::table(self::GROUPS_USERS_TABLE)->where('fk_group_id', $groupId)->delete();

        $this->joinUsersToGroup($groupId, $membersIds);
    }

    /**
     * Get All Members || Group Only Members
     * When $groupId null or 0 get all members
     * When $groupId exists get only group members
     *
     * @param Request $request
     * @param null $groupId
     * @return Collection
     */
    public function getMembersOfGroup(Request $request, $groupId=null)
    {
        $me = $request->user();

        $query = DB::table(self::USERS_TABLE)->select('user_id', 'user_name', 'user_avatar');
        
        if ($groupId) {
            $query->join(self::GROUPS_USERS_TABLE, function ($q) use ($groupId) {
                $q->on('fk_user_id', '=', 'user_id')->where('fk_group_id', '=', $groupId);
            });
        }
        
        return $query->where('user_id', '!=', $me->user_id)->latest('user_id')->get();
    }

    /**
     * Check IF Group Exists
     *
     * @param $group_id
     * @param $owner_id
     * @return bool
     */
    private function checkGroupExists($group_id, $owner_id): bool
    {
        $groups = collect(Cache::get(self::GROUPS_TABLE));

        return (bool)$groups->where('group_id', $group_id)->where('fk_created_by', $owner_id)->count();
    }

    /**
     * Check IF Member is Exists in Group
     *
     * @param $group_id
     * @param $user_id
     * @return bool
     */
    private function checkMemberExistsInGroup($group_id, $user_id): bool
    {
        return DB::table(self::GROUPS_USERS_TABLE)->where('fk_group_id', $group_id)->where('fk_user_id', $user_id)->exists();
    }

    /**
     * Insert Message attachment Files in DB
     *
     * @param $data
     * @return int
     */
    public function saveMessageFiles($data)
    {
        return DB::table(self::MESSAGES_FILES_TABLE)->insertGetId($data);
    }

    /**
     * Store New Message
     *
     * @param $message
     * @return int
     */
    public function addMessage($message)
    {
        $message['created_at'] = Carbon::now();
        return DB::table(self::MESSAGES_TABLE)->insertGetId($message);
    }

    /**
     * Get Message By Id
     * - used it when will retrieve last insert message from last insert id
     *
     * @param $messageId
     * @param false $containFile
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getMessage($messageId, $containFile=false)
    {
        $query = DB::table(self::MESSAGES_TABLE)
                    ->select(self::MESSAGES_TABLE.'.*', 'user_id', 'user_name', 'user_avatar')
                    ->join(self::USERS_TABLE, 'user_id', '=', 'fk_sender_id')
                    ->where('message_id', $messageId);

        if ($containFile) {
            $query->addSelect('original_name', 'file_path');
            $query->leftJoin(self::MESSAGES_FILES_TABLE, function ($join) {
                $join->on('fk_file_id', '=', 'file_id')->where('fk_file_id', '!=', null);
            });
        }

        return $query->first();
    }

    /**
     * Clear Groups From Cahce
     *
     */
    private function clearGroupsCache()
    {
        Cache::forget(self::GROUPS_TABLE);
    }

    /**
     * Get User Groups
     *
     * @param $userId
     * @return array
     */
    public function getUserGroups($userId)
    {
        return DB::table(self::GROUPS_USERS_TABLE)->select('fk_group_id')->where('fk_user_id', $userId)->pluck('fk_group_id')->toArray();
    }
}
