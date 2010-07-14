$(document).ready(function(){
    easyFinance.widgets.profile.init();
    easyFinance.widgets.userCurrency.init();
    /*  Denis 25.06.2010
        инициализацию виджета operationReminders я перенёс внутрь инициализации profile.widget
        т.к. она выполниться только после того как отработает ajax-запрос в widget.profile.init
    */
    //easyFinance.widgets.operationReminders.init("#reminders", easyFinance.models.user, "profile");

    $('.menuProfile li').click(function(){
        $('.menuProfile li').removeClass('act');
        $(this).addClass('act');
        $('.block2 .ramka3.profile').hide();
        $('.block2 .ramka3'+$(this).attr('block')).show();
    });

    if (window.location.hash.indexOf("#currency") != -1) {
        $('.menuProfile #i4').click();
    }
        
    if (window.location.hash.indexOf("#reminders") != -1) {
        $('.menuProfile #i6').click();
    }
});
