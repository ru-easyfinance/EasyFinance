$(document).ready(function() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth();

    /**
     * Очищаем форму
     */
    function clearForm() {
        $('form #key,#title,#date_start,#date_end,#date,#time,#count,#comment').val('');
        $('form #tr_count,#tr_date_end').hide();
        $('form #repeat option').each(function(){ 
            $(this).removeAttr('selected').removeAttr('disabled');
        });
    }
    $('#tr_date_end,#tr_date_start,#tr_count').hide();
    $('#date,#date_start,#date_end').datepicker({showOn: 'button'});
    $('#datepicker').datepicker({ numberOfMonths: 3 }).datepicker('disable');
    //$('textarea#comment').jGrow();
    $("#tabs,#views").tabs();
    $('#time').timePicker().mask('99:99');
    $('#count').mask('99'); //@FIXME
    $('#calendar').fullCalendar({
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
            nextMonth: '>',
            nextYear:  '>>'
        },
        showTime: 'guess',
        timeFormat: "G:i",
        monthDisplay: function(year, month, monthTitle) {
            $('#datepicker').datepicker('setDate' , new Date(year, month-1));
            $("div.ui-datepicker-header a.ui-datepicker-prev,div.ui-datepicker-header a.ui-datepicker-next").hide();
            $('div #calendar-buttons').html($('#div #full-calendar-header').html());
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
            $.getJSON('/calendar/events/',
                {
                    start: start.getTime(),
                    end: end.getTime()
                },   function(result) {
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
                                }).html("<span>"+$.datepicker.formatDate('dd.mm',n)+"</span> "+l.title)
                            );
                        }
                    }
                    $('div#cal_events div').click(function(){
                        clearForm();
                        var el = $('#calendar').fullCalendar( 'getEventsById', $(this).attr('key'));
                        $('form #key').val(el[0].id);
                        $('form #chain').val(el[0].chain);
                        $('form #title').val(el[0].title);
                        $('form').attr('action', '/calendar/edit/');
                        dt = new Date();
                        dt.setTime(el[0].start);
                        $('form #date').val($.datepicker.formatDate('dd.mm.yy', dt));
                        $('form #time').val(dt.toLocaleTimeString().substr(0, 5));
                        $('form #repeat option').each(function(){
                            if (el[0].repeat == $(this).attr('value')) {
                                $(this).attr('selected','selected');
                            }
                        });
                        $('form #count').val(el[0].count);
                        $('form #comment').val(el[0].comment);
                        $('#dialog_event').dialog('open');
                    });
                    callback(result);
                }
            )
        },
        eventClick: function(calEvent, jsEvent) {
            if (calEvent.draggable === true) {
                clearForm();
                $('form #key').val(calEvent.id);
                $('form #chain').val(calEvent.chain);
                $('form #title').val(calEvent.title);
                $('form').attr('action', '/calendar/edit/');
                dt = new Date();
                dt.setTime(calEvent.start);
                $('form #date').val($.datepicker.formatDate('dd.mm.yy', dt));
                $('form #time').val(dt.toLocaleTimeString().substr(0, 5));
                $('form #repeat option').each(function(){
                    if (calEvent.repeat == $(this).attr('value')) {
                        $(this).attr('selected','selected');
                    }
                });
                $('form #count').val(calEvent.count);
                $('form #comment').val(calEvent.comment);
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
                        comment: $('form #comment').attr('value')
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
            },
            'Отмена': function() {
                $(this).dialog('close');
            },
            'Удалить': function () {
                if (confirm('Удалить событие?')) {
                    if ($('form #chain').val() > 0 && confirm("Это событие не единично.\nУдалить цепочку последующих событий?")) {
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
        },
        close: function() {
            //alert('close');
            //allFields.val('').removeClass('ui-state-error');
        }
    });
    $('#repeat').change(function(eventObject){
        if ($('#repeat option:selected').attr('value') == 0) {
            $('#tr_count,#tr_date_end').hide();
        } else {
            $('#tr_count,#tr_date_end').show();
        }
    });
});