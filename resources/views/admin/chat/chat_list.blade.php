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
            <table class="table table-bordered chat-list-table">
                <thead class="bg-info">
                    <tr>
                        <th width="30%">社員名</th>
                        <th>最終メッセージ日付</th>
                        <th width="15%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->last_name }} {{ $user->first_name }}</td>
                            <td>
                                @php
                                    $room = $chat_rooms->where('user_id', $user->id)->first();
                                @endphp
                                @if($room && $room->latestMessage)
                                    {{ \Carbon\Carbon::parse($room->latestMessage->created_at)->format('Y-n-j') }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($room)
                                    <a href="{{ route('admin.chat.room', ['room_id' => $room->id]) }}" 
                                       class="btn btn-primary chat-button">チャット画面</a>
                                @else
                                    <form action="{{ route('admin.chat.create') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <button type="submit" class="btn btn-success chat-button">チャット開始</button>
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
