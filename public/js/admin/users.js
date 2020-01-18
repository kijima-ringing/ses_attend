$(window).on('load',function(){
    if ($('.alert-danger').length > 0 ) {
        $('#InputForm').modal('show');
    }
});

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
            $('.js-add-button').trigger('click', res[0]);
        }).fail(function(jqXHR,textStatus,errorThrown){
            alert('ajax通信に失敗しました');
        });
    }

    $('.js-add-button').on('click', function(event, data){
        $('.alert-danger').remove();
        $('.is-invalid').removeClass('is-invalid');
        $('input[type!="checkbox"][type!="hidden"]').val('');
        $('input[name="placeholder_email"]').val('');
        $('#email').attr('placeholder', '');
        $('.js-department-checkbox').prop('checked', false);

        if( typeof data !== 'undefined'){
            $.each(data, function(key, value){
                if (key === 'email') {
                    $('#email').attr('placeholder', value);
                    $('input[name="placeholder_email"]').val(value);
                } else {
                    $('#' + key).val(value);
                }
            });

            var target_ids = data.department_ids !== null ? data.department_ids.split(',') : '';
            $('.js-department-checkbox').each(function(){
                if ($.inArray($(this).val(), target_ids) !== -1) {
                    $(this).prop('checked', true)
                }
            });
        } else {
            $('#HiddenId').val(null);
        }
    });
});