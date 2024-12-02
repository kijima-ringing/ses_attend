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

    public function update(UserRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // ログイン中のユーザーID
                $currentUserId = Auth::id();

                // 更新対象ユーザー
                $existingUser = User::where('email', $request->email)->first();

                if (!$existingUser || $existingUser->id == $request->id) {
                    // 管理者権限の変更を禁止する条件を追加
                    $adminFlag = $request->has('admin_flag') ? 1 : 0;

                    if ($request->id == $currentUserId) {
                        // ログイン中のユーザーの場合、admin_flag を変更させない
                        $adminFlag = $existingUser ? $existingUser->admin_flag : 1;
                    }

                    // ユーザーを作成または更新
                    $res = User::createOrUpdate($request->merge(['admin_flag' => $adminFlag]));

                    // 部門関連情報を更新
                    DepartmentMember::where('user_id', $res->id)->delete();
                    $userService = new UserService();
                    $userService->associateDepartment($request, $res);
                } else {
                    throw new \Exception('このメールアドレスはすでに使用されています');
                }
            });

            session()->flash('flash_message', '社員リストを更新しました。');
        } catch (\Exception $e) {
            session()->flash('error_message', $e->getMessage());
        }

        return redirect(route('admin.users.index'));
    }

    public function destroy(Request $request) {
        try {
            DB::transaction(function () use ($request) {
                DepartmentMember::where('user_id', $request->id)->delete();
                User::where('id', $request->id)->forceDelete();
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

        return $res;
    }

}
