<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->foreignId('fk_sender_id');
            $table->foreignId('fk_receiver_id')->nullable();
            $table->foreignId('fk_group_id')->nullable();
            $table->foreignId('fk_file_id')->nullable();
            $table->string('message_type')->default('text');
            $table->text('message_content');
            $table->timestamp('created_at')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->foreign('fk_sender_id')->on('users')->references('user_id')->cascadeOnDelete();
            $table->foreign('fk_receiver_id')->on('users')->references('user_id')->cascadeOnDelete();
            $table->foreign('fk_group_id')->on('groups')->references('group_id')->cascadeOnDelete();
            $table->foreign('fk_file_id')->on('messages_files')->references('file_id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
