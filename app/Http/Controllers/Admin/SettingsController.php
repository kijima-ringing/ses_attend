<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * 設定編集画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('admin.settings.edit')->with([
            'company' => Company::where('id', 1)->first(), // 固定IDで1を抽出
            'time_fraction_list' => Company::TIME_FRACTION_LIST,
        ]);
    }

    /**
     * 設定の更新処理
     *
     * @param  \App\Http\Requests\SettingRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SettingRequest $request)
    {
        // バリデーション済みデータを取得
        $validated = $request->validated();

        // トランザクションでデータを更新
        DB::transaction(function () use ($validated) {
            $company = Company::find(1); // 固定IDを使用
            $company->update($validated);
        });

        return redirect()->route('admin.settings.edit')->with('success', '設定を更新しました。');
    }
}
