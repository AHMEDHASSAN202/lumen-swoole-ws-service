<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class GroupsController extends Controller
{
    /**
     * Get All Groups
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllGroups()
    {
        $groups = app(\App\Repositories\ChatRepository::class)->getAllGroups();

        return response()->json(compact('groups'));
    }

    /**
     * Create New Group
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeGroup(Request $request)
    {
        $status = app(\App\Repositories\ChatRepository::class)->storeGroup($request);

        return response()->json(['status' => $status ? 'OK' : 'ERROR'], $status ? 201 : 400);
    }

    /**
     * Update Group
     *
     * @param $group_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGroup($group_id, Request $request)
    {
        $status = app(\App\Repositories\ChatRepository::class)->updateGroup($group_id, $request);

        return response()->json(['status' => $status ? 'OK' : 'ERROR'], $status ? 200 : 400);
    }

    /**
     * Delete Group
     *
     * @param Request $request
     * @param $group_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteGroup(Request $request, $group_id)
    {
        $status = app(\App\Repositories\ChatRepository::class)->deleteGroup($request, $group_id);

        return response()->json(['status' => $status ? 'OK' : 'ERROR'], $status ? 200 : 400);
    }

    /**
     * Get All members
     * - Group members (when group_id is exists)
     * - All members (when group_id == 0)
     *
     * @param Request $request
     * @param $group_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupMembers(Request $request, $group_id)
    {
        $members = app(\App\Repositories\ChatRepository::class)->getMembersOfGroup($request, $group_id);

        return response()->json(compact('members'));
    }
}