<?php
namespace App\Services;

use App\Models\AttendanceDaily;
use App\Models\Company;
use App\Services\GetDateService;
use Illuminate\Support\Carbon;
use App\Models\AttendanceHeader;

class AttendanceService
{
    const TIME_FORMAT = 'H:i:s';

    public function getUpdateDailyParams($params) {
        $company = Company::company();

        if ($params['attendance_class'] == AttendanceDaily::NORMAL_WORKING) {
            $dailyParams = $this->setDailyParamsNormalWorking($params, $company);
        } else if ($params['attendance_class'] == AttendanceDaily::PAID_HOLIDAYS) {
            $dailyParams = $this->setDailyParamsPaidHolidays($params, $company);
        } else {
            $dailyParams = $this->setDailyParamsDefault();
        }

        $updateDailyParams = array_merge($params, $dailyParams);

        return $updateDailyParams;
    }

    public function setDailyParamsNormalWorking($params, $company) {

        $scheduledWorkingHours = $this->getScheduledWorkingHours($company->base_time_to, $company->base_time_from);

        $workingHours = $this->getworkingHours($params);

        $overtimeHours = $this->getOvertimeHours($workingHours, $scheduledWorkingHours);

        return [
            'scheduled_working_hours' => $scheduledWorkingHours,
            'working_hours' => $workingHours,
            'overtime_hours' => $overtimeHours,
        ];
    }

    public function setDailyParamsPaidHolidays($params, $company) {
        $scheduledWorkingHours = $this->getScheduledWorkingHours($company->base_time_to, $company->base_time_from);

        $workingHours = $scheduledWorkingHours;

        $overtimeHours = date(self::TIME_FORMAT, 0);

        return [
            'scheduled_working_hours' => $scheduledWorkingHours,
            'working_hours' => $workingHours,
            'overtime_hours' => $overtimeHours,
        ];
    }

    public function setDailyParamsDefault() {
        $scheduledWorkingHours = date(self::TIME_FORMAT, 0);

        $workingHours = date(self::TIME_FORMAT, 0);

        $overtimeHours = date(self::TIME_FORMAT, 0);;

        return [
            'scheduled_working_hours' => $scheduledWorkingHours,
            'working_hours' => $workingHours,
            'overtime_hours' => $overtimeHours,
        ];
    }

    public function getScheduledWorkingHours($time_to, $time_from) {
        $from = strtotime($time_from);
        $to = strtotime($time_to);

        // 休憩時間の1時間をマイナスする
        $dif = $to - $from - 3600;

        return date('H:i:s', $dif);
    }

    public function getworkingHours($params) {
        $working_time = strtotime($params['working_time']);
        $leave_time = strtotime($params['leave_time']);
        $break_time_from = strtotime($params['break_time_from']);
        $break_time_to = strtotime($params['break_time_to']);

        $scheduled_working_hours = $leave_time - $working_time - ($break_time_to - $break_time_from);

        return date(self::TIME_FORMAT, $scheduled_working_hours);

    }

    public function getOvertimeHours($workingHours, $scheduledWorkingHours) {
        $time = strtotime($workingHours) - strtotime($scheduledWorkingHours);
        if ($time < 0) {
            $overtime_hours = 0;
        } else {
            $overtime_hours = $time;
        }
        return date(self::TIME_FORMAT, $overtime_hours);
    }

    public function getUpdateMonthParams($attendance_header_id) {
        $attendanceDailys = AttendanceDaily::where('attendance_header_id', '=', $attendance_header_id)->get();

        $working_days = 0;

        $scheduled_working_hours = 0;
        $overtime_hours = 0;
        $working_hours = 0;

        $getDataService = new GetDateService();

        foreach ($attendanceDailys as $attendance) {
            $working_days++;

            $scheduled_working_hours = $scheduled_working_hours + $getDataService->getHourInt($attendance->scheduled_working_hours);

            $overtime_hours = $overtime_hours + $getDataService->getHourInt($attendance->overtime_hours);

            $working_hours = $working_hours + $getDataService->getHourInt($attendance->working_hours);

        }

        return [
            'working_days' => $working_days,
            'scheduled_working_hours' => $this->amountHourFormat($scheduled_working_hours),
            'overtime_hours' => $this->amountHourFormat($overtime_hours),
            'working_hours' => $this->amountHourFormat($working_hours),
        ];
    }

    public function amountHourFormat($time) {
        $hour = $time * 3600;
        return floor($hour / 3600) . gmdate(":i:s", $hour);
    }
}
