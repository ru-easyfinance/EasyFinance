// {* $Id$ *}
$(window).load(function() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth();
    var event_list;
    
    $.fullCalendar.monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
    $.fullCalendar.monthAbbrevs = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
    $.fullCalendar.dayNames = ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
    $.fullCalendar.dayAbbrevs = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];

    	/**
	 * autor: CTAPbIu_MABP
	 * email: ctapbiumabp@gmail.com
	 * site: http://mabp.kiev.ua/2009/08/11/customized-datapicker/
	 * license: MIT & GPL
	 * last update: 11.08.2009
	 * version: 1.0
	 */


    /**
     * Очищаем форму
     */
    function clearForm() {
        $('form #key,#title,#date_start,#date_end,#date,#time,#count,#comment,#chain').val('');
        $('form #tr_count').hide();
        $('form #repeat option').each(function(){ 
            $(this).removeAttr('selected').removeAttr('disabled');
        });
        $('form #count option').each(function(){
            $(this).removeAttr('selected');
        });
        $('form #infinity').removeAttr('checked');
    }

    /**
     * Перед открытием формы
     * @param CalEvent el Элемент календаря, т.е. объект - событие
     */
    function beforeOpenForm(el) {
        clearForm();

        // Периодическая транзакция
        if (el.amount != 0) {
            $('#tabs').tabs('enable',1).tabs('disable',0).tabs( 'select',1);
            //$('#dialog_event').dialog('option', 'buttons', {});
            $('#pkey').val(el.id);
            $('#pchain').val(el.chain);
            $('#ptitle').val(el.title);
            dt = new Date(); dt.setTime(el.start);
            $('#pdate').val($.datepicker.formatDate('dd.mm.yy', dt));
            $('#prepeat').val(el.repeat);
            $('#pcount').val(el.count);
            $('#pcomment').val(el.comment);
            $('#pinfinity').val(el.infinity);
            $('#pamount').val(el.amount);
            if (el.infinity == 0) {
                $('#pcount').removeAttr('disabled');
            } else {
                $('#pcount').attr('disabled','disabled');
            }
        // Событие календаря
        } else {
            $('#tabs').tabs('enable',0).tabs('disable',1).tabs( 'select',0);
            $('#key').val(el.id);
            $('#chain').val(el.chain);
            $('#title').val(el.title);
            $('form').attr('action', '/calendar/edit/');
            dt = new Date(); dt.setTime(el.start);
            $('#date').val($.datepicker.formatDate('dd.mm.yy', dt));
            $('#time').val(dt.toLocaleTimeString().substr(0, 5));
            $('#repeat option').each(function(){ //@FIXME Оптимизировать
                if (el.repeat == $(this).attr('value')) {
                    $(this).attr('selected','selected');
                }
            });
            $('#count option').each(function(){ //@FIXME Оптимизировать
                if (el.count == $(this).attr('value')) {
                    $(this).attr('selected','selected');
                }
            });
            $('#comment').val(el.comment);
            if (el.infinity == 0) {
                $('.rep_type[value=1]').attr('checked','checked');
            }else if (el.infinity == 1) {
                $('#infinity').attr('checked','checked');
                $('.rep_type[value=2]').attr('checked','checked');
                $('#repeat').change();
            } else {
                $('.rep_type[value=3]').attr('checked','checked');
            }
        }
    }

    /**
     * Сохранить значение
     */
    function save() {
        //@TODO Проверить вводимые значения
        $.post(
            $('form').attr('action'),
            {
                key:        $('#key').attr('value'),
                title:      $('#title').attr('value'),
                date_start: $('#date_start').attr('value'),
                date_end:   $('#date_end').attr('value'),
                date:       $('#date').attr('value'),
                time:       $('#time').attr('value'),
                repeat:     $('#repeat option:selected').attr('value'),
                count:      $('#count').attr('value'),
                comment:    $('#comment').attr('value'),
                infinity:   $('#infinity').attr('value'),
                rep_type:   $('.rep_type[checked]').val(),
                mon:        $('#mon').attr('checked') ? 1 : 0,
                tue:        $('#tue').attr('checked') ? 1 : 0,
                wed:        $('#wed').attr('checked') ? 1 : 0,
                thu:        $('#thu').attr('checked') ? 1 : 0,
                fri:        $('#fri').attr('checked') ? 1 : 0,
                sat:        $('#sat').attr('checked') ? 1 : 0,
                sun:        $('#sun').attr('checked') ? 1 : 0
            }, function(data, textStatus){
                // data could be xmlDoc, jsonObj, html, text, etc...
                // textStatus can be one of: "timeout", "error", "notmodified", "success", "parsererror"
                for (var v in data) {
                    //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                    alert('Ошибка в ' + data[v]);
                }
                // В случае успешного добавления, закрываем диалог и обновляем календарь
                if (data.length == 0) {
                    $('#dialog_event').dialog('close');
                    $('#calendar').fullCalendar('refresh');
                }
            },
            'json'
        );
    }

    /**
     * Удалить значение
     */
    function del() {
        el = $('#calendar').fullCalendar( 'getEventsById', $('form #key').val());
        if (confirm('Удалить событие?')) {
            if (($('form #chain').val() > 0 || el[0].date < el[0].last_date || el[0].infinity == 1) &&
             confirm("Это событие не единично.\nУдалить цепочку последующих событий?")) {
                $.post('/calendar/del/', {id:$('form #key').val(), chain: $('form #chain').val()}, function(){
                    $('#dialog_event').dialog('close');
                    $('#calendar').fullCalendar('refresh');
                }, 'json');
            } else {
                $.post('/calendar/del/', {id:$('form #key').val(), chain: false}, function(){
                    $('#dialog_event').dialog('close');
                    $('#calendar').fullCalendar('refresh');
                }, 'json')
            }
        }
    }
    list[new Date(date[2], date[0] - 1, date[1])] = true;
  console.log(list);
    // Init
    $('#infinity').attr('disabled','disabled');
    $('#tr_date_start,#tr_count').hide();
    $('#date,#date_start,#date_end,#pdate').datepicker();
    $('#datepicker').datepicker({ numberOfMonths: 3 }).datepicker({beforeShowDay: function(date) {
    console.log([date, list[date]]);
    return[list[date], 'event'];
}});

    //$('textarea#comment').jGrow();
    $("#tabs,#views").tabs();
    $('#time').timePicker().mask('99:99');
    //$('#count').mask('99');
    for(i = 1; i < 31; i++){
        $('#count').append('<option>'+i+'</option>').val(i);
        $('#pcount').append('<option>'+i+'</option>').val(i);
    }
    $('#per_tabl thead input,#ev_tabl thead input').click(function(){
        if ($(this).is(':checked')) {
            $(this).closest('table').filter('tbody input').attr('checked','checked');
        } else {
            $(this).closest('table').filter('tbody input').removeAttr('checked');
        }
    });
    // Hooks
    $('#repeat').change(function(){
        $('#tr_count').show();
/*
        // Если указано повторять каждый: день, неделю, месяц, год
        if ($(this).val() == 1 || $(this).val() == 7 || $(this).val() == 30 || $(this).val() == 365) {
            $('#tr_count').show();
        // Если повторять не нужно
        } else if ($(this).val() == 0) {
            $('#tr_count').hide();
        } else{
            $('#tr_count').hide();
        }
*/
    });
    $('.rep_type').click(function(){
        if ($(this).val() == 1) {
            $('#count').removeAttr('disabled');
            $('#infinity').removeAttr('checked');
            $('#date_end').attr('disabled','disabled');
        } else if ($(this).val() == 2) {
            $('#count').attr('disabled','disabled');
            $('#infinity').attr('checked','on');
            $('#date_end').attr('disabled','disabled');
        } else {
            $('#count').attr('disabled','disabled');
            $('#infinity').removeAttr('checked');
            $('#date_end').removeAttr('disabled');
        }
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
            $('#datepicker').datepicker('setDate' , new Date(year, month-1));
            $("div.ui-datepicker-header a.ui-datepicker-prev,div.ui-datepicker-header a.ui-datepicker-next").hide();
            $('li.y_prev a').text(year-1);
            $('li.y_next a').text(year+1);
            if (month == 11) {
                $('li.m_next a').text($.fullCalendar.monthNames[0]);
            } else {
                $('li.m_next a').text($.fullCalendar.monthNames[month+1]);
            }
            if (month == 0) {
                $('li.m_prev a').text($.fullCalendar.monthNames[11]);
            } else {
                $('li.m_prev a').text($.fullCalendar.monthNames[month-1]);
            }
            $('li.cur').text(monthTitle);
        },
        dayClick: function(dayDate) {
            clearForm();
            $('#date,#date_start,#date_end').val($.datepicker.formatDate('dd.mm.yy',dayDate));
            $('form').attr('action','/calendar/add/');
            $('#dialog_event').dialog('open');
        },
        eventDragOpacity: 0.5,
        eventRevertDuration: 900,
        events: function(start, end, calback) {
            if (start.getDate() > 1) {
                s = new Date(start.getFullYear(), start.getMonth()+1, 1);
            } else {
                s = new Date(start.getFullYear(), start.getMonth(), 1);
            }
            e = new Date(s.getFullYear(), s.getMonth()+1, 1);
            $.getJSON('/calendar/events/', {
                    start: start.getTime(),
                    end:   end.getTime()
                },   function(result) {
                    // Заполняем список событий
                    $('#per_tabl tbody, #ev_tabl tbody').empty();
                    n = new Date();
                    
                    for(v in result){
                        
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
                                    t = '<td class="types"><span title="" class="t1"></span></td>';

                                }else {
                                    t ='<td class="types"><span title="" class="t2"></span></td>';
                                }
                                $('#per_tabl tbody').append(
                                    '<tr id="ev_+'+l.id+'"><td class="chk"><input type="checkbox" value="" /></td>'
                                        +'<td>'+$.datepicker.formatDate('dd.mm.yy',n)+'</td>'
                                        +'<td title="'+l.comment+'">'+l.title+'</td>'
                                        +'<td>Работа</td>'
                                        +'<td>евро</td>'
                                        +'<td>'+l.amount+'</td>'+t
                                        +'</tr>');
                            }

                        }
                    }
                    calback(result);
                }
            )
        },
        eventClick: function(calEvent, jsEvent) {
            if (calEvent.draggable === true) {
                beforeOpenForm(calEvent);
                $('#dialog_event').dialog('open');
                
            }
        },
    //eventMouseover, eventMouseout: function(calEvent, jsEvent)
    eventRender: function(calEvent, element){
        element.attr('title', calEvent.comment);
    },
//    eventDragStart, eventDragStop: function(calEvent, jsEvent, ui)
//    eventDrop: function(calEvent, dayDelta, jsEvent, ui),
//    resize: function() { alert ('resize');}
    loading: function(isLoading) {
        if (!isLoading) {
            //$('#cal_events').empty();
        }
    }
    });
    $('.full-calendar-buttons .today, .prev-month, .prev-year, .next-month, .next-year').addClass('ui-fullcalendar-button ui-button ui-state-default ui-corner-all');
    $('li.m_prev').click(function(){$('#calendar').fullCalendar('prevMonth')});
    $('li.m_next').click(function(){$('#calendar').fullCalendar('nextMonth')});
    $('li.y_prev').click(function(){$('#calendar').fullCalendar('prevYear')});
    $('li.y_next').click(function(){$('#calendar').fullCalendar('nextYear')});
    $('li.cur').click(function(){$('#calendar').fullCalendar('today')});

    $('#ui-datepicker-div').datepicker('setDate');

    $('#calendar .full-calendar-month-wrap').addClass('ui-corner-bottom');
    $("#dialog_event").dialog({
        bgiframe: true,
        autoOpen: false,
        width: 450,
        modal: true,
        buttons: {
            'Сохранить': function() {
                save();
            },
            'Отмена': function() {
                $(this).dialog('close');
            },
            'Удалить': function () {
                del();
            }
        },
        close: function() {
          $('#ui-datepicker-div').hide();
        }
    });
});