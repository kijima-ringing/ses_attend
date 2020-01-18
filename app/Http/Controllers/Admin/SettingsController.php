<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit() {

        return view('admin.settings.edit')->with([
            'company' => Company::where('id', 1)->first(), // 抽出①
            'time_fraction_list' => Company::TIME_FRACTION_LIST,
        ]);
    }

    public function update(SettingRequest $request) {
        // バリデーションはSettingRequest内で実施済み
    $test = 1;

        try {
            DB::transaction(function () use ($request) {
                // 更新①を実行
                Company::updateOrCreate(['id' => 1], $request->all());
            });
            session()->flash('flash_message', '設定を更新しました。');
        } catch (\Exception $e) {
            session()->flash('error_message', '設定の更新に失敗しました');
        }

        return redirect(route('admin.settings.edit'));
    }
}
