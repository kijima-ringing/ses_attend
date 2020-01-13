const SLASH = '/'

function isset( data ){
    return ( typeof( data ) != 'undefined' );
}

// 月選択の設定
$(function() {
    var currentTime = new Date();
    var year = currentTime.getFullYear();
    var startYear = parseInt(year) - 1
    var finalYear = parseInt(year) + 1;
    var op = {
        pattern: 'yyyy-mm',
        selectedYear: year,
        startYear: startYear,
        finalYear: finalYear,
        monthNames: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
    };
    $(".monthPick").monthpicker(op);
});



// 年月が選択されたらページ遷移
$('#year_month').change(function() {
    var url = $('#year_month_url').data('action');
    if (isset(url)) {
        if (isset($('#year_month').val())) {
            url = url.replace('year_month', $('#year_month').val());
        }
        window.location.href = url;
    } else {
        $('form').submit();
    }
});
