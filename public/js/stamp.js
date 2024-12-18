document.addEventListener('DOMContentLoaded', function() {
    // 現在時刻を表示する関数
    function updateDateTime() {
        const now = new Date();

        // 日付のフォーマット
        const dateOptions = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            weekday: 'short'
        };
        const formattedDate = now.toLocaleDateString('ja-JP', dateOptions);

        // 時刻のフォーマット
        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        const formattedTime = now.toLocaleTimeString('ja-JP', timeOptions);

        // 画面に表示
        document.getElementById('current-date').textContent = formattedDate;
        document.getElementById('current-time').textContent = formattedTime;
    }

    // 初回実行
    updateDateTime();

    // より正確な時刻表示のために100ミリ秒ごとに更新
    setInterval(updateDateTime, 100);

    // 各ボタンの要素を取得
    const workStartBtn = document.getElementById('work-start');
    const workEndBtn = document.getElementById('work-end');
    const breakStartBtn = document.getElementById('break-start');
    const breakEndBtn = document.getElementById('break-end');

    // ボタンのクリックイベントを共通化
    function handleButtonClick(url, successMessage) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_id: document.querySelector('meta[name="user-id"]').content
            })
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'エラーが発生しました。');
                }
                return data;
            });
        })
        .then(data => {
            if (data.locked) {
                alert('この操作は現在ロックされています。しばらくしてから再試行してください。');
            } else if (data.success) {
                alert(successMessage);
            } else {
                alert(data.message || 'エラーが発生しました。');
            }
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'エラーが発生しました。');
            window.location.reload();
        });
    }

    // 出勤ボタンのクリックイベント
    workStartBtn.addEventListener('click', function() {
        handleButtonClick('/api/attendance/start', '出勤時間を記録しました。');
    });

    // 退勤ボタンのクリックイベント
    workEndBtn.addEventListener('click', function() {
        handleButtonClick('/api/attendance/end', '退勤時間を記録しました。');
    });

    // 休憩開始ボタンのクリックイベント
    breakStartBtn.addEventListener('click', function() {
        handleButtonClick('/api/break/start', '休憩開始時間を記録しました。');
    });

    // 休憩終了ボタンのクリックイベント
    breakEndBtn.addEventListener('click', function() {
        handleButtonClick('/api/break/end', '休憩終了時間を記録しました。');
    });
});
