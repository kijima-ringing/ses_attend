<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceHeader;
use App\Services\AttendanceService;

class SettingsController extends Controller
{
    /**
     * 設定編集画面を表示するメソッド。
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // ビューに設定データを渡す
        return view('admin.settings.edit')->with([
            // 固定IDで会社情報を取得（ID=1を想定）
            'company' => Company::where('id', 1)->first(),
            // 時間の丸めオプションのリストを取得
            'time_fraction_list' => Company::TIME_FRACTION_LIST,
        ]);
    }

    /**
     * 設定の更新処理を行うメソッド。
     *
     * @param  \App\Http\Requests\SettingRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SettingRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            // 固定ID（1）の会社情報を取得
            $company = Company::find(1);

            // バリデーション済みデータで会社情報を更新
            $company->update($validated);

            // 勤怠ヘッダーの再計算を実行
            $this->recalculateAttendanceHeader();
        });

        return redirect()->route('admin.settings.edit')->with('success', '設定を更新し、勤怠データを再計算しました。');
    }

    /**
     * 勤怠ヘッダー（attendance_header）の再計算処理
     */
    protected function recalculateAttendanceHeader()
    {
        // 全ての勤怠ヘッダーデータを取得
        $attendanceHeaders = AttendanceHeader::all();

        foreach ($attendanceHeaders as $attendanceHeader) {
            // 月次データの再計算
            $attendanceService = new AttendanceService();
            $updateParams = $attendanceService->getUpdateMonthParams($attendanceHeader->id);

            // 再計算されたデータで更新
            $attendanceHeader->fill($updateParams)->save();
        }
    }
}
