<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('groups')->truncate();
        DB::table('groups_users')->truncate();

        DB::table('groups')->insert([
            [
                'group_name'    => 'For Writers',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'For Admins',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'For Subscribers',
                'fk_created_by' => 3,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Main Group',
                'fk_created_by' => 3,
                'created_at'    => Carbon::now()
            ]

        ]);

        DB::table('groups_users')->insert([
            [
                'fk_group_id'    => 1,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 1,
                'fk_user_id'     => 2
            ],
            [
                'fk_group_id'    => 1,
                'fk_user_id'     => 3
            ],
            [
                'fk_group_id'    => 1,
                'fk_user_id'     => 4
            ],
            [
                'fk_group_id'    => 2,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 2,
                'fk_user_id'     => 3
            ]
        ]);
    }
}
