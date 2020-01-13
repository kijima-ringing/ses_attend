@extends('layouts.app')

@section('addCss')
@endsection

@section('content')
    <div class="container">
        <div class="row pb-3">
            <div class="col-12">
                <form method="GET" action="{{ route('admin.attendance_header.index') }}">
                    @csrf
                    <input type="text" class="monthPick" id="year_month" name="year_month" value="{{ $date }}">
                    <input type="submit" class="d-none" id="year_month_submit">
                </form>
            </div>
        </div>
        <table class="table table-bordered">
            <thead class="bg-info">
            <tr>
                <th width="30%">社員名</th>
                <th>勤務日数</th>
                <th>所定内労働時間</th>
                <th>残業時間</th>
                <th>総労働時間</th>
                <th width="10%"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($users AS $user)
                <tr class="bg-white">
                    <th class="text-center">{{ $user->last_name }}{{ $user->first_name }}</th>
                    <th class="text-center">{{ $user->working_days }}</th>
                    <th class="text-center">{{ $user->scheduled_working_hours }}</th>
                    <th class="text-center">{{ $user->overtime_hours }}</th>
                    <th class="text-center">{{ $user->working_hours }}</th>
                    <td class="text-center">
                        <a href="{{ route('admin.attendance_header.show', ['user_id' => $user->user_id, 'year_month' => $date]) }}" class="btn btn-info">詳細</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
@section('addJs')

@endsection
