// 勤務区分の定数を定義
const NORMAL_WORKING = '0'; // 通常勤務
const PAID_HOLIDAYS = '1'; // 有給休暇
const ABSENT_WORKING = '2'; // 欠勤

// デフォルトの休憩時間を定義
const BASE_BREAK_TIME_FROM = '12:00:00'; // 休憩開始時間
const BASE_BREAK_TIME_TO = '13:00:00';   // 休憩終了時間

// 会社の勤務時間を取得（HTMLのdata属性から読み込む）
var companyBaseTimeFrom = $('.company').data('base_time_from') || ''; // 勤務開始時間
var companyBaseTimeTo = $('.company').data('base_time_to') || '';     // 勤務終了時間

// 勤怠情報を取得するためのURL（HTMLのdata属性から読み込む）
var getAttendanceInfoUrl = $('#attendance-info-url').data('url');

// 部門インデックスのデータ属性を削除
$('#department-index').removeAttr('data-url');

// ドキュメント読み込み後の処理
$(function () {
    // 「.dialog」クラスの要素がクリックされたときの処理
    $(".dialog").click(function () {
        var parent = $(this).parent(); // クリックされた要素の親要素を取得
        var id = parent.find('.id').data('id'); // 勤怠IDを取得

        // 勤怠IDがある場合、AJAXで勤怠データを取得
        if (id) {
            $.ajax({
                type: 'GET',                // HTTPメソッドはGET
                url: getAttendanceInfoUrl, // 勤怠情報取得用URL
                dataType: 'json',          // 応答データ形式はJSON
                data: { id: id }           // リクエストに勤怠IDを含める
            }).done(function (res) {
                // AJAX成功時の処理
                let data = res.data;

                // 勤怠データを取得、またはデフォルト値を設定
                var attendance_class = data.attendance_class || NORMAL_WORKING;
                var working_time = data.working_time || companyBaseTimeFrom;
                var leave_time = data.leave_time || companyBaseTimeTo;
                var memo = data.memo || '';

                // モーダル内の各フィールドに値をセット
                $('#attendance_class').val(attendance_class);
                $('#working_time').val(working_time);
                $('#leave_time').val(leave_time);
                $('#memo').val(memo);

                // 休憩時間の表示処理
                $('#break-times-container').empty(); // 既存の休憩時間をクリア
                if (data.break_times && data.break_times.length > 0) {
                    data.break_times.forEach(function (breakTime, index) {
                        $('#break-times-container').append(`
                            <div class="form-inline mb-2 break-time-entry">
                                <input type="time" name="break_times[${index}][break_time_from]" value="${breakTime.break_time_from}" class="form-control">
                                <span class="mx-2">〜</span>
                                <input type="time" name="break_times[${index}][break_time_to]" value="${breakTime.break_time_to}" class="form-control">
                                <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
                            </div>
                        `);
                    });
                } else {
                    // デフォルトの休憩時間を追加
                    $('#break-times-container').append(`
                        <div class="form-inline mb-2 break-time-entry">
                            <input type="time" name="break_times[0][break_time_from]" value="${BASE_BREAK_TIME_FROM}" class="form-control">
                            <span class="mx-2">〜</span>
                            <input type="time" name="break_times[0][break_time_to]" value="${BASE_BREAK_TIME_TO}" class="form-control">
                            <button type="button" class="btn btn-danger btn-sm ml-2 remove-break-time">削除</button>
                        </div>
                    `);
                }
            }).fail(function () {
                // AJAX失敗時のエラーメッセージ表示
                alert('ajax通信に失敗しました');
            });
        } else {
            // 勤怠IDがない場合はモーダルのフィールドをリセット
            resetModalFields();
        }

        // 日付情報と勤務日を取得
        let dateInfo = parent.find('.date_info').data('date_info');
        let work_date = parent.find('.work_date').data('work_date');

        // URLのプレースホルダーを勤務日に置き換え
        let replace = $('#delete-url').data("url").replace('work_date', work_date);
        $('#work_date').val(work_date);           // モーダルの隠しフィールドに勤務日をセット
        $('#delete-url').attr("href", replace);   // 削除ボタンのURLを設定

        $('.modal-title').text(dateInfo); // モーダルのタイトルを設定
        lockFieldsIfConfirmed();         // 確定状態に応じてフィールドをロック
        $(".modal").modal("show");       // モーダルを表示
    });
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

// 確定済みの場合にモーダルのフィールドをロックする関数
function lockFieldsIfConfirmed() {
    let isConfirmed = $('#attendance-info-url').data('confirmed'); // 確定状態を取得
    if (isConfirmed) {
        $('#modal-form :input').not('#attendance_submit, #delete-url').prop('disabled', true);
        $('#attendance_submit').prop('disabled', true);
        $('#delete-url').prop('disabled', true);
    } else {
        $('#modal-form :input').prop('disabled', false);
        $('#attendance_submit').prop('disabled', false);
        $('#delete-url').prop('disabled', false);
    }
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
