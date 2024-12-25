@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="h2 mb-4">有給休暇申請一覧</h1>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>申請者</th>
                    <th>申請日</th>
                    <th>申請理由</th>
                    <th>ステータス</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                <tr>
                    <td>{{ $request->paidLeaveDefault->user->last_name }} {{ $request->paidLeaveDefault->user->first_name }}</td>
                    <td>{{ $request->attendanceDaily->work_date }}</td>
                    <td>{{ $request->request_reason }}</td>
                    <td>
                        @if($request->status === 0)
                            <span class="badge badge-warning">申請中</span>
                        @endif
                    </td>
                    <td>
                        <a href="#" class="btn btn-primary btn-sm approve-request" 
                            data-request-id="{{ $request->id }}" 
                            data-toggle="modal" 
                            data-target="#approveModal">
                            承認・差戻
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 承認モーダル -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">有給休暇申請の承認・差戻</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="approveForm">
                    <div class="form-group">
                        <label>処理区分</label>
                        <select class="form-control" id="approveType">
                            <option value="approve">承認</option>
                            <option value="return">差戻</option>
                        </select>
                    </div>
                    <div class="form-group" id="returnReasonGroup" style="display: none;">
                        <label>差戻理由</label>
                        <textarea class="form-control" id="returnReason" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" id="submitApprove">承認</button>
                <button type="button" class="btn btn-warning" id="submitReturn" style="display: none;">差戻</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('addJs')
<script src="{{ asset('/js/admin/request.js') }}"></script>
@endsection
