<?php
namespace App\Services;

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Carbon;
use App\Models\AttendanceHeader;
use App\Services\GetDateService;
use Illuminate\Support\Facades\DB;

class UserService
{
    // ユーザ更新時に部門情報と関連付ける
    public function associateDepartment($request, $res) {
        $department_member_data = [];
        foreach((array)$request->department_ids AS $department_id) {
            $department_member_data[] = [
                'user_id' => $res->id,
                'department_id' => $department_id,
            ];
        }

        if (!empty($department_member_data)) {
            DB::table('department_members')->insert($department_member_data);
        }
    }
}
