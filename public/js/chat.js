//メッセージ入力欄のバリデーション
$(document).ready(function() {
    // メッセージフォームの送信イベントを処理
    $('#message-form').on('submit', function(e) {
        const messageInput = $('#message-input');
        const message = messageInput.val().trim();
        
        // メッセージが空の場合
        if (!message) {
            e.preventDefault(); // フォーム送信を中止
            alert('メッセージを入力してください。');
            messageInput.focus();
            return false;
        }
        
        // メッセージが1000文字を超える場合
        if (message.length > 1000) {
            e.preventDefault(); // フォーム送信を中止
            alert('メッセージは1000文字以内で入力してください。');
            messageInput.focus();
            return false;
        }
    });
});
