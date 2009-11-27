// {* $Id$ *}
$(document).ready(function() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth();

    $.fullCalendar.monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
    $.fullCalendar.monthAbbrevs = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
    $.fullCalendar.dayNames = ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
    $.fullCalendar.dayAbbrevs = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];


    $(window).bind("saveSuccess", function(e, data){
        $('#calendar').fullCalendar('refresh');
    });


    // Init
    $('#infinity').attr('disabled','disabled');
    $('#tr_date_start,#tr_count').hide();
    $('#date,#date_start,#date_end,#pdate').datepicker();
    $('#datepicker').datepicker({numberOfMonths: 3}).datepicker();

    $("#views").tabs();

    $('#per_tabl thead input,#ev_tabl thead input').click(function(){
        if ($(this).is(':checked')) {
            $(this).closest('table').filter('tbody input').attr('checked','checked');
        } else {
            $(this).closest('table').filter('tbody input').removeAttr('checked');
        }
    });

    $('button#remove_event').live('click',function(){
        $.post('/calendar/del/',
            {id:    $('#cal_key').val(),
            chain:  $('#cal_chain').val()},
            function(data){
                $('#op_dialog_event').dialog('close');
                $('#calendar').fullCalendar('refresh');
            },
            'json');
        $(this).remove();
    });

    $('#calendar').fullCalendar({
        weekStart: 1,
        draggable: false,
        year: y,
        month: m,
        fixedWeeks: false,
        abbrevDayHeadings: true,
        title: false,
        titleFormat: 'F Y',
        buttons: false,
        showTime: 'guess',
        timeFormat: "G:i",
        monthDisplay: function(year, month, monthTitle) {
            var _$cal = $('#views');

            $('#datepicker').datepicker('setDate' , new Date(year, month-1));
            $("a.ui-datepicker-prev,a.ui-datepicker-next",'div.ui-datepicker-header').hide();
            $('.calendar_block .calendar a.ui-datepicker-prev ').css('display','block').css('left','15px');
            $('.calendar_block .calendar a.ui-datepicker-next ').css('display','block').css('right','15px');
            _$cal.find('li.y_prev a').text(year-1);
            _$cal.find('li.y_next a').text(year+1);
            if (month == 11) {
                _$cal.find('li.m_next a').text($.fullCalendar.monthNames[0]);
            } else {
                _$cal.find('li.m_next a').text($.fullCalendar.monthNames[month+1]);
            }
            if (month == 0) {
                _$cal.find('li.m_prev a').text($.fullCalendar.monthNames[11]);
            } else {
                _$cal.find('li.m_prev a').text($.fullCalendar.monthNames[month-1]);
            }
            _$cal.find('li.cur').text(monthTitle);
        },
        dayClick: function(dayDate) {
            $('#op_addtocalendar_but').click();
            $('#op_dialog_event input').val('');
            $('#op_dialog_event textarea').val('').text('');
            $('#cal_date,#cal_date_end','#op_dialog_event').val($.datepicker.formatDate('dd.mm.yy',dayDate));
        },
        eventDragOpacity: 0.5,
        eventRevertDuration: 900,
        events: function(start, end, calback) {
            var s;
            if (start.getDate() > 1) {
                s = new Date(start.getFullYear(), start.getMonth()-1, 1);
            } else {
                s = new Date(start.getFullYear(), start.getMonth(), 1);
            }
            var e = new Date(s.getFullYear(), s.getMonth()+4, 1);

            $.getJSON('/calendar/events/', {
                    start: s.getTime(),
                    end:   e.getTime()
                },   function(result) {
                    $('.hasDatepicker td a').css('color', '#e4e4e4').click(function(){return false;});
                    // Заполняем список событий
                    $('#per_tabl tbody, #ev_tabl tbody').empty();
                    var n = new Date();
                    var l,t='';
                    var ddt = new Date();
                    var ddt_day , ddt_month;
                    var i = 0
                    var ddt2month=['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
                    for(var v in result){
                        
                        
                        l = result[v];
                        n.setTime(l.date*1000);
                        if (n >= s && n < e) {
                            // Если это событие
                            if (l.amount == 0) {
                                $('#ev_tabl tbody').append(
                                    '<tr id="ev_+'+l.id+'"><td class="chk"><input type="checkbox" value="" /></td>'
                                        +'<td>'+$.datepicker.formatDate('dd.mm.yy',n)+'</td>'
                                        +'<td><b>'+n.toLocaleTimeString().substr(0, 5)+'</b></td>'
                                        +'<td>'+l.title+'</td>'
                                        +'<td>'+l.comment+'</td>'
                                        +'</tr>');
                            // Если периодическая транзакция
                            } else {
                                if (l.amount > 0) {
                                    t ='<td class="types"><span title="" class="t2"></span></td>';
                                }else {
                                    t = '<td class="types"><span title="" class="t1"></span></td>';
                                }
                                $('#per_tabl tbody').append(
                                    '<tr id="ev_+'+l.id+'"><td class="chk"><input type="checkbox" value="" /></td>'
                                        +'<td>'+$.datepicker.formatDate('dd.mm.yy',n)+'</td>'
                                        +'<td title="'+l.comment+'">'+l.title+'</td>'
                                        +'<td>'+l.comment+'</td>'
                                        +'<td>'+l.amount+'</td>'+t
                                        +'</tr>');
                            }
                            }
///////////////////////////////hard huk .. so ..//////////////////////////////////
                        ddt.setTime(result[v].date*1000);
                        //get calendar month
                        ddt_month = ddt2month[ddt.getMonth()];
                        $('.hasDatepicker .ui-datepicker-month').each(function(){

                            if ($(this).text()==ddt_month)
                            {

                                //search daty
                                ddt_day = ddt.getDate();
                                $(this).closest('.ui-datepicker-group').find('td a').each(function(){
                                   

                                    if ($(this).text() == ddt_day)
                                    {
                                        $(this).css('color', '#000000');
                                    }
                                })
                            }
                        })
                        /////////////////////////////////////////////////////////////////
                        
                    }
                    calback(result);
                }
            )
        },
        eventClick: function(calEvent, jsEvent) {
            if (calEvent.draggable === true) {
                $('#op_addtocalendar_but').click();
                var dt = new Date();
                var el = calEvent;
                if (el.amount != 0) {//@depricate переписать
                    $('#cal_mainselect #periodic').click();
                    $('#cal_key').val(el.id);//hidden
                    $('#cal_chain').val(el.chain);//hidden
                    $('#cal_title').val(el.title);
                    dt.setTime(el.start);
                    $('#cal_date').val($.datepicker.formatDate('dd.mm.yy', dt));
                    $('#cal_repeat').val(el.repeat);
                    $('#cal_count').val(el.count);
                    $('#cal_comment').val(el.comment);
                    $('#cal_infinity').val(el.infinity);
                    $('#cal_amount').val(el.amount);
                    if (el.infinity == 0) {
                        $('#cal_count').removeAttr('disabled');
                    } else {
                        $('#cal_count').attr('disabled','disabled');
                    }
                // Событие календаря
                } else {
                    $('#cal_mainselect #event').click();
                    $('#cal_key').val(el.id);

                    $('#cal_chain').val(el.chain);
                    $('#cal_title').val(el.title);
                    //$('form').attr('action', '/calendar/edit/');
                    dt.setTime(el.start);
                    $('#cal_date').val($.datepicker.formatDate('dd.mm.yy', dt));
                    $('#cal_time').val(dt.toLocaleTimeString().substr(0, 5));
                    $('#cal_repeat option').each(function(){ //@FIXME Оптимизировать
                        if (el.repeat == $(this).attr('value')) {
                            $(this).attr('selected','selected');
                        }
                    });
                    $('#cal_count option').each(function(){ //@FIXME Оптимизировать
                        if (el.count == $(this).attr('value')) {
                            $(this).attr('selected','selected');
                        }
                    });
                    $('#cal_comment').val(el.comment);
                    if (el.infinity == 0) {
                        $('.rep_type[value=1]').attr('checked','checked');
                    }else if (el.infinity == 1) {
                        $('#cal_infinity').attr('checked','checked');
                        $('.rep_type[value=2]').attr('checked','checked');
                        $('#cal_repeat').change();
                    } else {
                        $('.rep_type[value=3]').attr('checked','checked');
                    }
                }
                $('#cal_mainselect').attr('disabled','disabled');
                if ($('#cal_repeat').val()=="7"){
                    $('#week.week').closest('.line').show();
                    $('.repeat').closest('.line').show()
                }else if($('#cal_repeat').val()=="0"){
                    $('#week.week').closest('.line').hide();
                    $('.repeat').closest('.line').hide()
                }else{
                    $('#week.week').closest('.line').hide();
                    $('.repeat').closest('.line').show()
                }
                $('div.ui-dialog-buttonpane').append('<button type="button" id="remove_event" class="ui-state-default ui-corner-all">Удалить</button>');
                
            }
        },
    eventRender: function(calEvent, element){
        element.attr('title', calEvent.comment);
    },
    loading: function(isLoading) {
    }
    });

    var _$cal = $('#views');
    _$cal.find('.full-calendar-buttons .today, .prev-month, .prev-year, .next-month, .next-year').addClass('ui-fullcalendar-button ui-button ui-state-default ui-corner-all');
    _$cal.find('li.m_prev').click(function(){$('#calendar').fullCalendar('prevMonth')});
    _$cal.find('li.m_next').click(function(){$('#calendar').fullCalendar('nextMonth')});
    _$cal.find('li.y_prev').click(function(){$('#calendar').fullCalendar('prevYear')});
    _$cal.find('li.y_next').click(function(){$('#calendar').fullCalendar('nextYear')});
    _$cal.find('li.cur').click(function(){$('#calendar').fullCalendar('today')});

    //$('#ui-datepicker-div').datepicker('setDate');

    $('#calendar .full-calendar-month-wrap').addClass('ui-corner-bottom');

});

