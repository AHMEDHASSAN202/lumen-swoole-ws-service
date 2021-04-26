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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        DB::table('groups')->truncate();
        DB::table('groups_users')->truncate();

        DB::table('groups')->insert([
            [
                'group_name'    => 'First Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'First Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'First Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'First Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'First Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],[
                'group_name'    => 'First Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 2,
                'created_at'    => Carbon::now()
            ]
            ,[
                'group_name'    => 'First Group',
                'fk_created_by' => 2,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 1,
                'created_at'    => Carbon::now()
            ]
            ,[
                'group_name'    => 'First Group',
                'fk_created_by' => 2,
                'created_at'    => Carbon::now()
            ],
            [
                'group_name'    => 'Second Group',
                'fk_created_by' => 1,
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
                'fk_group_id'    => 2,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 3,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 3,
                'fk_user_id'     => 2
            ],
            [
                'fk_group_id'    => 4,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 5,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 4,
                'fk_user_id'     => 2
            ],
            [
                'fk_group_id'    => 4,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 6,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 7,
                'fk_user_id'     => 2
            ],
            [
                'fk_group_id'    => 8,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 9,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 10,
                'fk_user_id'     => 2
            ],
            [
                'fk_group_id'    => 11,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 12,
                'fk_user_id'     => 1
            ],
            [
                'fk_group_id'    => 13,
                'fk_user_id'     => 2
            ],
            [
                'fk_group_id'    => 14,
                'fk_user_id'     => 1
            ],
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
