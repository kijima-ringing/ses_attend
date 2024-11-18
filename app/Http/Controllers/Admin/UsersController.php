<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\DepartmentMember;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request) {
        $user = new User;
        $user_list = $user->getViewListForIndex();

        $department = new Department;
        $department_select_list = $department->getDepartmentSelectList();

        return view('admin.users.index')->with([
            'user_list' => $user_list,
            'department_select_list' => $department_select_list,
        ]);
    }

    public function update(UserRequest $request) {
        try {
            DB::transaction(function () use ($request) {
                $res = User::createOrUpdate($request->merge(['admin_flag' => $request->has('admin_flag') ? 1 : 0]));

                DepartmentMember::where('user_id', $res->id)->delete();

                $userService = new UserService();
                $userService->associateDepartment($request, $res);
            });

            session()->flash('flash_message', '社員リストを更新しました。');
        } catch (\Exception $e) {
            session()->flash('error_message', '社員リストの更新に失敗しました');
        }

        return redirect(route('admin.users.index'));
    }

    public function destroy(Request $request) {
        try {
            DB::transaction(function () use ($request) {
                DepartmentMember::where('user_id', $request->id)->delete();
                User::destroy($request->id);
            });
            session()->flash('flash_message', '削除しました。');
        } catch (\Exception $e) {
            session()->flash('error_message', '削除に失敗しました');
        }

        return redirect(route('admin.users.index'));
    }

    public function ajaxGetUserInfo(Request $request) {
        $query = $request->query();
        $res = User::find($query['user_id'])->toArray(); // admin_flagが正しく含まれているか確認
        $res['departments'] = DepartmentMember::getDepartments($query['user_id']);
        \Log::info('User Data:', $res); // デバッグ用ログ出力
        return $res;
    }

}
