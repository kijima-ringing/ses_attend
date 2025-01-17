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
                        <th>日付</th>
                        <th width="15%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chat_rooms as $room)
                        <tr>
                            <td>{{ $room->user->last_name }} {{ $room->user->first_name }}</td>
                            <td>
                                @if($room->latestMessage)
                                    {{ \Carbon\Carbon::parse($room->latestMessage->created_at)->format('Y-n-j') }}
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.chat.room', ['room_id' => $room->id]) }}" 
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
