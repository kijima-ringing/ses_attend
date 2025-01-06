$(document).ready(function() {
    let currentRequestId = null;

    // 承認モーダルを開く時の処理
    $('.status-link').click(function(e) {
        e.preventDefault();
        currentRequestId = $(this).data('request-id');
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
    });

    // 差戻ボタンクリック時の処理
    $('#submitReturn').click(function() {
        if (!currentRequestId) return;

        const returnReason = $('#returnReason').val();
        if (!returnReason) {
            alert('差戻理由を入力してください。');
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
                alert('差戻処理に失敗しました。');
            }
        });
    });
});
