$(document).ready(function(){
    easyFinance.widgets.profile.init();
    easyFinance.widgets.userCurrency.init();
    //easyFinance.widgets.userIntegrations.init();
    //easyFinance.widgets.userIntegrations.load(res.profile.integration.email && res.profile.integration.email != '' ? {service_mail : res.profile.integration.email} : {});

    $('.menuProfile li').click(function(){
        $('.menuProfile li').removeClass('act');
        $(this).addClass('act');
        $('.block2 .ramka3.profile').hide();
        $('.block2 .ramka3'+$(this).attr('block')).show();
    });
    if (window.location.hash.indexOf("#currency") != -1)
        $('.menuProfile #i4').click();


});