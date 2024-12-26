<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaidLeaveRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('paid_leave_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('paid_leave_default_id');
            $table->foreign('paid_leave_default_id')->references('id')->on('paid_leave_defaults')->onDelete('cascade');
            $table->unsignedBigInteger('attendance_daily_id');
            $table->foreign('attendance_daily_id')->references('id')->on('attendance_daily')->onDelete('cascade');
            $table->unsignedBigInteger('break_time_id');
            $table->foreign('break_time_id')->references('id')->on('break_times')->onDelete('cascade');
            $table->unsignedTinyInteger('status');
            $table->text('request_reason');
            $table->text('return_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paid_leave_requests');
    }
} 