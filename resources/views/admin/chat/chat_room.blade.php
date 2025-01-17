@extends('layouts.app')

@section('addCss')
<link rel="stylesheet" href="{{ asset('/css/chat.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="back-button chat-flex">
                <h4>{{ $chat_room->user->last_name }} {{ $chat_room->user->first_name }}</h4>
                <a href="{{ route('admin.chat.list') }}" class="btn btn-secondary">戻る</a>
            </div>
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
            <form id="message-form" action="{{ route('admin.chat.send', ['room_id' => $chat_room->id]) }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" id="message-input" name="message" class="chat-input" placeholder="メッセージを入力">
                    <div class="send-button-container">
                        <button type="submit" class="btn btn-primary send-button">送信</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('addJs')
<script src="{{ asset('js/chat.js') }}"></script>
@endsection