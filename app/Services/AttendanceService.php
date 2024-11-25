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
        $params['break_times'] ?? [],
        $company->base_time_to,
        $company->base_time_from
        );

        $workingHours = $this->getWorkingHours(
            $params,
            $company->base_time_to,
            $company->base_time_from
        );

        // 基準外の休憩時間を計算
        $breakTimeOutsideBase = 0;
        foreach ($params['break_times'] ?? [] as $breakTime) {
            $breakFrom = strtotime($breakTime['break_time_from']);
            $breakTo = strtotime($breakTime['break_time_to']);
            $outsideBase = ($breakTo - $breakFrom) - max(0, min($breakTo, strtotime($company->base_time_to)) - max($breakFrom, strtotime($company->base_time_from)));
            $breakTimeOutsideBase += $outsideBase;
        }

        $overtimeHours = $this->getOvertimeHours($workingHours, $scheduledWorkingHours, $breakTimeOutsideBase);

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

    public function getScheduledWorkingHours($time_to, $time_from, $breakTimes = [], $base_time_to = null, $base_time_from = null)
    {
        $from = strtotime($time_from);
        $to = strtotime($time_to);

        // 基準時間（所定内労働時間の範囲）を設定
        $baseFrom = strtotime($base_time_from ?? $time_from);
        $baseTo = strtotime($base_time_to ?? $time_to);

        $breakTimeInsideBase = 0; // 基準時間内の休憩時間
        $breakTimeOutsideBase = 0; // 基準時間外の休憩時間

        // 各休憩時間を基準時間内外で分類
        foreach ($breakTimes as $breakTime) {
            $breakFrom = strtotime($breakTime['break_time_from']);
            $breakTo = strtotime($breakTime['break_time_to']);

            // 基準時間内の重複部分を計算
            $insideBase = max(0, min($breakTo, $baseTo) - max($breakFrom, $baseFrom));
            $outsideBase = ($breakTo - $breakFrom) - $insideBase;

            $breakTimeInsideBase += $insideBase;
            $breakTimeOutsideBase += $outsideBase;
        }

        // 所定内労働時間 = 基準時間範囲内の実労働時間 - 基準内の休憩時間
        $scheduledWorkingHours = ($baseTo - $baseFrom) - $breakTimeInsideBase;

        return date(self::TIME_FORMAT, $scheduledWorkingHours);
    }

    public function getWorkingHours($params, $base_time_to = null, $base_time_from = null)
    {
        $workingTime = strtotime($params['working_time']);
        $leaveTime = strtotime($params['leave_time']);

        // 基準時間（所定内労働の範囲）
        $baseFrom = strtotime($base_time_from ?? $params['working_time']);
        $baseTo = strtotime($base_time_to ?? $params['leave_time']);

        $breakTimeInsideBase = 0; // 基準時間内の休憩時間
        $breakTimeOutsideBase = 0; // 基準時間外の休憩時間

        // 各休憩時間を基準時間内外で分類
        foreach ($params['break_times'] ?? [] as $breakTime) {
            $breakFrom = strtotime($breakTime['break_time_from']);
            $breakTo = strtotime($breakTime['break_time_to']);

            // 基準時間内の重複部分を計算
            $insideBase = max(0, min($breakTo, $baseTo) - max($breakFrom, $baseFrom));
            $outsideBase = ($breakTo - $breakFrom) - $insideBase;

            $breakTimeInsideBase += $insideBase;
            $breakTimeOutsideBase += $outsideBase;
        }

        // 実働時間 = 総労働時間（退勤 - 出勤） - 全休憩時間
        $actualWorkingHours = ($leaveTime - $workingTime) - ($breakTimeInsideBase + $breakTimeOutsideBase);

        return date(self::TIME_FORMAT, $actualWorkingHours);
    }

    public function getOvertimeHours($workingHours, $scheduledWorkingHours, $breakTimeOutsideBase)
    {
        // 残業時間 = 実働時間 - 所定内労働時間 - 基準外休憩時間
        $time = strtotime($workingHours) - strtotime($scheduledWorkingHours) - $breakTimeOutsideBase;
        if ($time < 0) {
            $overtimeHours = 0;
        } else {
            $overtimeHours = $time;
        }
        return date(self::TIME_FORMAT, $overtimeHours);
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