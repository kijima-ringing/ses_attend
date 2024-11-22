<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('break_times', function (Blueprint $table) {
            // すべてのカラムをnullableに変更
            $table->time('break_time_from')->nullable()->change();
            $table->time('break_time_to')->nullable()->change();
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->unsignedBigInteger('updated_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('break_times', function (Blueprint $table) {
            // nullable制約を元に戻す
            $table->time('break_time_from')->nullable(false)->change();
            $table->time('break_time_to')->nullable(false)->change();
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->unsignedBigInteger('updated_by')->nullable(false)->change();
        });
    }
}

