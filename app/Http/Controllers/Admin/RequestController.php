<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaidLeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    public function index()
    {
        $requests = PaidLeaveRequest::with(['paidLeaveDefault.user', 'attendanceDaily'])
            ->where('status', PaidLeaveRequest::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        // ステータス定数をビューに渡す
        $statuses = [
            'STATUS_PENDING' => PaidLeaveRequest::STATUS_PENDING,
            'STATUS_APPROVED' => PaidLeaveRequest::STATUS_APPROVED,
            'STATUS_RETURNED' => PaidLeaveRequest::STATUS_RETURNED,
        ];

        return view('admin.request.request', compact('requests', 'statuses'));
    }

    public function approve(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $paidLeaveRequest = PaidLeaveRequest::findOrFail($id);

                // 有給休暇申請を承認状態に更新
                $paidLeaveRequest->update([
                    'status' => PaidLeaveRequest::STATUS_APPROVED
                ]);

                // 残日数を減少
                $paidLeaveDefault = $paidLeaveRequest->paidLeaveDefault;
                $paidLeaveDefault->decrement('remaining_days');
            });

            return response()->json(['success' => true, 'message' => '申請を承認しました。']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '承認処理に失敗しました。'], 500);
        }
    }

    public function return(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'return_reason' => 'required|string|max:1000',
            ]);

            $paidLeaveRequest = PaidLeaveRequest::findOrFail($id);
            $paidLeaveRequest->update([
                'status' => PaidLeaveRequest::STATUS_RETURNED,
                'return_reason' => $validated['return_reason']
            ]);

            return response()->json(['success' => true, 'message' => '申請を差し戻しました。']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '差し戻し処理に失敗しました。'], 500);
        }
    }
}
