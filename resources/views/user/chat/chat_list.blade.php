@extends('layouts.app')

@section('addCss')
<style>
    .chat-list-table {
        margin-top: 20px;
    }
    .chat-button {
        min-width: 100px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="row pb-3">
                <div class="col-md-2 ml-auto">
                    <a href="{{ route('user.attendance_header.show', ['user_id' => Auth::id(), 'year_month' => now()->format('Y-m')]) }}" class="nav-link text-right">
                        <i class="fas fa-calendar-alt mr-1"></i>勤怠一覧
                    </a>
                </div>
            </div>
            <table class="table table-bordered chat-list-table">
                <thead class="bg-info">
                    <tr>
                        <th width="30%">管理者</th>
                        <th>日付</th>
                        <th width="15%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chat_rooms as $room)
                        @php
                            $hasUnread = \App\Models\ChatMessage::where('chat_room_id', $room->id)
                                ->where('user_id', '!=', Auth::id())
                                ->where('read_flag', 0)
                                ->exists();
                        @endphp
                        <tr style="{{ $hasUnread ? 'background-color: #f9d6d5;' : '' }}">
                            <td>{{ $room->admin->last_name }} {{ $room->admin->first_name }}</td>
                            <td>
                                @if($room->latestMessage)
                                    {{ \Carbon\Carbon::parse($room->latestMessage->created_at)->timezone('Asia/Tokyo')->format('Y-n-j H:i:s') }}
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('user.chat.room', ['room_id' => $room->id]) }}" 
                                   class="btn btn-primary chat-button">チャット画面</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('addJs')

@endsection