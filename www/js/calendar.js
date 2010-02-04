// {* $Id$ *}
var calendar;
$(document).ready(function() {
    var model = easyFinance.models.calendar();
    //var list = easyFinance.widgets.calendarList();
    calendar = easyFinance.widgets.calendar();
    calendar.init(model);
    //list.init(model);
    //list.print();
    if (window.location.hash == '#list'){
        $('div#calend').hide();
        $('div#events').show();
    }else{
        $('div#calend').show();
        $('div#events').hide();
    }

    $('.menu3 #m5 ul li').click(function(){

    $('.menu3 #m5 ul li').removeClass('selected');
    $(this).addClass('selected');

    if ($(this).find('a').attr('href').indexOf('#list') != -1){
        $('div#calend').hide();
        $('div#events').show();
    }else{
        $('div#calend').show();
        $('#calendar').fullCalendar('refresh');
        $('div#events').hide();
    }
    });

});

