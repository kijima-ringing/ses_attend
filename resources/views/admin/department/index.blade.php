@extends('layouts.app')

@section('addCss')
@endsection

@section('content')
    <div class="container department-info-url" id="department-index" data-url="{{ route('admin.department.ajax_get_department_info') }}">

        <div class="row pb-5">
            <div class="col-12">
                <div class="create-route float-right">
                    <button class="btn btn-primary px-5 add-dialog" id="add-department" data-action="{{ route('admin.department.store') }}">部門追加</button>
                </div>
                <div class="create-route">
                    <form action="{{ route('admin.department.overtime_report.export') }}" method="GET" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="thresholdOvertimeHours" class="mr-2">基準残業時間</label>
                            <input type="number" name="thresholdOvertimeHours" id="thresholdOvertimeHours" class="form-control" value="40" required>
                        </div>
                        <div class="form-group mr-2">
                            <label for="targetMonth" class="mr-2">年月</label>
                            <input type="month" name="targetMonth" id="targetMonth" class="form-control" value="{{ now()->format('Y-m') }}" required>
                        </div>
                        <button type="submit" class="btn btn-success px-5">Excelに抽出</button>
                    </form>
                </div>
            </div>
        </div>

        <table class="table table-bordered" id="edit-department" data-action="{{ route('admin.department.update', ['department' => 'department_id']) }}">
            <thead class="bg-info">
            <tr>
                <th>部門名</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($departments AS $department)
                <tr class="bg-white">
                    <th class="text-center" ><span class="edit-department click-text" data-id="{{ $department->id }}">{{ $department->name }}</span></th>

                    <td class="text-center">
                        <a href="{{ route('admin.department.delete', ['department' => $department->id]) }}" class="btn btn-danger">削除</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="modal fade" tabindex="-1" id="form-modal" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div><!-- /.modal-header -->
                <form method="GET" id="modal-form">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                    <div class="modal-body">
                        <div class="alert-danger d-none col-12">
                            <ul>

                            </ul>
                        </div>
                        <input type="hidden" name="id" value="" id="id">

                        <div class="form-group row">
                            <label for="name" class="col-md-4 control-label col-form-label text-right">
                                部門名
                            </label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" id="name">
                            </div>
                        </div>

                    </div><!-- /.modal-body -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="department-submit">保存</button>
                    </div><!-- /.modal-footer -->
            </div><!-- /.modal-content -->
            </form>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@endsection
@section('addJs')
    <script src="{{ asset('js/departmentForm.js') }}"></script>
@endsection
