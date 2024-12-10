@extends('layouts.app')

@section('addCss')
<link href="{{ asset('css/stamp.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="stamp-container">
    <div class="mb-4">
        <a href="{{ route('user.attendance_header.show', ['user_id' => $user_id, 'year_month' => $date]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>勤怠一覧へ戻る
        </a>
    </div>

    <div class="date-display">
        <span id="current-date"></span>
    </div>
    <div class="time-display">
        <span id="current-time"></span>
    </div>

    <div class="stamp-buttons">
        <button class="stamp-button" id="work-start">
            出勤
        </button>
        <button class="stamp-button" id="work-end">
            退勤
        </button>
    </div>

    <div class="break-buttons">
        <button class="break-button" id="break-start">
            休憩開始
        </button>
        <button class="break-button" id="break-end">
            休憩終了
        </button>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('addJs')
<script src="{{ asset('js/stamp.js') }}"></script>
@endsection