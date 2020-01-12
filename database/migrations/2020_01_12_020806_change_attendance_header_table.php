<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAttendanceHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_header', function (Blueprint $table) {
            $table->unsignedSmallInteger('working_days')->nullable()->change();
            $table->time('overtime_hours')->nullable()->change();
            $table->time('scheduled_working_hours')->nullable()->change();
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
        Schema::table('attendance_header', function (Blueprint $table) {
            $table->unsignedSmallInteger('working_days')->nullable(false)->change();
            $table->time('overtime_hours')->nullable(false)->change();
            $table->time('scheduled_working_hours')->nullable(false)->change();
            $table->time('working_hours')->nullable(false)->change();
        });
    }
}
