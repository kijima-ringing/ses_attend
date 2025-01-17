@extends('layouts.app')

@section('addCss')
<style>
    .chat-container {
        height: 500px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 20px;
    }
    .message {
        margin-bottom: 15px;
    }
    .message-admin {
        text-align: right;
    }
    .message-user {
        text-align: left;
    }
    .message-content {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 15px;
        max-width: 70%;
    }
    .message-admin .message-content {
        background-color: #007bff;
        color: white;
    }
    .message-user .message-content {
        background-color: #e9ecef;
    }
    .message-time {
        font-size: 0.8em;
        color: #6c757d;
        margin: 5px 0;
    }
    .chat-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .back-button {
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="back-button">
                <a href="{{ route('admin.chat.list') }}" class="btn btn-secondary">戻る</a>
            </div>
            <h4>{{ $chat_room->user->last_name }} {{ $chat_room->user->first_name }}</h4>
            <div class="chat-container">
                @foreach($chat_room->messages as $message)
                    <div class="message {{ $message->user_id == Auth::id() ? 'message-admin' : 'message-user' }}">
                        <div class="message-content">
                            {{ $message->message }}
                        </div>
                        <div class="message-time">
                            {{ \Carbon\Carbon::parse($message->created_at)->format('Y-n-j H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
            <form action="{{ route('admin.chat.send', ['room_id' => $chat_room->id]) }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="message" class="chat-input" placeholder="メッセージを入力">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">送信</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('addJs')

@endsection