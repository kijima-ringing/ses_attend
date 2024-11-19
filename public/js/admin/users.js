$(function() {
    console.log('users.js loaded');
    let isSubmitting = false; // 送信フラグを追加

    $('tbody.selectable').selectable({
        filter: 'tr',
        selected: function(event, ui) {
            var user_id = $(ui.selected).attr('data-user_id');
            $('#HiddenId').val(user_id);
            ajaxGetUserInfo(user_id);
        }
    });

    function ajaxGetUserInfo(user_id) {
        $.ajax({
            type: 'GET',
            url: '/admin/users/ajax_get_user_info',
            dataType: 'json',
            data: { user_id: user_id }
        }).done(function(res) {
            console.log('Ajax Response:', res);
            $('.js-add-button').trigger('click', res);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Ajax error:', textStatus, errorThrown);
            alert('ajax通信に失敗しました');
        });
    }

    $('.js-add-button').on('click', function(event, data){
        removeErrorElement();
        isSubmitting = false; // モーダルを開く時にフラグをリセット

        if (typeof data === 'undefined') {
            $('#HiddenId').val(''); // null ではなく空文字列を使用
        } else {
            $('#HiddenId').val(data.id);
        }

        // 入力フォームをリセット
        $('input[type!="checkbox"][type!="hidden"]').val('');
        $('input[name="placeholder_email"]').val('');
        $('#email').attr('placeholder', '');
        $('.js-department-checkbox').prop('checked', false);
        $('#admin_flag').prop('checked', false);

        if (typeof data !== 'undefined') {
            $.each(data, function(key, value) {
                if (key === 'email') {
                    $('#email').attr('placeholder', value);
                    $('input[name="placeholder_email"]').val(value);
                } else {
                    $('#' + key).val(value);
                }

                if (key === 'admin_flag') {
                    $('#admin_flag').prop('checked', value == 1);
                }
            });

            var target_ids = [];
            $.each(data.departments, function(index, value) {
                target_ids.push(String(value.id));
            });

            $('.js-department-checkbox').each(function() {
                if ($.inArray($(this).val(), target_ids) !== -1) {
                    $(this).prop('checked', true);
                }
            });
        }

        changeDepartment();
    });

    function changeDepartment() {
        var target_select = $('.js-select');
        target_select.empty();
        target_select.append('<option>-</option>');

        var target_added_list = $('.js-added-list');
        target_added_list.empty();
        $('.js-department-checkbox').each(function() {
            if ($(this).prop('checked') == true) {
                var element = '<div class="offset-sm-2 col-sm-4 my-1">' + $(this).attr('data-label') + '</div>'
                    + '<div class="col-sm-1 my-1"><button class="btn btn-danger js-department-delete-btn" type="button" data-target-id="' + $(this).val() + '">x</button></div>'
                    + '<div class="col-sm-5 my-1"></div>';
                target_added_list.append(element);
                return true;
            }

            target_select.append('<option class="js-option" value="' + $(this).val() + '">' + $(this).attr('data-label') + '</option>');
        });
    }

    $('.js-department-add-btn').on('click', function() {
        if ($('.js-select').val() == '-') {
            return;
        }
        $('.js-checkbox-' + $('.js-select').val()).prop('checked', true);
        changeDepartment();
    });

    $('form').on('click', '.js-department-delete-btn', function() {
        $('.js-checkbox-' + $(this).attr('data-target-id')).prop('checked', false);
        changeDepartment();
    });

    $("#modal-form").off('submit').on('submit', function(e) {
        e.preventDefault();

        // 二重送信防止
        if (isSubmitting) {
            console.log('Form submission prevented - already submitting');
            return false;
        }

        isSubmitting = true;
        let form = $(this);
        modalAjaxPost(form);
    });

    function removeErrorElement() {
        $('.alert-danger').addClass('d-none').find('ul').empty();
    }

    function modalAjaxPost(form) {
        const submitButton = $("#user-submit");
        submitButton.prop('disabled', true);

        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('.alert-danger').removeClass('d-none').find('ul').append('<li>' + value + '</li>');
                    });
                } else {
                    $('.alert-danger').removeClass('d-none').find('ul').append('<li>予期せぬエラーが発生しました</li>');
                }
            },
            complete: function() {
                isSubmitting = false;  // リクエストが完了したらフラグをリセット
                submitButton.prop('disabled', false);
            }
        });
    }

    // モーダルが閉じられたときのイベントハンドラを追加
    $('#InputForm').on('hidden.bs.modal', function () {
        isSubmitting = false; // フラグをリセット
        removeErrorElement();
    });
});
