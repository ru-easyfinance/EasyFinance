$(document).ready(function() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth();
    $('#datepicker').datepicker({ numberOfMonths: 3});
    $('#datepicker').datepicker('disable');
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
           $('#dialog_event').dialog('open');
        },
        eventDragOpacity: 0.5,
        eventRevertDuration: 900,
        events: '/calendar/events/',
        eventClick: function(calEvent, jsEvent) {
            if (calEvent.draggable === true) {
                $('form #key').val(calEvent.key);
                $('form #title').val(calEvent.title);
                $('form #date_start').val($.datepicker.formatDate('dd.mm.yy',calEvent.start));
                $('form #date_end').val($.datepicker.formatDate('dd.mm.yy',calEvent.end));
                var dt = new Date(calEvent.dt*1000);
                $('form #date').val($.datepicker.formatDate('dd.mm.yy', dt));
                $('form #time').val(dt.getHours() + ':' + dt.getMinutes());
                $('form #repeat').val(calEvent.repeat);
                $('form #count').val(calEvent.count);
                $('form #comment').val(calEvent.comment);
                $('#dialog_event').dialog('open');
            }
        }
        //eventMouseover, eventMouseout: function(calEvent, jsEvent)
        //eventRender: function(calEvent, element){alert(calEvent + element)},
        //eventDragStart, eventDragStop: function(calEvent, jsEvent, ui)
        //eventDrop: function(calEvent, dayDelta, jsEvent, ui),
        //resize: function() { alert ('resize');}
    });
    $("#dialog_event").dialog({
        bgiframe: true,
        autoOpen: false,
        height: 400,
        width: 460,
        modal: true,
        buttons: {
            'Сохранить': function() {
                    //Выполняем действие по щелчку на кнопке
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