<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('admin_id')->notNullable();
            $table->integer('user_id')->notNullable();
            $table->datetime('created_at')->notNullable();
            $table->unsignedBigInteger('created_by')->notNullable();
            $table->datetime('updated_at')->notNullable();
            $table->unsignedBigInteger('updated_by')->notNullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_rooms');
    }
}