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
        // バリデーションはUserRequest内で実施済み

        try {
            DB::transaction(function () use ($request) {
                // 更新③を実行
                $res = User::createOrUpdate($request);

                // 更新②を実行
                DepartmentMember::where('user_id', $res->id)->delete();

                // 更新④を実行
                $userService = new userService();
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
                // 更新②を実行
                DepartmentMember::where('user_id', $request->id)->delete();

                // 更新①を実行
                DB::table('users')->where('id', $request->id)
                ->update([
                    'deleted_by' => Auth::user()->id,
                    'deleted_at' => \Carbon\Carbon::now(),
                ]);
            });
            session()->flash('flash_message', '削除しました。');
        } catch (\Exception $e) {
            session()->flash('error_message', '削除に失敗しました');
        }

        return redirect(route('admin.users.index'));
    }

    public function ajaxGetUserInfo(Request $request) {
        $user = new User;
        $query = $request->query();
        $res = $user->getUserInfo($query['user_id']);
        return $res;
    }
}
