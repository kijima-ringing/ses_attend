<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_daily', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_header_id');
            $table->foreign('attendance_header_id')->references('id')->on('attendance_header')->onDelete('cascade');
            $table->date('work_date');
            $table->unsignedTinyInteger('attendance_class');
            $table->time('working_time');
            $table->time('leave_time');
            $table->time('break_time_from');
            $table->time('break_time_to');
            $table->text('memo');
            $table->time('scheduled_working_hours');
            $table->time('overtime_hours');
            $table->time('working_hours');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_daily');
    }
}
