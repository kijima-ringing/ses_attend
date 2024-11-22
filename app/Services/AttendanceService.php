<?php

namespace App\Services;

use App\Models\AttendanceDaily;
use App\Models\BreakTime;
use App\Models\Company;
use App\Services\GetDateService;
use Illuminate\Support\Carbon;
use App\Models\AttendanceHeader;

class AttendanceService
{
    const TIME_FORMAT = 'H:i:s';

    public function getUpdateDailyParams($params)
    {
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

    public function setDailyParamsNormalWorking($params, $company)
    {
        $scheduledWorkingHours = $this->getScheduledWorkingHours(
            $company->base_time_to,
            $company->base_time_from,
            $params['break_times'] ?? []
            );

        $workingHours = $this->getWorkingHours($params);

        $overtimeHours = $this->getOvertimeHours($workingHours, $scheduledWorkingHours);

        return [
            'scheduled_working_hours' => $scheduledWorkingHours,
            'working_hours' => $workingHours,
            'overtime_hours' => $overtimeHours,
        ];
    }

    public function setDailyParamsPaidHolidays($params, $company)
    {
        $scheduledWorkingHours = $this->getScheduledWorkingHours($company->base_time_to, $company->base_time_from);

        $workingHours = $scheduledWorkingHours;

        $overtimeHours = date(self::TIME_FORMAT, 0);

        return [
            'scheduled_working_hours' => $scheduledWorkingHours,
            'working_hours' => $workingHours,
            'overtime_hours' => $overtimeHours,
        ];
    }

    public function setDailyParamsDefault()
    {
        $scheduledWorkingHours = date(self::TIME_FORMAT, 0);

        $workingHours = date(self::TIME_FORMAT, 0);

        $overtimeHours = date(self::TIME_FORMAT, 0);;

        return [
            'scheduled_working_hours' => $scheduledWorkingHours,
            'working_hours' => $workingHours,
            'overtime_hours' => $overtimeHours,
        ];
    }

    public function getScheduledWorkingHours($time_to, $time_from, $breakTimes = [])
    {
        $from = strtotime($time_from);
        $to = strtotime($time_to);

        // 休憩時間の合計を計算
        $totalBreakTime = 0;
        foreach ($breakTimes as $breakTime) {
            $break_time_from = strtotime($breakTime['break_time_from']);
            $break_time_to = strtotime($breakTime['break_time_to']);
            $totalBreakTime += ($break_time_to - $break_time_from);
        }

        // 所定内労働時間 = 終了時間 - 開始時間 - 休憩時間
        $dif = $to - $from - $totalBreakTime;

        return date(self::TIME_FORMAT, $dif);
    }

    public function getWorkingHours($params)
    {
        $working_time = strtotime($params['working_time']);
        $leave_time = strtotime($params['leave_time']);

        // 休憩時間の合計を計算
        $totalBreakTime = 0;
        foreach ($params['break_times'] ?? [] as $breakTime) {
            $break_time_from = strtotime($breakTime['break_time_from']);
            $break_time_to = strtotime($breakTime['break_time_to']);
            $totalBreakTime += ($break_time_to - $break_time_from);
        }

        // 実働時間 = 退勤時間 - 出勤時間 - 休憩時間合計
        $actualWorkingHours = $leave_time - $working_time - $totalBreakTime;

        return date(self::TIME_FORMAT, $actualWorkingHours);
    }

    public function getOvertimeHours($workingHours, $scheduledWorkingHours)
    {
        $time = strtotime($workingHours) - strtotime($scheduledWorkingHours);
        if ($time < 0) {
            $overtime_hours = 0;
        } else {
            $overtime_hours = $time;
        }
        return date(self::TIME_FORMAT, $overtime_hours);
    }

    public function getUpdateMonthParams($attendance_header_id)
    {
        $attendanceDailies = AttendanceDaily::where('attendance_header_id', '=', $attendance_header_id)->get();

        $working_days = 0;

        $scheduled_working_hours = 0;
        $overtime_hours = 0;
        $working_hours = 0;

        $getDataService = new GetDateService();

        foreach ($attendanceDailies as $attendance) {
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

    public function getUpdateMonthParamsWithGlobalRounding($attendance_header_id)
    {
        $attendanceDailies = AttendanceDaily::where('attendance_header_id', '=', $attendance_header_id)->get();

        $working_days = 0;
        $scheduled_working_hours = 0;
        $overtime_hours = 0;
        $working_hours = 0;

        foreach ($attendanceDailies as $attendance) {
            $working_days++;

            $scheduled_working_hours += (new GetDateService())->getRawHourInt($attendance->scheduled_working_hours);
            $overtime_hours += (new GetDateService())->getRawHourInt($attendance->overtime_hours);
            $working_hours += (new GetDateService())->getRawHourInt($attendance->working_hours);
        }

        // 合計値に対して丸め処理を適用
        $getDateService = new GetDateService();
        $company = Company::find(1); // 現在の会社設定を取得

        $scheduled_working_hours = $getDateService->applyRounding($scheduled_working_hours, $company);
        $overtime_hours = $getDateService->applyRounding($overtime_hours, $company);
        $working_hours = $getDateService->applyRounding($working_hours, $company);

        return [
            'working_days' => $working_days,
            'scheduled_working_hours' => $this->amountHourFormat($scheduled_working_hours),
            'overtime_hours' => $this->amountHourFormat($overtime_hours),
            'working_hours' => $this->amountHourFormat($working_hours),
        ];
    }

    public function amountHourFormat($time)
    {
        $hour = $time * 3600;
        return floor($hour / 3600) . gmdate(":i:s", $hour);
    }
}