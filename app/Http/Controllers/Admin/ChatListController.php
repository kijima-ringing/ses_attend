<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class ChatListController extends Controller
{
    public function index()
    {
        // admin_flagが0のユーザーを取得
        $users = User::where('admin_flag', 0)->get();

        // 各ユーザーに対応するチャットルームを取得
        $chat_rooms = ChatRoom::with(['user', 'latestMessage'])
            ->where('admin_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.chat.chat_list', compact('users', 'chat_rooms'));
    }

    public function createRoom(Request $request)
    {
        // 既存のチャットルームをチェック
        $existingRoom = ChatRoom::where('admin_id', Auth::id())
            ->where('user_id', $request->user_id)
            ->first();

        if (!$existingRoom) {
            // 最後のIDを取得して+1する
            $lastId = ChatRoom::max('id') ?? 0;
            
            // チャットルームを作成
            $chatRoom = ChatRoom::create([
                'id' => $lastId + 1,
                'admin_id' => Auth::id(),
                'user_id' => $request->user_id,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            // 作成したチャットルームのIDを使用
            return redirect()->route('admin.chat.room', ['room_id' => $chatRoom->id]);
        }

        // 既存のチャットルームがある場合はそのルームに遷移
        return redirect()->route('admin.chat.room', ['room_id' => $existingRoom->id]);
    }
}
