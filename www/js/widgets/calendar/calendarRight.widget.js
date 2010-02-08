easyFinance.widgets.calendarRight = function(result){
    var ddt = new Date();
    var now = new Date();
    var ddt_day , ddt_month;
    var ddt2month=['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
    for(var v in result){
        ddt.setTime(result[v].date*1000);
        ddt_month = ddt2month[ddt.getMonth()];
        $('.calendar_block .ui-datepicker-title').each(function(){
            var month = $(this).find('span.ui-datepicker-month').text();
            var year = $(this).find('span.ui-datepicker-year').text();
            if (month == ddt_month && year == ddt.getFullYear()){
                ddt_day = ddt.getDate();
                $(this).closest('.ui-datepicker-group,.calendar').find('td a').each(function(){
                    if ($(this).text() == ddt_day){
                        if ($(this).css('priority') != 'red' ){
                            if (result[v].accept == '0' && now >= ddt){
                                $(this).css('color', 'red');
                            }else{
                                $(this).css('color', '#000000');
                            }
                        }
                        $(this).
                            attr('date',$.datepicker.formatDate('dd.mm.yy', ddt)).
                            attr('used',($(this).attr('used')||'') + '<tr><td>' + result[v].title + '<td></td>' + (result[v].type == 'e' ? ddt.toLocaleTimeString().substr(0, 5) : result[v].amount) + '</td></tr>').
                            closest('td').
                            addClass('hasEvents');
                    }
                });
            }
        });
    }
    $('.calendar_block .hasDatepicker').qtip({
        content: (''),
        position: {
            corner: {
                target: 'bottomMiddle',
                tooltip: 'topMiddle'
            }
        },
        style: 'modern'
    });
    $('.calendar_block .hasDatepicker td').removeAttr('onclick').find('a').removeAttr('href');
    $('.calendar_block .hasDatepicker td a').live('mouseover',function(){
        var content =  $(this).attr('used') ?
            (   '<div><b>' +($(this).attr('date')||'') +
                '</b></div><table class="calendar_tip">' +
                $(this).attr('used') + '</table>') :
            '<div style="color:#B4B4B4">На выбранный день ничего не запланировано</div>';
        $('.calendar_block .hasDatepicker').qtip('api').updateContent(content);
    });

}
$(document).ready(function(){
    var s = new Date();
    s.setDate(1);
    var e = new Date(s.getFullYear(), s.getMonth()+1, 1);
//    $.getJSON('/calendar/events/', {
//            start: s.getTime(),
//            end:   e.getTime()
//        },
//        function(result) {
//            easyFinance.widgets.calendarRight(result);
//        },
//        'json');
        $('.hasDatepicker td').live('click',function(){
            var ddt2month=['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
            var month = $(this).closest('.calendar, .ui-datepicker-group').find('span.ui-datepicker-month').text();
            var year = $(this).closest('.calendar, .ui-datepicker-group').find('span.ui-datepicker-year').text();
            var day = $(this).find('a').text();
            for(var tmpKey in ddt2month){
                if (ddt2month[tmpKey] == month){
                    month = tmpKey;
                    break;
                }
            }
//            _editor.load();
//            $('#cal_date').val(day + '.' + (Number(month) + 1) + '.' + year);
            var i = 1;
            var dayDate = new Date(year, month, day, 1, 1, 1, 1);
            var weekDay = dayDate.getDay();
            if (weekDay === 0){
                weekDay = 7;
            }
            calendarEditor.load();
            $('#cal_date').val($.datepicker.formatDate('dd.mm.yy',dayDate ));
            $('#week.week input').each(function(){
                if (i == weekDay){
                    $(this).attr('checked', 'checked');
                }
                i++;
            });
        });
    if (res.calendar && res.calendar.calendar){
        easyFinance.widgets.calendarRight(res.calendar.calendar);
    }
});