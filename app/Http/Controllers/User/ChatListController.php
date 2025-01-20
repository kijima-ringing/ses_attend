<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class ChatListController extends Controller
{
    public function index()
    {
        // 管理者（admin_flag=1）のユーザーを取得
        $admins = User::where('admin_flag', 1)->get();

        // ログインユーザーに関連するチャットルームを取得
        $chat_rooms = ChatRoom::with(['admin', 'latestMessage'])
            ->where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        // 未読メッセージの確認
        $unreadMessages = ChatMessage::where('user_id', '!=', Auth::id())
            ->where('read_flag', 0)
            ->whereIn('chat_room_id', $chat_rooms->pluck('id'))
            ->exists();

        return view('user.chat.chat_list', compact('admins', 'chat_rooms', 'unreadMessages'));
    }

    public function createRoom(Request $request)
    {
        // 既存のチャットルームをチェック
        $existingRoom = ChatRoom::where('admin_id', $request->admin_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$existingRoom) {
            // 最後のIDを取得して+1する
            $lastId = ChatRoom::max('id') ?? 0;
            
            // UTCの現在時刻を取得し、Asia/Tokyoに変換
            $now = \Carbon\Carbon::now()->timezone('Asia/Tokyo');
            
            // チャットルームを作成
            $chatRoom = ChatRoom::create([
                'id' => $lastId + 1,
                'admin_id' => $request->admin_id,
                'user_id' => Auth::id(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => $now->setTimezone('UTC'),
                'updated_at' => $now->setTimezone('UTC')
            ]);

            return redirect()->route('user.chat.room', ['room_id' => $chatRoom->id]);
        }

        return redirect()->route('user.chat.room', ['room_id' => $existingRoom->id]);
    }
}
