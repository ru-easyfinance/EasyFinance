// {* $Id$ *}

$(window).load(function(){
    //    calendar = easyFinance.widgets.calendar();
   
    easyFinance.models.calendarCache.init(res.calendar.calendar)
    easyFinance.widgets.calendarList.init();
 	easyFinance.widgets.calendar.init();
    easyFinance.models.calendarCache.reloadWidgets();
    
    // показываем просроченные операции
    easyFinance.widgets.calendarOverdue.init('#divCalendarOverdue', easyFinance.models.accounts);
    
    if (window.location.hash == '#list') {
        $('div#calend').hide();
        $('div#events').show();
        $('.menu3 ul li ul li a[href$=/calendar/#list]').parent().addClass('selected');
    }
    else {
        $('div#calend').show();
        $('div#events').hide();
        $('.menu3 ul li ul li a[href$=/calendar/#calend]').parent().addClass('selected');
    }
    
    $('#linkMainMenuEventsJournal,#AshowEvents').click(function(){
        // #1351. показываем журнал событий
        $('.menu3 #m5 ul li').removeClass('selected');
        $('#linkMainMenuEventsJournal').parent().addClass('selected');

        $('div#calend').hide();
        $('div#events').show();
    });

    $('#linkMainMenuEventsCalendar').click(function(){
        // показываем большой графический календарь
        $('.menu3 #m5 ul li').removeClass('selected');
        $('#linkMainMenuEventsCalendar').parent().addClass('selected');

        $('div#calend').show();
        $('#calendar').fullCalendar('rerenderEvents');
        $('div#events').hide();
    });

});

