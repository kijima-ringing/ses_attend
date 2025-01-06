$(document).ready(function() {
    let currentRequestId = null;

    // 承認モーダルを開く時の処理
    $('.status-link').click(function(e) {
        e.preventDefault();
        currentRequestId = $(this).data('request-id');
        
        // return_reasonが存在する場合、テキストエリアに設定
        const returnReason = $(this).data('return-reason');
        if (returnReason) {
            $('#returnReason').val(returnReason);
            // 差し戻し用の表示に切り替え
            $('#approveType').val('return').trigger('change');
        } else {
            // return_reasonがない場合は承認用の表示にリセット
            $('#returnReason').val('');
            $('#approveType').val('approve').trigger('change');
        }
    });

    // 処理区分の変更時の処理
    $('#approveType').change(function() {
        const selectedValue = $(this).val();
        if (selectedValue === 'return') {
            $('#returnReasonGroup').show();
            $('#submitApprove').hide();
            $('#submitReturn').show();
        } else {
            $('#returnReasonGroup').hide();
            $('#submitApprove').show();
            $('#submitReturn').hide();
        }
    });

    // 承認ボタンクリック時の処理
    $('#submitApprove').click(function() {
        if (!currentRequestId) return;

        $.ajax({
            url: `/admin/request/${currentRequestId}/approve`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert('申請を承認しました。');
                    location.reload();
                }
            },
            error: function() {
                alert('承認処理に失敗しました。');
            }
        });

        // モーダルを閉じる
        $('#approveModal').modal('hide');
    });

    // 差し戻しボタンクリック時の処理
    $('#submitReturn').click(function() {
        if (!currentRequestId) return;

        const returnReason = $('#returnReason').val().trim();
        
        // 差し戻し理由の入力チェック
        if (!returnReason) {
            alert('差し戻し理由を入力してください。');
            return;
        }

        $.ajax({
            url: `/admin/request/${currentRequestId}/return`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                return_reason: returnReason
            },
            success: function(response) {
                if (response.success) {
                    alert('申請を差し戻しました。');
                    location.reload();
                }
            },
            error: function() {
                alert('差し戻し処理に失敗しました。');
            }
        });

        // モーダルを閉じる
        $('#approveModal').modal('hide');
    });
});
