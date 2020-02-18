<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentCreateValidationRequest;
use App\Http\Requests\DepartmentUpdateValidationRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\DepartmentMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index() {
        $departments = Department::all();

        return view('admin.department.index')->with([
            'departments' => $departments,
        ]);
    }

    public function store(DepartmentCreateValidationRequest $request) {
        $params = $request->validated();

        DB::transaction(function () use ($request, $params) {
            $department = Department::create($params);
        });

        session()->flash('flash_message', '登録が成功しました');

        return redirect(route('admin.department.index'));
    }

    public function update(DepartmentUpdateValidationRequest $request, $id) {
        $params = $request->validated();

        DB::transaction(function () use ($request, $params, $id) {
            $department = Department::findOrFail($id)->fill($params);

            $department->saveOrFail();
        });

        session()->flash('flash_message', '更新が成功しました');

        return redirect(route('admin.department.index'));
    }

    public function destroy($id) {

        DB::transaction(function () use ($id) {

            $department = Department::findOrFail($id);

            DepartmentMember::where('department_id', '=', $id)->delete();

            $department->delete();
        });

        session()->flash('flash_message', '削除が成功しました');

        return redirect(route('admin.department.index'));
    }

    public function ajaxGetDepartmentInfo(Request $request) {
        $department = Department::findOrNew($request->id);

        return DepartmentResource::make($department);
    }
}
