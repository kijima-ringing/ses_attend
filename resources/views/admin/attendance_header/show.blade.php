@extends('layouts.app')

@section('addCss')
@endsection

@section('content')
    <div class="container">
        <div class="row pb-3">
            <div class="col-2">
                <form method="GET" action="{{ route('admin.attendance_header.index') }}">
                    @csrf
                    <input type="text" class="monthPick" id="year_month" name="year_month" value="{{ $date }}">
                    <input type="submit" class="d-none" id="year_month_submit">
                </form>
            </div>

            <div class="col-2 text-right h2">
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
                    <th class="text-center">{{ $attendance->working_days }}日</th>
                    <th class="text-center">{{ $attendance->scheduled_working_hours }}</th>
                    <th class="text-center">{{ $attendance->overtime_hours }}</th>
                    <th class="text-center">{{ $attendance->working_hours }}</th>
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
                    <tr class="bg-white">
                        <th class="text-right">{{ $day['day'] }}日</th>
                        <th class="text-center">{{ $day['dayOfWeek'] }}</th>
                        @if (count($atendanceDaily) > 0)
                            @foreach($atendanceDaily as $daily)
                                @if ($daily->work_date == $day['work_date'])
                                    <th class="text-center">{{ AttendanceHelper::attendanceClass($daily->attendance_class) }}</th>
                                    <th class="text-center">{{ $daily->working_time  }} ~ {{ $daily->leave_time }}</th>
                                    <th class="text-center">{{ $daily->break_time_from  }} ~ {{ $daily->break_time_to }}</th>
                                    <th class="text-right">{{ $daily->scheduled_working_hours }}</th>
                                    <th class="text-right">{{ $daily->overtime_hours }}</th>
                                    <th class="text-right">{{ $daily->working_hours }}</th>
                                @else
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                @endif
                            @endforeach
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

@endsection
@section('addJs')

@endsection
