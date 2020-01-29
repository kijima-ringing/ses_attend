<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceDailyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'attendance_header_id' => $this->attendance_header_id,
            'work_date' => $this->work_date,
            'attendance_class' => $this->attendance_class,
            'working_time' => $this->working_time,
            'leave_time' => $this->leave_time,
            'break_time_from' => $this->break_time_from,
            'break_time_to' => $this->break_time_to,
            'memo' => $this->memo,
            'scheduled_working_hours' => $this->scheduled_working_hours,
            'overtime_hours' => $this->overtime_hours,
            'working_hours' => $this->working_hours,
        ];

    }
}
