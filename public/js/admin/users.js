$(function() {
    $('tbody.selectable').selectable({
        filter: 'tr',
        selected: function( event, ui ) {
            var user_id = $(ui.selected).attr('data-user_id');
            $('#HiddenId').val(user_id);
            ajaxGetUserInfo(user_id);
        }
    });

    function ajaxGetUserInfo (user_id) {
        $.ajax({
            type:'GET',
            url:'/admin/users/ajax_get_user_info',
            dataType:'json',
            data: {user_id: user_id}
        }).done(function (res){
            $('.js-add-button').trigger('click', res);
        }).fail(function(jqXHR,textStatus,errorThrown){
            alert('ajax通信に失敗しました');
        });
    }

    function changeDepartment () {
        // プルダウンの設定(フロントはプルダウンで設定させるが実際は、チェックボックスを用いてpostする)
        var target_select = $('.js-select');
        target_select.empty();
        target_select.append('<option>-</option>');

        var target_added_list = $('.js-added-list');
        target_added_list.empty();
        $('.js-department-checkbox').each(function(){
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

    $('.js-add-button').on('click', function(event, data){
        removeErrorElement()

        $('input[type!="checkbox"][type!="hidden"]').val('');
        $('input[name="placeholder_email"]').val('');
        $('#email').attr('placeholder', '');
        $('.js-department-checkbox').prop('checked', false);

        // チェックボックスの設定
        if( typeof data !== 'undefined'){
            $.each(data, function(key, value){
                if (key === 'email') {
                    $('#email').attr('placeholder', value);
                    $('input[name="placeholder_email"]').val(value);
                } else {
                    $('#' + key).val(value);
                }
            });

            var target_ids = [];
            $.each(data.departments, function(index, value){
                target_ids.push(String(value.id));
            });

            $('.js-department-checkbox').each(function(){
                if ($.inArray($(this).val(), target_ids) !== -1) {
                    $(this).prop('checked', true)
                }
            });
        } else {
            $('#HiddenId').val(null);
        }

        changeDepartment();
    });

    $('.js-department-add-btn').on('click', function(){
        if ($('.js-select').val() == '-') {
            return;
        }
        $('.js-checkbox-' + $('.js-select').val()).prop('checked', true);
        changeDepartment();
    });

    $('form').on('click', '.js-department-delete-btn', function(){
        $('.js-checkbox-' + $(this).attr('data-target-id')).prop('checked', false);
        changeDepartment();
    });

    $(function() {
        $("#user-submit").click(function() {
            let form = $('#modal-form');

            modalAjaxPost(form);
        });
    });



});