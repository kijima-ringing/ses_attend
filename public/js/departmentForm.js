
var form = $('#modal-form');

var createValidationUrl = $('#department-index').data('create_validation');
var updateValidationUrl = $('#department-index').data('update_validation');

var createAction = $('#add-department').data('action');
var updateAction = $('#edit-department').data('action');


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

        postUrl = createValidationUrl;

        $(".modal").modal("show");
    });
});

$(function() {
    $(".edit-department").click(function() {

        removeErrorElement();
        var self = $(this);

        var id = isset(self.data('id')) ? self.data('id') : '';
        var name = isset(self.data('name')) ? self.data('name') : '';

        $('#id').val(id);
        $('#name').val(name);

        let replaceUpdateAction = updateAction;

        form.attr('action', replaceUpdateAction.replace('department_id', id));

        form.attr('method', 'GET');

        replaceUpdateUrl = updateValidationUrl;
        postUrl = replaceUpdateUrl.replace('department_id', id);

        $(".modal").modal("show");
    });
});

$(function() {
    $("#department-submit").click(function() {
        $.ajax({
            url: postUrl,
            type: 'POST',
            data:{
                'id':$('#id').val(),
                'name':$('#name').val(),
                '_token':$('#_token').val()
            }
        })
            .done( (data) => {
                $('form').submit();
            })
            .fail( (data) => {
                addErrorElement();
            });
    });
});
