// {* $Id$ *}

$(window).load(function(){
    easyFinance.widgets.calendarMonthPicker.init("#calendarMonthPicker");

    // #1444. обновляем граф. календарь или журнал событий
    // при изменении месяца в виджете выбора месяца
    $(document).bind('monthPickerChanged', function(e) {
        $('#calendar').fullCalendar('gotoDate', e.startDate);
        easyFinance.models.calendarCache.reloadWidgets('');
    });

    easyFinance.models.calendarCache.init(res.calendar.calendar)
    easyFinance.widgets.calendarList.init();
    easyFinance.widgets.calendar.init();
    easyFinance.models.calendarCache.reloadWidgets();

    // показываем просроченные операции
    easyFinance.widgets.calendarOverdue.init('#divCalendarOverdue', easyFinance.models.accounts);

    if (window.location.hash == '#list') {
        showEventsJournal();
    } else {
        showFullCalendar();
    }

    $('#linkMainMenuEventsJournal,#calendarModeTable,#AshowEvents').click(showEventsJournal);

    $('#linkMainMenuEventsCalendar,#calendarModeCalendar').click(showFullCalendar);
});

function showFullCalendar() {
    // показываем большой графический календарь
    $('.menu3 #m5 ul li').removeClass('selected');
    $('#linkMainMenuEventsCalendar').parent().addClass('selected');

    $('div#calend').show();
    $('#calendar').fullCalendar('rerenderEvents');
    $('div#events').hide();

    $("#calendarModeCalendar").addClass("active");
    $("#calendarModeTable").removeClass("active");
}

function showEventsJournal() {
    // #1351. показываем журнал событий
    $('.menu3 #m5 ul li').removeClass('selected');
    $('#linkMainMenuEventsJournal').parent().addClass('selected');

    $('div#calend').hide();
    $('div#events').show();

    $("#calendarModeTable").addClass("active");
    $("#calendarModeCalendar").removeClass("active");
}