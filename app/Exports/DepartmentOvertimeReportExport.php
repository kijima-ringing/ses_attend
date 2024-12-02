<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\AttendanceHeader;

class DepartmentOvertimeReportExport implements FromCollection
{
    protected $thresholdOvertimeHours;
    protected $targetMonth;

    /**
     * コンストラクタ
     */
    public function __construct($thresholdOvertimeHours, $targetMonth)
    {
        $this->thresholdOvertimeHours = $thresholdOvertimeHours;
        $this->targetMonth = $targetMonth;
    }

    /**
     * エクセルに出力するデータコレクションを作成
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $departments = Department::all();
        $reportData = collect([
            ['基準残業時間', $this->thresholdOvertimeHours], // 基準残業時間
            ['抽出対象年月', $this->targetMonth],           // 抽出対象年月
            [],                                             // 空行
            ['部門名', 'メンバー数', '基準超過人数'],         // データヘッダー行
        ]);

        foreach ($departments as $department) {
            // 部門メンバーを取得
            $memberIds = DepartmentMember::where('department_id', $department->id)
                ->pluck('user_id');

            // メンバーの勤怠データを取得 (抽出月に該当するデータのみ)
            $attendanceHeaders = AttendanceHeader::whereIn('user_id', $memberIds)
                ->where('year_month', "{$this->targetMonth}-01")
                ->get();

            // 基準超過人数をカウント
            $exceededCount = $attendanceHeaders->filter(function ($attendanceHeader) {
                $overtimeHours = $this->convertTimeToHours($attendanceHeader->overtime_hours);
                return $overtimeHours > $this->thresholdOvertimeHours;
            })->count();

            $reportData->push([
                $department->name,          // 部門名
                $memberIds->count(),        // 部門のメンバー数
                $exceededCount,             // 基準超過人数
            ]);
        }

        return $reportData;
    }

    /**
     * 時間文字列(H:i:s)を小数時間に変換
     * @param string $time
     * @return float
     */
    private function convertTimeToHours($time)
    {
        list($hours, $minutes, $seconds) = explode(':', $time);
        return $hours + ($minutes / 60) + ($seconds / 3600);
    }
}
