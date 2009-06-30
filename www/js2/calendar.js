$(document).ready(function() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth();

    /**
     * Очищаем форму
     */
    function clearForm() {
        $('form #key,#title,#date_start,#date_end,#date,#time,#count,#comment').val('');
        $('form #repeat option').each(function(){ 
            $(this).removeAttr('selected')
        });
    }

    $('#datepicker').datepicker({ numberOfMonths: 3 });
    $('#datepicker').datepicker('disable');
    $('#time').timeEntry({show24Hours: true});
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
        showTime: true,
        timeFormat: "G:i",
        monthDisplay: function(year, month, monthTitle) {
            $('#datepicker').datepicker('setDate' , new Date(year, month-1));
            $("div.ui-datepicker-header a.ui-datepicker-prev,div.ui-datepicker-header a.ui-datepicker-next").hide();
        },
        dayClick: function(dayDate) {
            clearForm();
            $('#dialog_event').dialog('open');
        },
        eventDragOpacity: 0.5,
        eventRevertDuration: 900,
        events: '/calendar/events/',
        eventClick: function(calEvent, jsEvent) {
            if (calEvent.draggable === true) {
                clearForm();
                $('form #key').val(calEvent.key);
                $('form #title').val(calEvent.title);
                $('form #date_start').val($.datepicker.formatDate('dd.mm.yy',calEvent.start));
                $('form #date_end').val($.datepicker.formatDate('dd.mm.yy',calEvent.end));
                var dt = new Date(calEvent.dt*1000);
                $('form #date').val($.datepicker.formatDate('dd.mm.yy', dt));
                $('form #time').timeEntry('setTime',dt);
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
    eventRender: function(calEvent, element){ element.attr('title', calEvent.comment); }
    //eventDragStart, eventDragStop: function(calEvent, jsEvent, ui)
    //eventDrop: function(calEvent, dayDelta, jsEvent, ui),
    //resize: function() { alert ('resize');}
    });
    $('.full-calendar-buttons .today, .prev-month, .prev-year, .next-month, .next-year').addClass('ui-fullcalendar-button ui-button ui-state-default ui-corner-all');
    $('#calendar .full-calendar-month-wrap').addClass('ui-corner-bottom');

    $("#dialog_event").dialog({
        bgiframe: true,
        autoOpen: false,
        height: 400,
        width: 460,
        modal: true,
        buttons: {
            'Сохранить': function() {
                //@TODO Проверить вводимые значения
                $.post($('form').attr('action'), {
                    key: $('form #key').attr('value'),
                    title: $('form #title').attr('value'),
                    date_start: $('form #date_start').attr('value'),
                    date_end: $('form #date_end').attr('value'),
                    date: $('form #date').attr('value'),
                    time: $('form #time').attr('value'),
                    repeat: $('form #repeat option:selected').attr('value'),
                    count: $('form #count').attr('value'),
                    comment: $('form #comment').attr('value')
                }, function(){
                    $('#calendar').fullCalendar('refresh');
                }, 'json');
                $(this).dialog('close');
            },
            'Отмена': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
        //allFields.val('').removeClass('ui-state-error');
        }
    });
    
});