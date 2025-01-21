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
    <div class="row pb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                <a href="{{ route('user.attendance_header.show', ['user_id' => Auth::id(), 'year_month' => now()->format('Y-m')]) }}" class="nav-link">
                    <i class="fas fa-calendar-alt mr-1"></i><span class="text-nowrap">勤怠一覧</span>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered chat-list-table">
                <thead class="bg-info">
                    <tr>
                        <th width="30%">管理者</th>
                        <th>最終メッセージ日付</th>
                        <th width="15%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                        @php
                            $room = $chat_rooms->where('admin_id', $admin->id)->first();
                            $hasUnread = $room ? \App\Models\ChatMessage::where('chat_room_id', $room->id)
                                ->where('user_id', '!=', Auth::id())
                                ->where('read_flag', 0)
                                ->exists() : false;
                        @endphp
                        <tr style="{{ $hasUnread ? 'background-color: #f9d6d5;' : '' }}">
                            <td>{{ $admin->last_name }} {{ $admin->first_name }}</td>
                            <td>
                                @if($room && $room->latestMessage)
                                    {{ \Carbon\Carbon::parse($room->latestMessage->created_at)->timezone('Asia/Tokyo')->format('Y-n-j H:i') }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($room)
                                    <a href="{{ route('user.chat.room', ['room_id' => $room->id]) }}" 
                                       class="btn btn-primary chat-button">チャット画面</a>
                                @else
                                    <form action="{{ route('user.chat.create') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="admin_id" value="{{ $admin->id }}">
                                        <button type="submit" class="btn btn-primary chat-button">チャット画面</button>
                                    </form>
                                @endif
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