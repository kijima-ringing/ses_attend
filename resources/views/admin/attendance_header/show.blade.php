@extends('layouts.app')

@section('addCss')
@endsection

@section('content')
<div class="container company validation-url" id="attendance-info-url"
    data-url="{{ route('admin.attendance_header.ajax_get_attendance_info') }}"
    data-base_time_from="{{ $company->base_time_from }}" data-base_time_to="{{ $company->base_time_to }}">
    <div class="row pb-3">
        <form method="GET" action="{{ route('admin.attendance_header.index') }}">
            @csrf
            <input type="hidden" name="year_month" value="{{ $date }}">
            <button type="submit" class="d-none" id="year_month_submit"></button>
            <div class="col-12">
                <div class="back-index click-text">戻る</div>
            </div>
        </form>
    </div>
    <div class="row pb-3">
        <div class="col-2">
            <div data-action="{{ route('admin.attendance_header.show', ['user_id' => $attendance->user_id, 'year_month' => 'year_month']) }}"
                id="year_month_url">
                <input type="text" class="monthPick" id="year_month" name="year_month" value="{{ $date }}">
                <input type="submit" class="d-none" id="year_month_submit">
            </div>
        </div>

        <div class="col-1"></div>
        <!-- 月次勤怠を確定ボタン配置 -->
        <form method="POST" action="{{ route('admin.attendance_header.confirm', ['user_id' => $attendance->user_id, 'year_month' => $date]) }}">
            @csrf
            <button type="submit" class="btn btn-success">
                {{ $attendance->confirm_flag ? '確定を取り消す' : '勤怠を確定する' }}
            </button>
        </form>

        <div class="h2">
            {{ $attendance->user->last_name }}{{ $attendance->user->first_name }}
        </div>
    </div>

    <table class="table table-bordered">
        <thead class="bg-info">
            <tr>
                <th>勤務日数</th>
                <th>所定内労働時間</th>
                <th>残業時間</th>
                <th>総労働時間</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white">
                <th class="text-center">{{ AttendanceHelper::daysFormat($attendance->working_days) }}日</th>
                <th class="text-center">{{ AttendanceHelper::timeFormat($attendance->scheduled_working_hours) }}</th>
                <th class="text-center">{{ AttendanceHelper::timeFormat($attendance->overtime_hours) }}</th>
                <th class="text-center">{{ AttendanceHelper::timeFormat($attendance->working_hours) }}</th>
            </tr>
        </tbody>
    </table>
    <table class="table table-bordered">
        <thead class="bg-info">
            <tr>
                <th>日付</th>
                <th></th>
                <th>区分</th>
                <th>勤務</th>
                <th>休憩</th>
                <th>所定内労働</th>
                <th>残業</th>
                <th>総労働時間</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daysOfMonth as $day)
            <tr class="bg-white dateInfo">
                <th class="text-right dialog date_info work_date click-text"
                    data-date_info="{{ $date . '-' . $day['day'] . '(' . $day['dayOfWeek'] . ')' }}"
                    data-work_date="{{ $day['work_date'] }}">{{ $day['day'] }}日</th>
                <th class="text-center">{{ $day['dayOfWeek'] }}</th>
                @if (count($attendanceDaily) > 0)
                @if (isset($attendanceDaily[$day['work_date']]))
                <th class="text-center attendance_class memo id"
                    data-id="{{ $attendanceDaily[$day['work_date']]['id'] }}">
                    {{ AttendanceHelper::attendanceClass($attendanceDaily[$day['work_date']]['attendance_class']) }}
                </th>
                <th class="text-center working_time leave_time">
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['working_time']) }}
                    ~
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['leave_time']) }}
                </th>
                <th class="text-center break_time_from break_time_to">
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['break_time_from']) }}
                    ~
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['break_time_to']) }}
                </th>
                <th class="text-right scheduled_working_hours">
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['scheduled_working_hours']) }}
                </th>
                <th class="text-right overtime_hours">
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['overtime_hours']) }}
                </th>
                <th class="text-right working_hours">
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['working_hours']) }}
                </th>
                @else
                <th class="text-center"></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                @endif
                @else
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div><!-- /.modal-header -->
            <form method="GET" action="{{ route('admin.attendance_header.update') }}" id="modal-form">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="alert-danger d-none col-12">
                            <ul>

                            </ul>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
                    <input type="hidden" name="year_month" value="{{ $date }}">
                    <input type="hidden" name="work_date" value="" id="work_date">
                    <div class="form-group row">
                        <label for="attendance_class" class="col-md-4 col-form-label text-right">
                            区分
                        </label>
                        <div class="col-md-8">
                            <div class="form-inline">
                                <select name="attendance_class" class="form-control" id="attendance_class">
                                    <option value="0">通常勤務</option>
                                    <option value="1">有給休暇</option>
                                    <option value="2">欠勤</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="working_time" class="col-md-4 col-form-label text-right">
                            出勤
                        </label>
                        <div class="col-md-8">
                            <div class="form-inline">
                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                    <input id="working_time" size="8" type="time" name="working_time"
                                        class="form-control" value="{{ $company->base_time_from }}">
                                </div>
                                <div class="input-group mb-2 mr-sm-2  ml-sm-2 mb-sm-0">
                                    〜
                                </div>
                                <div class="input-group mb-2 ml-sm-2 mb-sm-0">
                                    <input id="leave_time" type="time" size="8" name="leave_time" class="form-control"
                                        value="{{ $company->base_time_to }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="break_time_from" class="col-md-4 col-form-label text-right">
                            休憩
                        </label>
                        <div class="col-md-8">
                            <div class="form-inline">
                                <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                    <input id="break_time_from" size="8" type="time" name="break_time_from"
                                        class="form-control" value="12:00">
                                </div>
                                <div class="input-group mb-2 mr-sm-2  ml-sm-2 mb-sm-0">
                                    〜
                                </div>
                                <div class="input-group mb-2 ml-sm-2 mb-sm-0">
                                    <input id="break_time_to" type="time" size="8" name="break_time_to"
                                        class="form-control" value="13:00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="memo" class="col-md-4 control-label col-form-label text-right">
                            メモ
                        </label>
                        <div class="col-md-8">
                            <textarea class="field-textarea" id="memo" class="form-control" name="memo"></textarea>
                        </div>
                    </div>

                </div><!-- /.modal-body -->
                <div class="modal-footer">
                    <a data-url="{{ route('admin.attendance_header.delete', ['user_id' => $attendance->user_id, 'year_month' => $date, 'work_date' => 'work_date']) }}"
                        class="btn btn-secondary" id="delete-url">未入力に戻す</a>
                    <button type="button" class="btn btn-primary" id="attendance_submit">変更を保存</button>
                </div><!-- /.modal-footer -->
        </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection
@section('addJs')
<script src="{{ asset('/js/attendanceForm.js') }}"></script>
@endsection