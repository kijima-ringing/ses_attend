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

        // 確定フラグを取得し、数値型で比較
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
                    var attendance_class = data.attendance_class || NORMAL_WORKING;
                    var working_time = formatTimeToHHMM(data.working_time) || companyBaseTimeFrom;
                    var leave_time = formatTimeToHHMM(data.leave_time) || companyBaseTimeTo;
                    var memo = data.memo || '';

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
            } else {
                alert('申請処理中にエラーが発生しました。');
            }
        }
    });
});

// 有給休暇の日付クリック時の処理
$(document).on('click', '.paid-leave-dialog', function(event) {
    event.preventDefault();
    
    const dateInfo = $(this).data('date_info');
    const paidLeaveReason = $(this).data('paid-leave-reason');
    
    // 有給休暇詳細モーダルの内容を設定
    $('#paid-leave-date').text(dateInfo);
    $('#paid-leave-reason-display').text(paidLeaveReason || '理由が登録されていません');
    
    // 有給休暇詳細モーダルを表示
    $('#paid-leave-modal').modal('show');
});