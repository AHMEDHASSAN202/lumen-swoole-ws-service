<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('messages')->truncate();

        DB::table('messages')->insert([
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'hi!',
                'created_at'    => Carbon::now()
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'how are you ?',
                'created_at'    => Carbon::now()
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => 1,
                'fk_group_id' => null,
                'message_content' => 'fine thnx',
                'created_at'    => Carbon::now()
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => 1,
                'message_content' => 'blaaaaaa',
                'created_at'    => Carbon::now()
            ],
        ]);
    }
}
