<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastReadMessages extends Migration
{

    public function up()
    {
        Schema::create('last_read_messages', function (Blueprint $table) {
            $table->id('last_id');
            $table->foreignId('fk_user_id');
            $table->foreignId('fk_message_id');
            $table->foreignId('fk_sender_id')->nullable();
            $table->foreignId('fk_group_id')->nullable();

            $table->foreign('fk_user_id')->on('users')->references('user_id')->cascadeOnDelete();
            $table->foreign('fk_message_id')->on('messages')->references('message_id')->cascadeOnDelete();
            $table->foreign('fk_sender_id')->on('users')->references('user_id')->cascadeOnDelete();
            $table->foreign('fk_group_id')->on('groups')->references('group_id')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('last_read_messages');
    }
}