document.addEventListener('DOMContentLoaded', function() {
    const metaIsAdmin = document.querySelector('meta[name="is-admin"]');
    if (!metaIsAdmin) {
        console.error('Meta tag "is-admin" not found');
        return;
    }
    
    const currentAdminFlag = metaIsAdmin.content;
    console.log('Initial admin flag:', currentAdminFlag);
    
    // 定期的に管理者フラグをチェック（10秒ごと）
    setInterval(() => {
        fetch('/api/check-admin-flag', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Current admin flag:', currentAdminFlag);
            console.log('Received admin flag:', data.admin_flag);
            
            if (data.admin_flag !== undefined && data.admin_flag !== currentAdminFlag) {
                console.log('Admin flag changed, logging out...');
                alert('権限が変更されたため、ログアウトします。');
                const logoutForm = document.getElementById('logout-form');
                if (logoutForm) {
                    logoutForm.submit();
                } else {
                    console.error('Logout form not found');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }, 10000);
}); 