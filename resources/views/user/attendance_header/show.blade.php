@extends('layouts.app')

@section('addCss')
@endsection

@section('content')
<div class="container company validation-url" id="attendance-info-url"
    data-url="{{ route('user.attendance_header.ajax_get_attendance_info') }}"
    data-confirmed="{{ $attendance->confirm_flag }}"
    data-base_time_from="{{ $company->base_time_from }}"
    data-base_time_to="{{ $company->base_time_to }}">

    <meta name="base-lock-url" content="/user/attendance_daily/">
    <div class="row pb-3">
        <div class="col-2">
            <div data-action="{{ route('user.attendance_header.show', ['user_id' => $attendance->user_id, 'year_month' => 'year_month']) }}"
                id="year_month_url">
                <input type="text" class="monthPick" id="year_month" name="year_month" value="{{ $date }}">
                <input type="submit" class="d-none" id="year_month_submit">
            </div>
        </div>
        <div class="col-2">
            <a href="{{ route('stamp.index', ['user_id' => $attendance->user_id, 'year_month' => $date]) }}" class="btn btn-primary">
                <i class="fas fa-clock mr-1"></i>打刻画面へ
            </a>
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
                    data-work_date="{{ $day['work_date'] }}"
                    @if($attendance->confirm_flag) style="pointer-events: none; color: gray;" @endif>
                    {{ $day['day'] }}日
                </th>
                <th class="text-center">{{ $day['dayOfWeek'] }}
                </th>
                @if (count($attendanceDaily) > 0 && isset($attendanceDaily[$day['work_date']]))
                <th class="text-center attendance_class memo id"
                    data-id="{{ $attendanceDaily[$day['work_date']]['id'] }}">
                    {{ AttendanceHelper::attendanceClass($attendanceDaily[$day['work_date']]['attendance_class']) }}
                </th>
                <th class="text-center working_time leave_time">
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['working_time']) }}
                    ~
                    {{ AttendanceHelper::timeFormat($attendanceDaily[$day['work_date']]['leave_time']) }}
                </th>
                <th class="text-center break_times">
                    @if (isset($attendanceDaily[$day['work_date']]))
                        @foreach ($attendanceDaily[$day['work_date']]['break_times'] ?? [] as $breakTime)
                            {{ AttendanceHelper::timeFormat($breakTime['break_time_from']) }} ~ {{ AttendanceHelper::timeFormat($breakTime['break_time_to']) }}<br>
                        @endforeach
                    @endif
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
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="attendance-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div><!-- /.modal-header -->
            <form method="POST" action="{{ route('user.attendance_header.update') }}" id="modal-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
                    <input type="hidden" name="year_month" value="{{ $date }}">
                    <input type="hidden" name="work_date" value="" id="work_date">

                    <div id="error-messages" class="alert alert-danger d-none">
                        <ul id="error-list"></ul>
                    </div>

                    <!-- 勤務区分 -->
                    <div class="form-group row">
                        <label for="attendance_class" class="col-md-4 col-form-label text-right">
                            区分
                        </label>
                        <div class="col-md-8">
                            <div class="form-inline">
                                <select name="attendance_class" class="form-control" id="attendance_class" {{ $attendance->confirm ? 'disabled' : '' }}>
                                    <option value="0">通常勤務</option>
                                    <option value="1">有給休暇</option>
                                    <option value="2">欠勤</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 勤務時間 -->
                    <div class="form-group row">
                        <label for="working_time" class="col-md-4 col-form-label text-right">出勤</label>
                        <div class="col-md-8">
                            <div class="form-inline">
                                <input id="working_time" size="8" type="time" name="working_time"
                                    class="form-control" value="{{ $company->base_time_from }}">
                                <span class="mx-2">〜</span>
                                <input id="leave_time" size="8" type="time" name="leave_time"
                                    class="form-control" value="{{ $company->base_time_to }}">
                            </div>
                        </div>
                    </div>

                    <!-- 休憩時間 -->
                    <div class="form-group row">
                        <label for="break_times" class="col-md-4 col-form-label text-right">休憩</label>
                        <div class="col-md-8">
                            <div id="break-times-container">
                                <!-- 休憩時間入力エリア -->
                                <div class="form-inline mb-2 break-time-entry">
                                    <input type="time" name="break_times[0][break_time_from]" class="form-control" placeholder="開始時間">
                                    <span class="mx-2">〜</span>
                                    <input type="time" name="break_times[0][break_time_to]" class="form-control" placeholder="終了時間">
                                    <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
                                </div>
                            </div>
                            <button type="button" id="add-break-time" class="btn btn-primary btn-sm mt-2">休憩時間を追加</button>
                        </div>
                    </div>

                    <!-- メモ -->
                    <div class="form-group row">
                        <label for="memo" class="col-md-4 col-form-label text-right">メモ</label>
                        <div class="col-md-8">
                            <textarea class="field-textarea" id="memo" name="memo"></textarea>
                        </div>
                    </div>
                </div><!-- /.modal-body -->
                <div class="modal-footer">
                    <a data-url="{{ route('user.attendance_header.delete', ['user_id' => $attendance->user_id, 'year_month' => $date, 'work_date' => 'work_date']) }}"
                        class="btn btn-secondary" id="delete-url">未入力に戻す</a>
                    <button type="submit" class="btn btn-primary">変更を保存</button>
                </div><!-- /.modal-footer -->
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@section('addJs')
<script src="{{ asset('/js/attendanceForm.js') }}"></script>
@endsection
