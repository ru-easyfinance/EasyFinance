$(document).ready(function() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth();
    var listEvents = [];

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
//            start.setMonth( start.getMonth() - 1 );
//            end.setMonth( end.getMonth() + 1 );
            $.getJSON('/calendar/events/',
                {
                    start: start.getTime(),
                    end: end.getTime()
                },   function(result) {
                    callback(result);
                }
            )
        },
        eventClick: function(calEvent, jsEvent) {
            if (calEvent.draggable === true) {
                clearForm();
                $('form #key').val(calEvent.id);
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
            $('#cal_events').empty();
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
                            alert(v);
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
            }
        },
        close: function() {
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