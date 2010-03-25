// {* $Id$ *}

$(window).load(function(){
    //    calendar = easyFinance.widgets.calendar();
   
    easyFinance.models.calendarCache.init(res.calendar.calendar)
    easyFinance.widgets.calendarList.init();
 	easyFinance.widgets.calendar.init();
    easyFinance.models.calendarCache.reloadWidgets();
    
//    $(document).bind('operationEdited operationsChainAdded operationsChainEdited operationDateEdited', function(data){
//        easyFinance.models.calendarCache.clean();
//        easyFinance.models.calendarCache.init(data.calendar || {});
//        easyFinance.models.calendarCache.reloadWidgets();
//    });
//    
//    $(document).bind('operationsAccepted', function(data){
//        easyFinance.models.calendarCache.acceptElements(data.ids || []);
//        easyFinance.models.calendarCache.reloadWidgets();
//    });
//    $(document).bind('operationsDeleted', function(data){
//        easyFinance.models.calendarCache.removeElements(data.ids || []);
//        easyFinance.models.calendarCache.reloadWidgets();
//    });
//    $(document).bind('operationsChainDeleted', function(data){
//        easyFinance.models.calendarCache.removeChain(data.id || 0);
//        easyFinance.models.calendarCache.reloadWidgets();
//    });
    
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
    
    $('.menu3 #m5 ul li').click(function(){
        $('.menu3 #m5 ul li').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).find('a').attr('href').indexOf('#list') != -1) {
            $('div#calend').hide();
            $('div#events').show();
        }
        else {
            $('div#calend').show();
            $('#calendar').fullCalendar('rerenderEvents');
            $('div#events').hide();
        }
    });
    
});

