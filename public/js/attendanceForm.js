// 勤務区分の定数を定義
const NORMAL_WORKING = '0'; // 通常勤務
const PAID_HOLIDAYS = '1'; // 有給休暇
const ABSENT_WORKING = '2'; // 欠勤

// デフォルトの休憩時間を定義
const BASE_BREAK_TIME_FROM = '12:00'; // 休憩開始時間
const BASE_BREAK_TIME_TO = '13:00';   // 休憩終了時間

// 会社の勤務時間を取得（HTMLのdata属性から読み込む）
var companyBaseTimeFrom = $('.company').data('base_time_from') || ''; // 勤務開始時間
var companyBaseTimeTo = $('.company').data('base_time_to') || '';     // 勤務終了時間

// 勤怠情報を取得するためのURL（HTMLのdata属性から読み込む）
var getAttendanceInfoUrl = $('#attendance-info-url').data('url');

// ベースURLを動的に設定
var baseLockUrl = $('meta[name="base-lock-url"]').attr('content') || '/admin/attendance_daily/';

// 部門インデックスのデータ属性を削除
$('#department-index').removeAttr('data-url');

// HH:mm:ss を HH:mm に変換する関数
function formatTimeToHHMM(time) {
    if (!time) return ''; // 空値の場合はそのまま返す
    return time.substring(0, 5); // HH:mm 部分だけを抽出
}

// ページリロード時にdisableModalFieldsを実行
$(document).ready(function () {
    setTimeout(disableModalFields, 0);
});

// ローダル内のフォーム要素を無効化する関数
function disableModalFields() {
    // モーダル内のすべてのフォーム要素を非活性化
    $('#attendance-modal').find('input, select, textarea, button').prop('disabled', true);
    // `<a>` タグを無効化する（クリックイベントを停止）
    $('#attendance-modal').find('a').addClass('disabled').css('pointer-events', 'none');
    // 動的に追加された休憩時間の削除ボタンを無効化
    $('#break-times-container').find('.break-time-entry input, .break-time-entry button').prop('disabled', true);
}

// モーダル内のフォーム要素を有効化する関数（必要に応じて）
function enableModalFields() {
    // モーダル内のすべてのフォーム要素を活性化
    $('#attendance-modal').find('input, select, textarea, button').prop('disabled', false);
    // `<a>` タグを有効化する
    $('#attendance-modal').find('a').removeClass('disabled').css('pointer-events', 'auto');
    // 動的に追加された休憩時間の削除ボタンを有効化
    $('#break-times-container').find('.break-time-entry input, .break-time-entry button').prop('disabled', false);
}

// モーダルを閉じる際にフォームを再度有効化
$('#attendance-modal').on('hidden.bs.modal', function () {
    setTimeout(disableModalFields, 0);
});

// モーダル内のフィールドをリセットする関数
function resetModalFields() {
    $('#attendance_class').val(NORMAL_WORKING);          // 勤務区分を通常勤務にリセット
    $('#working_time').val(companyBaseTimeFrom);         // 勤務開始時間をリセット
    $('#leave_time').val(companyBaseTimeTo);             // 勤務終了時間をリセット
    $('#memo').val('');                                  // メモを空にリセット
    $('#break-times-container').empty();                // 休憩時間をクリア
    $('#return-reason-section').hide();                 // 差し戻し理由セクションを非表示
    $('#break-times-container').append(`
        <div class="form-inline mb-2 break-time-entry">
            <input type="time" name="break_times[0][break_time_from]" value="${BASE_BREAK_TIME_FROM}" class="form-control">
            <span class="mx-2">〜</span>
            <input type="time" name="break_times[0][break_time_to]" value="${BASE_BREAK_TIME_TO}" class="form-control">
            <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
        </div>
    `);
}

// 保存ボタンのクリックイベント（AJAX送信）
$('#modal-form').on('submit', function (e) {
    e.preventDefault();

    const form = $(this);
    const url = form.attr('action');
    const formData = form.serialize();

    $.ajax({
        type: 'POST',
        url: url,
        data: formData,
        success: function (response) {
            if (response.success) {
                alert(response.message);
                location.reload(); // 必要に応じてリロード
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayErrors(errors);
            } else if (xhr.status === 403) {
                // 403エラーの場合に、サーバーからのメッセージを表示
                alert(xhr.responseJSON.message || 'この操作は許可されていません。');
                setTimeout(() => {
                    location.reload(); // ページリロードを遅らせて実行
                }, 100); // 100ミリ秒後にリロード
            } else {
                alert('サーバーエラーが発生しました。もう一度お試しください。');
            }
        }
    });
});

// エラーメッセージを表示する関数
function displayErrors(errors) {
    const errorContainer = $('#error-messages');
    const errorList = $('#error-list');

    errorList.empty();
    errorContainer.removeClass('d-none');

    $.each(errors, function (key, messages) {
        messages.forEach(function (message) {
            errorList.append(`<li>${message}</li>`);
        });
    });
}

// 休憩時間追加・削除機能
$(document).ready(function () {
    let breakTimeIndex = 1;

    // 休憩時間を追加するボタンのクリックイベント
    $('#add-break-time').click(function () {
        $('#break-times-container').append(`
            <div class="form-inline mb-2 break-time-entry">
                <input type="time" name="break_times[${breakTimeIndex}][break_time_from]" class="form-control" placeholder="開始時間">
                <span class="mx-2">〜</span>
                <input type="time" name="break_times[${breakTimeIndex}][break_time_to]" class="form-control" placeholder="終了時間">
                <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
            </div>
        `);
        breakTimeIndex++;
    });

    // 休憩時間を削除するボタンのクリックイベント
    $(document).on('click', '.remove-break-time', function () {
        $(this).closest('.break-time-entry').remove();
    });
});

// 休憩時間の重複を検出
function hasDuplicateBreakTimes(breakTimes) {
    const seen = new Set();
    for (const breakTime of breakTimes) {
        const key = `${breakTime.break_time_from}-${breakTime.break_time_to}`;
        if (seen.has(key)) {
            return true; // 重複が見つかった場合
        }
        seen.add(key);
    }
    return false;
}

// 保存ボタンのクリックイベント
$('#attendance-submit-button').click(function (e) {
    e.preventDefault();

    const breakTimes = [];
    $('.break-time-entry').each(function () {
        const from = $(this).find('input[name*="[break_time_from]"]').val();
        const to = $(this).find('input[name*="[break_time_to]"]').val();
        if (from && to) {
            breakTimes.push({ break_time_from: from, break_time_to: to });
        }
    });

    if (hasDuplicateBreakTimes(breakTimes)) {
        alert('休憩時間が重複しています。異なる時間を入力してください。');
        return false;
    }

    // 重複がなければサーバーに送信
    $('#attendance-form').submit();
});

// 削除ボタンのクリックイベント
$('#delete-url').on('click', function (e) {
    e.preventDefault();

    const deleteUrl = $(this).attr('href');

    if (confirm('この勤怠データを削除しますか？')) {
        $.ajax({
            type: 'DELETE', // HTTP メソッドを DELETE に変更
            url: deleteUrl,
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'), // CSRFトークン
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload(); // 正常時はページをリロード
                }
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    // 確定済みデータの場合
                    alert(xhr.responseJSON.message || 'この勤怠データは確定済みのため削除できません。');
                    setTimeout(() => {
                        location.reload(); // ページリロード
                    }, 100);
                } else {
                    alert('削除中にエラーが発生しました。もう一度お試しください。');
                }
            },
        });
    }
});

// ドキュメント読み込み後の処理
$(function () {
    $(".dialog").click(function (event) {
        event.preventDefault(); // デフォルトのクリック動作を停止

        // 確定フラグを取得し、数値型比較
        let isConfirmed = Number($('#attendance-info-url').data('confirmed')) === 1;

        // 確定済みの場合はクリックを無効化
        if (isConfirmed) {
            alert('このデータは確定済みのため編集できません。');
            return;
        }

        var parent = $(this).parent();
        var id = parent.find('.id').data('id');
        let dateInfo = parent.find('.date_info').data('date_info');
        let work_date = parent.find('.work_date').data('work_date');

        // ロックチェックとモーダル表示の処理を関数化
        const showModalWithData = () => {
            // 勤怠IDがある場合、AJAXで勤怠データを取得
            if (id) {
                $.ajax({
                    type: 'GET',
                    url: getAttendanceInfoUrl,
                    dataType: 'json',
                    data: { id: id }
                }).done(function (res) {
                    let data = res.data;

                    // 勤怠データを取得またはデフォルト値を設定
                    // 有給休暇で差し戻しの場合は通常勤務にセット
                    var attendance_class = NORMAL_WORKING;
                    var working_time = formatTimeToHHMM(data.working_time) || companyBaseTimeFrom;
                    var leave_time = formatTimeToHHMM(data.leave_time) || companyBaseTimeTo;
                    var memo = data.memo || '';

                    // 有給休暇で差し戻しの場合、差し戻し理由を表示
                    if (data.attendance_class == PAID_HOLIDAYS && 
                        data.paid_leave_request && 
                        data.paid_leave_request.status == 2) { // 2は差し戻しステータス
                        $('#return-reason-section').show();
                        $('#attendance-return-reason').text(data.paid_leave_request.return_reason || '');
                    } else {
                        $('#return-reason-section').hide();
                    }

                    // モーダル内の各フィールドに値をセット
                    $('#attendance_class').val(attendance_class);
                    $('#working_time').val(working_time);
                    $('#leave_time').val(leave_time);
                    $('#memo').val(memo);

                    // 休憩時間の表示処理
                    $('#break-times-container').empty();
                    if (data.break_times && data.break_times.length > 0) {
                        data.break_times.forEach(function (breakTime, index) {
                            var breakTimeFrom = formatTimeToHHMM(breakTime.break_time_from);
                            var breakTimeTo = formatTimeToHHMM(breakTime.break_time_to);
                            $('#break-times-container').append(`
                                <div class="form-inline mb-2 break-time-entry">
                                    <input type="time" name="break_times[${index}][break_time_from]" value="${breakTimeFrom}" class="form-control">
                                    <span class="mx-2">〜</span>
                                    <input type="time" name="break_times[${index}][break_time_to]" value="${breakTimeTo}" class="form-control">
                                    <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
                                </div>
                            `);
                        });
                    } else {
                        $('#break-times-container').append(`
                            <div class="form-inline mb-2 break-time-entry">
                                <input type="time" name="break_times[0][break_time_from]" value="${BASE_BREAK_TIME_FROM}" class="form-control">
                                <span class="mx-2">〜</span>
                                <input type="time" name="break_times[0][break_time_to]" value="${BASE_BREAK_TIME_TO}" class="form-control">
                                <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
                            </div>
                        `);
                    }
                    // エラー表示エリアを非表示
                    $('#error-messages').addClass('d-none');
                    $('#error-list').empty();

                    // モーダルを表示
                    let replace = $('#delete-url').data("url").replace('work_date', work_date);
                    $('#work_date').val(work_date);
                    $('#delete-url').attr("href", replace);
                    $('.modal-title').text(dateInfo);
                    $(".modal").modal("show");
                    setTimeout(enableModalFields, 0);

                }).fail(function () {
                    alert('AJAX通信に失敗しました');
                });
            } else {
                resetModalFields();
                let replace = $('#delete-url').data("url").replace('work_date', work_date);
                $('#work_date').val(work_date);
                $('#delete-url').attr("href", replace);
                $('.modal-title').text(dateInfo);
                $(".modal").modal("show");
                setTimeout(enableModalFields, 0);
            }
        };

        // IDがある場合はロックチェックを行う
        if (id) {
            $.ajax({
                type: 'GET',
                url: `${baseLockUrl}check-lock`,
                dataType: 'json',
                data: { id: id }
            }).done(function (response) {
                const lockedBy = response.locked_by;
                const currentUserId = $('meta[name="user-id"]').attr('content');

                if (lockedBy && Number(lockedBy) !== Number(currentUserId)) {
                    alert('この勤怠データは他のユーザーが編集中です。');
                    return;
                }

                // ロックを取得してからモーダルを表示
                $.ajax({
                    type: 'POST',
                    url: `${baseLockUrl}lock`,
                    dataType: 'json',
                    data: {
                        id: id,
                        user_id: currentUserId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                }).done(function () {
                    showModalWithData();
                }).fail(function () {
                    alert('勤怠データのロックに失敗しました。');
                });
            }).fail(function () {
                alert('ロック状態の確認に失敗しました。');
            });
        } else {
            // IDがない場合は直接モーダルを表示
            showModalWithData();
        }
    });
});

// 勤務区分の変更を監視
$('#attendance_class').on('change', function() {
    const attendanceClass = $(this).val();
    
    // 有給休暇が選択された場合、デフォルト値をセット
    if (attendanceClass === PAID_HOLIDAYS) {
        // 出勤時間をデフォルト値にセット
        $('#working_time').val(companyBaseTimeFrom);
        $('#leave_time').val(companyBaseTimeTo);
        
        // 休憩時間をデフォルト値にセット
        $('#break-times-container').empty().append(`
            <div class="form-inline mb-2 break-time-entry">
                <input type="time" name="break_times[0][break_time_from]" value="${BASE_BREAK_TIME_FROM}" class="form-control">
                <span class="mx-2">〜</span>
                <input type="time" name="break_times[0][break_time_to]" value="${BASE_BREAK_TIME_TO}" class="form-control">
                <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
            </div>
        `);
    }

    // 入力フィールドの活性/非活性と表示/非表示を切り替え
    toggleTimeInputs(attendanceClass);
    toggleModalElements(attendanceClass);
});

// 入力フィールドの活性/非活性を切り替える関数
function toggleTimeInputs(attendanceClass) {
    const isDisabled = attendanceClass === PAID_HOLIDAYS;
    
    // 出勤時間フィールドと休憩時間関連を非活性化するが、値は保持
    $('#working_time, #leave_time').prop('readonly', isDisabled);
    $('#break-times-container input').prop('readonly', isDisabled);
    
    // ボタン類の非活性化
    $('#add-break-time').prop('disabled', isDisabled);
    $('.remove-break-time').prop('disabled', isDisabled);
}

// メモ欄とボタン類の表示/非表示を切り替える関数
function toggleModalElements(attendanceClass) {
    const isHidden = attendanceClass === PAID_HOLIDAYS;
    
    // 通常の入力要素の表示制御
    $('.form-group.row').has('#memo').toggle(!isHidden);
    
    // 「未入力に戻す」ボタンの表示制御
    $('#delete-url').toggle(!isHidden);

    // 通常の保存ボタンと有給休暇申請ボタンの切り替え
    $('#normal-submit').toggle(!isHidden);
    $('#paid-leave-submit, #paid-leave-section').toggle(isHidden);
}

// 有給休暇申請ボタンのクリックイベント
$(document).on('click', '#paid-leave-submit', function(e) {
    e.preventDefault();
    const reason = $('#paid-leave-reason').val();

    if (!reason) {
        alert('申請理由を入力してください。');
        return;
    }

    // フォームデータの取得
    const form = $('#modal-form');
    const formData = new FormData(form[0]);
    
    // 追加のデータをセット
    formData.set('attendance_class', PAID_HOLIDAYS);
    formData.set('paid_leave_reason', reason);

    // AJAX送信
    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert('有給休暇の申請が完了しました。');
                $('#attendance-modal').modal('hide');
                location.reload();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayErrors(errors);
            } else if (xhr.status === 403) {
                alert(xhr.responseJSON.message || 'この操作は許可されていません。');
                $('#attendance-modal').modal('hide');
                location.reload();
            } else {
                alert('申請処理中にエラーが発生しました。');
            }
        }
    });
});

// 有給休暇の日付クリック時の処理を修正
$(document).on('click', '.paid-leave-dialog', function(event) {
    event.preventDefault();
    
    const dateInfo = $(this).data('date_info');
    const workDate = $(this).data('work_date');
    const attendanceClass = $(this).closest('tr').find('.attendance_class').text().trim();
    const id = $(this).closest('tr').find('.attendance_class').data('id');
    
    // 勤務区分が有給休暇でない場合は処理を中止
    if (attendanceClass !== '有給休暇') {
        return;
    }
    
    // URLから user_id を取得
    const pathSegments = window.location.pathname.split('/');
    const userIdIndex = pathSegments.indexOf('attendance_header') + 1;
    const userId = pathSegments[userIdIndex];
    
    const isAdmin = $('meta[name="is-admin"]').attr('content') === '1';
    
    // URLをデータ属性から取得
    const requestUrl = $('#attendance-info-url').data('request-url');

    $.ajax({
        type: 'GET',
        url: requestUrl,
        data: {
            work_date: workDate,
            user_id: userId
        },
        success: function(response) {
            // statusが2（差し戻し）の場合は勤怠編集モーダルを表示
            if (response.status === 2) {
                // 勤怠編集モーダルを表示する処理を呼び出し
                showModalWithData(id, workDate, dateInfo);
                return;
            }

            // statusが0（申請中）または1（承認済み）の場合は申請詳細モーダルを表示
            if (response.status === 0 || response.status === 1) {
                let statusText = response.status === 0 ? '申請中' : '承認済み';

                // モーダルの内容を設定
                $('#paid-leave-date').text(dateInfo);
                $('#paid-leave-status').text(statusText);
                $('#paid-leave-reason-display').text(response.reason || '');

                // 差し戻し関連の要素を非表示
                $('.return-reason-section').hide();
                $('#paid-leave-reason-display').show();
                $('#paid-leave-reason-edit').hide();
                $('#reapply-button').hide();
                
                // モーダルを表示
                $('#paid-leave-modal').modal('show');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error details:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            alert('申請情報の取得に失敗しました');
        }
    });
});

// 勤怠編集モーダルを表示する関数
function showModalWithData(id, work_date, dateInfo) {
    if (id) {
        $.ajax({
            type: 'GET',
            url: getAttendanceInfoUrl,
            dataType: 'json',
            data: { id: id }
        }).done(function (res) {
            let data = res.data;

            // 勤怠データを取得またはデフォルト値を設定
            // 有給休暇で差し戻しの場合は通常勤務にセット
            var attendance_class = NORMAL_WORKING;
            var working_time = formatTimeToHHMM(data.working_time) || companyBaseTimeFrom;
            var leave_time = formatTimeToHHMM(data.leave_time) || companyBaseTimeTo;
            var memo = data.memo || '';

            // 有給休暇で差し戻しの場合、差し戻し理由を表示
            if (data.attendance_class == PAID_HOLIDAYS && 
                data.paid_leave_request && 
                data.paid_leave_request.status == 2) { // 2は差し戻しステータス
                $('#return-reason-section').show();
                $('#attendance-return-reason').text(data.paid_leave_request.return_reason || '');
            } else {
                $('#return-reason-section').hide();
            }

            // モーダル内の各フィールドに値をセット
            $('#attendance_class').val(attendance_class);
            $('#working_time').val(working_time);
            $('#leave_time').val(leave_time);
            $('#memo').val(memo);

            // 休憩時間の表示処理
            $('#break-times-container').empty();
            if (data.break_times && data.break_times.length > 0) {
                data.break_times.forEach(function (breakTime, index) {
                    var breakTimeFrom = formatTimeToHHMM(breakTime.break_time_from);
                    var breakTimeTo = formatTimeToHHMM(breakTime.break_time_to);
                    $('#break-times-container').append(`
                        <div class="form-inline mb-2 break-time-entry">
                            <input type="time" name="break_times[${index}][break_time_from]" value="${breakTimeFrom}" class="form-control">
                            <span class="mx-2">〜</span>
                            <input type="time" name="break_times[${index}][break_time_to]" value="${breakTimeTo}" class="form-control">
                            <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
                        </div>
                    `);
                });
            } else {
                $('#break-times-container').append(`
                    <div class="form-inline mb-2 break-time-entry">
                        <input type="time" name="break_times[0][break_time_from]" value="${BASE_BREAK_TIME_FROM}" class="form-control">
                        <span class="mx-2">〜</span>
                        <input type="time" name="break_times[0][break_time_to]" value="${BASE_BREAK_TIME_TO}" class="form-control">
                        <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
                    </div>
                `);
            }

            // エラー表示エリアを非表示
            $('#error-messages').addClass('d-none');
            $('#error-list').empty();

            // モーダルを表示
            let replace = $('#delete-url').data("url").replace('work_date', work_date);
            $('#work_date').val(work_date);
            $('#delete-url').attr("href", replace);
            $('.modal-title').text(dateInfo);
            $(".modal").modal("show");
            setTimeout(enableModalFields, 0);

        }).fail(function () {
            alert('AJAX通信に失敗しました');
        });
    }
}

// 再申請ボタンのクリックイベント
$(document).on('click', '#reapply-button', function() {
    const newReason = $('#paid-leave-reason-edit').val();
    const workDate = $('#paid-leave-date').text().split('(')[0]; // 日付部分を抽出
    const userId = $('meta[name="user-id"]').attr('content');
    const isAdmin = $('meta[name="is-admin"]').attr('content') === '1';
    
    // 管理者ページか一般社員ページかに応じてURLを設定
    const baseUrl = isAdmin ? '/admin/attendance_header' : '/user/attendance_header';

    if (!newReason) {
        alert('申請理由を入力してください。');
        return;
    }

    // 再申請のAJAXリクエスト
    $.ajax({
        type: 'POST',
        url: `${baseUrl}/reapply`,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            work_date: workDate,
            user_id: userId,
            paid_leave_reason: newReason
        },
        success: function(response) {
            if (response.success) {
                alert('有給休暇の再申請が完了しました。');
                $('#paid-leave-modal').modal('hide');
                location.reload();
            } else {
                alert(response.message || '再申請処理に失敗しました。');
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                alert(Object.values(errors).flat().join('\n'));
            } else if (xhr.status === 403) {
                alert(xhr.responseJSON.message || 'この操作は許可されていません。');
                $('#paid-leave-modal').modal('hide');
                location.reload();
            } else {
                alert('再申請処理中にエラーが発生しました。');
            }
        }
    });
});