<?php
/**
 * Created by PhpStorm.
 * User: AQSSA
 */

namespace App\Repositories;

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

    //Don't Forgot Ahmed Cached This Query
    public function getUsersAndGroups($request) : Collection
    {
        $languageId = $request->get('lang', self::DEFAULT_LANGUAGE_ID);
        $userId = $request->user()->user_id;

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
                    ->get();

        $groups = DB::table(self::GROUPS_TABLE)
                    ->select('group_id', 'group_name', DB::raw("'group' AS model_type"), self::MESSAGES_TABLE.'.*')
                    ->join(self::GROUPS_USERS_TABLE, function ($join) use ($userId) {
                        $join->on('group_id', '=', 'fk_group_id')
                             ->where('fk_user_id', '=', $userId);
                    })
                    ->leftJoin(self::MESSAGES_TABLE, function ($join) use ($userId) {
                        $lastMessageQuery = DB::raw('(SELECT message_id FROM ' . self::MESSAGES_TABLE . ' WHERE fk_group_id=group_id ORDER BY (message_id) DESC LIMIT 1)');
                        $join->on('message_id', '=', $lastMessageQuery);
                    })
                    ->get();

        /** @noinspection PhpLanguageLevelInspection */
        $result = collect([...$users, ...$groups]);

        return $result;
    }
}
