
var form = $('#modal-form');


var createAction = $('#add-department').data('action');
var updateAction = $('#edit-department').data('action');

var getDepartmentUrl = $('.department-info-url').data('url');
$('.department-info-url').removeAttr('data-url');


$('#department-index').removeAttr('data-create_validation');
$('#department-index').removeAttr('data-update_validation');
$('#add-department').removeAttr('data-action');

var postUrl = '';

$(function() {
    $("#add-department").click(function() {

        removeErrorElement();

        let action = $('#add-department').data('action');

        form.attr('action', createAction);

        form.attr('method', 'POST');

        $(".modal").modal("show");
    });
});

$(function() {
    $(".edit-department").click(function() {

        removeErrorElement();
        var self = $(this);

        var id = isset(self.data('id')) ? self.data('id') : '';

        $.ajax({
            type:'GET',
            url: getDepartmentUrl,
            dataType:'json',
            data: {id: id}
        }).done(function (res){
            let data = res.data

            var name = isset(data.name) ? data.name : '';

            $('#name').val(name);

            let replaceUpdateAction = updateAction;

            form.attr('action', replaceUpdateAction.replace('department_id', id));

            $(".modal").modal("show");
        }).fail(function(jqXHR,textStatus,errorThrown){
            alert('ajax通信に失敗しました');
        });
    });
});

$(function() {
    $("#department-submit").click(function() {
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data:$('#modal-form').serialize()
        })
            .done( (data) => {
                location.reload();
            })
            .fail( (data) => {
                addErrorElement();
            });
     });
});
