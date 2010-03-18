// {* $Id$ *}

function promptSingleOrChain(mode, callback){
    if (mode == "edit") {
        $("#dialogSingleOrChainEdit").html('<div style="margin: 0 14px">Это операция является частью серии операций.<br> Вы хотите изменить только выбранную операцию или все неподтверждённые операции в этой серии? </div>').dialog({
            autoOpen: false,
            width: 540,
            title: 'Редактирование календаря',
            buttons: {
                "Изменить все неподтверждённые": function(){
                    $(this).dialog('close');
                    callback(true);
                },
                "Изменить выбранную": function(){
                    $(this).dialog('close');
                    callback(false);
                }
            }
        }).dialog('open');
    }
    else 
        if (mode == "delete") {
            $("#dialogSingleOrChainDelete").html('<div style="margin: 0 14px">Это операция является частью серии операций.<br> Вы хотите удалить только выбранную операцию или все неподтверждённые операции в этой серии? </div>').dialog({
                autoOpen: false,
                width: 540,
                title: 'Удаление из календаря',
                buttons: {
                    "Удалить все неподтверждённые": function(){
                        $(this).dialog('close');
                        callback(true);
                    },
                    "Удалить выбранную": function(){
                        $(this).dialog('close');
                        callback(false);
                    }
                }
            }).dialog('open');
        }
}

$(window).load(function(){
    //    calendar = easyFinance.widgets.calendar();
    easyFinance.widgets.calendar.init();
    easyFinance.models.calendarCache.init(res.calendar.calendar)
    easyFinance.widgets.calendarList.init();
    easyFinance.models.calendarCache.reloadWidgets();
    
    $(document).bind('operationEdited operationsChainAdded operationsChainEdited operationDateEdited', function(data){
        easyFinance.models.calendarCache.clean();
        easyFinance.models.calendarCache.init(data.calendar || {});
        var date = new Date($('#calendar').fullCalendar('getDate'));
        date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
        easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
    });
    
    $(document).bind('operationsAccepted', function(data){
        easyFinance.models.calendarCache.acceptElements(data.ids || []);
        var date = new Date($('#calendar').fullCalendar('getDate'));
        date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
        easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
    });
    $(document).bind('operationsDeleted', function(data){
        easyFinance.models.calendarCache.removeElements(data.ids || []);
        var date = new Date($('#calendar').fullCalendar('getDate'));
        date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
        easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
    });
    $(document).bind('operationsChainDeleted', function(data){
        easyFinance.models.calendarCache.removeChain(data.id || 0);
        var date = new Date($('#calendar').fullCalendar('getDate'));
        date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
        easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
    });
    
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

