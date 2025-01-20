<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('chat_room_id')->notNullable();
            $table->integer('user_id')->notNullable();
            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
            $table->text('message')->notNullable();
            $table->unsignedTinyInteger('read_flag')->default(0)->notNullable();
            $table->datetime('created_at')->notNullable();
            $table->unsignedBigInteger('created_by')->notNullable();
            $table->datetime('updated_at')->notNullable();
            $table->unsignedBigInteger('updated_by')->notNullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}