<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $this->call(GroupsSeeder::class);
        $this->call(MessagesSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}