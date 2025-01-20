//メッセージ入力欄のバリデーション
$(document).ready(function() {
    const chatContainer = $('.chat-container');
    // 最後のメッセージIDを取得
    let lastMessageId = $('.message').last().data('message-id') || 0;

    // メッセージフォームの送信イベントを処理
    $('#message-form').on('submit', function(e) {
        e.preventDefault();
        
        const messageInput = $('#message-input');
        const message = messageInput.val().trim();
        const roomId = $(this).data('room-id');
        const isAdmin = $('meta[name="is-admin"]').attr('content') === '1';
        const baseUrl = isAdmin ? '/admin' : '/user';
        
        if (!message) {
            alert('メッセージを入力してください。');
            messageInput.focus();
            return false;
        }
        
        if (message.length > 1000) {
            alert('メッセージは1000文字以内で入力してください。');
            messageInput.focus();
            return false;
        }

        $.ajax({
            url: `${baseUrl}/chat/${roomId}/send`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                message: message
            },
            success: function(response) {
                if (response.success) {
                    messageInput.val('');
                }
            },
            error: function() {
                alert('メッセージの送信に失敗しました。');
            }
        });
    });

    // チャットコンテナを最下部にスクロール
    if (chatContainer.length) {
        chatContainer.scrollTop(chatContainer[0].scrollHeight);
    }
    
    // 3秒ごとに新しいメッセージをチェック
    setInterval(function() {
        const roomId = $('#message-form').data('room-id');
        const isAdmin = $('meta[name="is-admin"]').attr('content') === '1';
        const baseUrl = isAdmin ? '/admin' : '/user';
        
        $.ajax({
            url: `${baseUrl}/chat/${roomId}/check-new-messages`,
            method: 'GET',
            data: {
                last_message_id: lastMessageId
            },
            success: function(response) {
                if (response.messages && response.messages.length > 0) {
                    response.messages.forEach(function(message) {
                        // 既に表示されているメッセージは追加しない
                        if (!$(`.message[data-message-id="${message.id}"]`).length) {
                            appendMessage(message);
                            // 最後のメッセージIDを更新
                            lastMessageId = Math.max(lastMessageId, message.id);
                        }
                    });
                    // 新しいメッセージが追加された場合のみスクロール
                    chatContainer.scrollTop(chatContainer[0].scrollHeight);
                }
            }
        });
    }, 3000);

    // メッセージを追加する関数
    function appendMessage(message) {
        const messageHtml = `
            <div class="message ${message.is_current_user ? 'message-admin' : 'message-user'}" data-message-id="${message.id}">
                <div class="message-content">
                    ${message.message}
                </div>
                <div class="message-info">
                    <span class="message-time">
                        ${message.created_at}
                    </span>
                </div>
            </div>
        `;
        $('.chat-container').append(messageHtml);
    }
});
