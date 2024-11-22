<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAttendanceDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_daily', function (Blueprint $table) {
            $table->time('working_time')->nullable()->change();
            $table->time('leave_time')->nullable()->change();
            $table->text('memo')->nullable()->change();
            $table->time('scheduled_working_hours')->nullable()->change();
            $table->time('overtime_hours')->nullable()->change();
            $table->time('working_hours')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_daily', function (Blueprint $table) {
            $table->time('working_time')->nullable(false)->change();
            $table->time('leave_time')->nullable(false)->change();
            $table->text('memo')->nullable(false)->change();
            $table->time('scheduled_working_hours')->nullable(false)->change();
            $table->time('overtime_hours')->nullable(false)->change();
            $table->time('working_hours')->nullable(false)->change();
        });
    }
}
