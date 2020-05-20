const NORMAL_WORKING = '0';
const PAID_HOLIDAYS = '1';
const ABSENT_WORKING = '2';

const BASE_BREAK_TIME_FROM = '12:00:00'
const BASE_BREAK_TIME_TO = '13:00:00'

var companyBaseTimeFrom = isset($('.company').data('base_time_from')) ? $('.company').data('base_time_from') : '';
var companyBaseTimeTo = isset($('.company').data('base_time_to')) ? $('.company').data('base_time_to') : '';

var getAttendanceInfoUrl = $('#attendance-info-url').data('url');
$('#department-index').removeAttr('data-url');

$(function() {
    $(".dialog").click(function() {
        var parent = $(this).parent();

        var id = parent.find('.id').data('id');

        if(isset(id)) {
            $.ajax({
                type:'GET',
                url: getAttendanceInfoUrl,
                dataType:'json',
                data: {id: id}
            }).done(function (res){
                let data = res.data;

                var attendance_class = isset(data.attendance_class) ? data.attendance_class : NORMAL_WORKING;
                var working_time = isset(data.working_time) ? data.working_time : companyBaseTimeFrom;
                var leave_time = isset(data.leave_time) ? data.leave_time : companyBaseTimeTo;
                var break_time_from = isset(data.break_time_from) ? data.break_time_from : BASE_BREAK_TIME_FROM;
                var break_time_to = isset(data.break_time_to) ? data.break_time_to : BASE_BREAK_TIME_TO;

                var memo = isset(data.memo) ? data.memo : '';

                $('#attendance_class').val(attendance_class);
                $('#working_time').val(working_time);
                $('#leave_time').val(leave_time);

                $('#break_time_from').val(break_time_from);
                $('#break_time_to').val(break_time_to);

                $('#memo').val(memo);
            }).fail(function(jqXHR,textStatus,errorThrown){
                alert('ajax通信に失敗しました');
            });
        } else {

            var attendance_class =  NORMAL_WORKING;
            var working_time =  companyBaseTimeFrom;
            var leave_time =  companyBaseTimeTo;
            var break_time_from =  BASE_BREAK_TIME_FROM;
            var break_time_to = BASE_BREAK_TIME_TO;
            var memo =  '';

            $('#attendance_class').val(attendance_class);
            $('#working_time').val(working_time);
            $('#leave_time').val(leave_time);

            $('#break_time_from').val(break_time_from);
            $('#break_time_to').val(break_time_to);

            $('#memo').val(memo);
        }


        let dateInfo = parent.find('.date_info').data('date_info');
        let work_date = parent.find('.work_date').data('work_date');

        let replace = $('#delete-url').data("url").replace('work_date', work_date);

        $('#work_date').val(work_date);

        $('#delete-url').attr("href", replace) ;

        $('.modal-title').text(dateInfo);
        removeErrorElement();
        $(".modal").modal("show");
    });
});

$(function() {
    $('#attendance_submit').click(function() {
        let form = $('#modal-form');
        modalAjaxPost(form);

    });
});



