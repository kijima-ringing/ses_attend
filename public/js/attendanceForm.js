const NORMAL_WORKING = '0';
const PAID_HOLIDAYS = '1';
const ABSENT_WORKING = '2'


var companyBaseTimeFrom = isset($('.company').data('base_time_from')) ? $('.company').data('base_time_from') : '';
var companyBaseTimeTo = isset($('.company').data('base_time_to')) ? $('.company').data('base_time_to') : '';


$(function() {
    $(".dialog").click(function() {
        var parent = $(this).parent();

        var dateInfo = parent.find('.date_info').data('date_info');
        var work_date = parent.find('.work_date').data('work_date');

        var attendance_class = isset(parent.find('.attendance_class').data('attendance_class')) ? parent.find('.attendance_class').data('attendance_class') : NORMAL_WORKING;
        var working_time = isset(parent.find('.working_time').data('working_time')) ? parent.find('.working_time').data('working_time') : companyBaseTimeFrom;
        var leave_time = isset(parent.find('.leave_time').data('leave_time')) ? parent.find('.leave_time').data('leave_time') : companyBaseTimeTo;
        var break_time_from = isset(parent.find('.break_time_from').data('break_time_from')) ? parent.find('.break_time_from').data('break_time_from') : '12:00';
        var break_time_to = isset(parent.find('.break_time_to').data('break_time_to')) ? parent.find('.break_time_to').data('break_time_to') : '13:00';
        var memo = isset(parent.find('.memo').data('memo')) ? parent.find('.memo').data('memo') : '';

        $('#attendance_class').val(attendance_class);
        $('#working_time').val(working_time);
        $('#leave_time').val(leave_time);
        $('#break_time_from').val(break_time_from);
        $('#break_time_to').val(break_time_to);
        $('#memo').val(memo);
        $('#work_date').val(work_date);

        var replace = $('#delete-url').data("url").replace('work_date', work_date);
        $('#delete-url').attr("href", replace) ;

        $('.modal-title').text(dateInfo);
        removeErrorElement();
        $(".modal").modal("show");
    });
});

$(function() {
    $('#attendance_submit').click(function() {
        removeErrorElement();
        let data = getInputdata();

        if (checkAttendanceClass(data['attendance_class']) && comparisonTime(data['working_time'], data['leave_time']) && comparisonTime(data['break_time_from'], data['break_time_to'])) {
            $('form').submit();
        } else {
            addErrorElement();
        }
    });
});


$(function() {
    $('#attendance_class').blur(function() {
        console.log('uuuu');
    });
});

function getInputdata() {
    let attendance_class = $('#attendance_class').val();
    let working_time = $('#working_time').val();
    let leave_time = $('#leave_time').val();
    let break_time_from = $('#break_time_from').val();
    let break_time_to = $('#break_time_to').val();
    let memo = $('#memo').val();

    return {
        attendance_class: attendance_class,
        working_time: working_time,
        leave_time: leave_time,
        break_time_from: break_time_from,
        break_time_to: break_time_to,
        memo: memo
    }
}

function checkAttendanceClass(attendanceClass) {
    switch (attendanceClass) {
        case NORMAL_WORKING:
            return true;
            break;
        case PAID_HOLIDAYS:
            return true;
            break;
        case ABSENT_WORKING:
            return true;
            break;
        default:
            return false;
            break;

    }
}

function comparisonTime(beforeTime, afterTime) {
    if (beforeTime < afterTime) {
        return true;
    } else {
       return false;
    }
}

function removeErrorElement() {
    let errorElement = getModalErrorElement();

    errorElement.addClass('d-none')
}

function addErrorElement() {
    let errorElement = getModalErrorElement();

    errorElement.removeClass('d-none')
}

function getModalErrorElement() {
    return $('#modal-error-element')
}
