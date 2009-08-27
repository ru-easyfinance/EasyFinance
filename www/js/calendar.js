// {* $Id$ *}
$(window).load(function() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth();
    
    $.fullCalendar.monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
    $.fullCalendar.monthAbbrevs = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
    $.fullCalendar.dayNames = ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
    $.fullCalendar.dayAbbrevs = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];
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
            $('#tabs').tabs( 'select',1);
            $('#dialog_event').dialog('option', 'buttons', {});
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
            $('form #key').val(el.id);
            $('form #chain').val(el.chain);
            $('form #title').val(el.title);
            $('form').attr('action', '/calendar/edit/');
            dt = new Date(); dt.setTime(el.start);
            $('form #date').val($.datepicker.formatDate('dd.mm.yy', dt));
            $('form #time').val(dt.toLocaleTimeString().substr(0, 5));
            $('form #repeat option').each(function(){ //@FIXME Оптимизировать
                if (el.repeat == $(this).attr('value')) {
                    $(this).attr('selected','selected');
                }
            });
            $('form #count option').each(function(){ //@FIXME Оптимизировать
                if (el.count == $(this).attr('value')) {
                    $(this).attr('selected','selected');
                }
            });
            $('form #comment').val(el.comment);
            if (el.infinity == 0) {
                $('.rep_type[value=1]').attr('checked','checked');
            }else if (el.infinity == 1) {
                $('form #infinity').attr('checked','checked');
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
                key: $('form #key').attr('value'),
                title: $('form #title').attr('value'),
                date_start: $('form #date_start').attr('value'),
                date_end: $('form #date_end').attr('value'),
                date: $('form #date').attr('value'),
                time: $('form #time').attr('value'),
                repeat: $('form #repeat option:selected').attr('value'),
                count: $('form #count').attr('value'),
                comment: $('form #comment').attr('value'),
                infinity: $('form #infinity').attr('value'),
                rep_type: $('form .rep_type[checked]').val(),
                mon: $('form #mon').attr('checked') ? 1 : 0,
                tue: $('form #tue').attr('checked') ? 1 : 0,
                wed: $('form #wed').attr('checked') ? 1 : 0,
                thu: $('form #thu').attr('checked') ? 1 : 0,
                fri: $('form #fri').attr('checked') ? 1 : 0,
                sat: $('form #sat').attr('checked') ? 1 : 0,
                sun: $('form #sun').attr('checked') ? 1 : 0
            }, function(data, textStatus){
                // data could be xmlDoc, jsonObj, html, text, etc...
                // textStatus can be one of: "timeout", "error", "notmodified", "success", "parsererror"
                for (var v in data) {
                    //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                    alert('Ошибка в ' + v);
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

    // Init
    $('#infinity').attr('disabled','disabled');
    $('#tr_date_start,#tr_count').hide();
    $('#date,#date_start,#date_end,#pdate').datepicker();
    $('#datepicker').datepicker({ numberOfMonths: 3 }).datepicker('disable');
    //$('textarea#comment').jGrow();
    $("#tabs,#views").tabs();
    $('#time').timePicker().mask('99:99');
    //$('#count').mask('99');
    for(i = 1; i < 31; i++){
        $('#count').append('<option>'+i+'</option>').val(i);
        $('#pcount').append('<option>'+i+'</option>').val(i);
    }

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
        title: true,
        titleFormat: 'F Y',
        buttons: {
            prevYear:  '<<',
            prevMonth: '<',
            today:     'Сегодня',
            nextYear:  '>>',
            nextMonth: '>'
            
        },
        showTime: 'guess',
        timeFormat: "G:i",
        monthDisplay: function(year, month, monthTitle) {
            $('#datepicker').datepicker('setDate' , new Date(year, month-1));
            $("div.ui-datepicker-header a.ui-datepicker-prev,div.ui-datepicker-header a.ui-datepicker-next").hide();
            //$('div #calendar-buttons').html($('#div #full-calendar-header').html());
        },
        dayClick: function(dayDate) {
            clearForm();
            $('#date,#date_start,#date_end').val($.datepicker.formatDate('dd.mm.yy',dayDate));
            $('form').attr('action','/calendar/add/');
            $('#dialog_event').dialog('open');
        },
        eventDragOpacity: 0.5,
        eventRevertDuration: 900,
        events: function(start, end, callback) {
            // Получаем события
            $.getJSON('/calendar/events/',
                {
                    start: start.getTime(),
                    end: end.getTime()
                },   function(result) {
                    // Заполняем список событий
                    $('div#cal_events').empty();
                    n = new Date();
                    for(v in result){
                        l = result[v];
                        n.setTime(l.date*1000);
                        if (start.getTime() <= n.getTime() && n.getTime() <= end.getTime()) {
                            $('div#cal_events').append(
                                $('<div>').attr({
                                    key: l.id,
                                    title: l.comment
                                }).html("<span style='background: yellow;'>"+$.datepicker.formatDate('dd.mm',n)+"</span> "+l.title)
                            );
                        }
                    }
                    // Устанавливаем хук на щелчок
                    $('div#cal_events div').click(function(){
                        el = $('#calendar').fullCalendar( 'getEventsById', $(this).attr('key'));
                        beforeOpenForm(el[0]);
                        $('#dialog_event').dialog('open');
                    });
                    callback(result);
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
            //alert('close');
            //allFields.val('').removeClass('ui-state-error');
        }
    });
});