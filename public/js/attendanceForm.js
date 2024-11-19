const NORMAL_WORKING = '0';
const PAID_HOLIDAYS = '1';
const ABSENT_WORKING = '2';

const BASE_BREAK_TIME_FROM = '12:00:00';
const BASE_BREAK_TIME_TO = '13:00:00';

var companyBaseTimeFrom = $('.company').data('base_time_from') || '';
var companyBaseTimeTo = $('.company').data('base_time_to') || '';

var getAttendanceInfoUrl = $('#attendance-info-url').data('url');
$('#department-index').removeAttr('data-url');

$(function () {
    $(".dialog").click(function () {
        var parent = $(this).parent();
        var id = parent.find('.id').data('id');

        if (id) {
            $.ajax({
                type: 'GET',
                url: getAttendanceInfoUrl,
                dataType: 'json',
                data: { id: id }
            }).done(function (res) {
                let data = res.data;

                var attendance_class = data.attendance_class || NORMAL_WORKING;
                var working_time = data.working_time || companyBaseTimeFrom;
                var leave_time = data.leave_time || companyBaseTimeTo;
                var break_time_from = data.break_time_from || BASE_BREAK_TIME_FROM;
                var break_time_to = data.break_time_to || BASE_BREAK_TIME_TO;

                var memo = data.memo || '';

                $('#attendance_class').val(attendance_class);
                $('#working_time').val(working_time);
                $('#leave_time').val(leave_time);

                $('#break_time_from').val(break_time_from);
                $('#break_time_to').val(break_time_to);

                $('#memo').val(memo);
            }).fail(function () {
                alert('ajax通信に失敗しました');
            });
        } else {
            resetModalFields();
        }

        let dateInfo = parent.find('.date_info').data('date_info');
        let work_date = parent.find('.work_date').data('work_date');

        let replace = $('#delete-url').data("url").replace('work_date', work_date);
        $('#work_date').val(work_date);
        $('#delete-url').attr("href", replace);

        $('.modal-title').text(dateInfo);
        lockFieldsIfConfirmed();
        $(".modal").modal("show");
    });
});

function resetModalFields() {
    $('#attendance_class').val(NORMAL_WORKING);
    $('#working_time').val(companyBaseTimeFrom);
    $('#leave_time').val(companyBaseTimeTo);
    $('#break_time_from').val(BASE_BREAK_TIME_FROM);
    $('#break_time_to').val(BASE_BREAK_TIME_TO);
    $('#memo').val('');
}

function lockFieldsIfConfirmed() {
    let isConfirmed = $('#attendance-info-url').data('confirmed'); // 勤怠確定状態を取得
    if (isConfirmed) {
        $('#attendance_submit').prop('disabled', true);
        $('#modal-form :input').prop('disabled', true);
    } else {
        $('#attendance_submit').prop('disabled', false);
        $('#modal-form :input').prop('disabled', false);
    }
}

$(function () {
    $('#attendance_submit').click(function () {
        let form = $('#modal-form');
        modalAjaxPost(form);
    });
});




