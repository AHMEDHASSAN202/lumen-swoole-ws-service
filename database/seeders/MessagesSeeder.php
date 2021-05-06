<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'how are you ?',
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => 1,
                'fk_group_id' => null,
                'message_content' => 'fine thnx',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => null,
                'fk_group_id' => 1,
                'message_content' => 'blaaaaaa message in group',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'hi!',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'how are you ?',
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => 1,
                'fk_group_id' => null,
                'message_content' => 'fine thnx',
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => null,
                'fk_group_id' => 2,
                'message_content' => 'blaaaaaa',
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => null,
                'fk_group_id' => 2,
                'message_content' => 'blaaaaaa 2',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'hi!',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'how are you ?',
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => 1,
                'fk_group_id' => null,
                'message_content' => 'fine thnx',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => null,
                'fk_group_id' => 2,
                'message_content' => 'blaaaaaa',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'hi!',
            ],
            [
                'fk_sender_id' => 1,
                'fk_receiver_id' => 2,
                'fk_group_id' => null,
                'message_content' => 'how are you ?',
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => 1,
                'fk_group_id' => null,
                'message_content' => 'fine thnx',
            ],
            [
                'fk_sender_id' => 2,
                'fk_receiver_id' => null,
                'fk_group_id' => 2,
                'message_content' => 'blaaaaaa',
            ],
        ]);

        DB::table('last_read_messages')->insert([
            [
                'fk_user_id'    => 1,
                'fk_message_id'    => 7,
                'fk_sender_id'    => 2,
                'fk_group_id'    => null,
            ],
            [
                'fk_user_id'    => 1,
                'fk_message_id'    => 8,
                'fk_sender_id'    => null,
                'fk_group_id'    => 2,
            ],
        ]);
    }
}
