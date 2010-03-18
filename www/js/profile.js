$(document).ready(function(){
	easyFinance.widgets.userIntegrations.init();
	easyFinance.widgets.userIntegrations.load();
})
//to screen
$(document).ready(function(){
	$('.menuProfile li').click(function(){
        $('.menuProfile li').removeClass('act');
        $(this).addClass('act');
        $('.block2 .ramka3.profile').hide();
        $('.block2 .ramka3'+$(this).attr('block')).show();
    });
    easyFinance.widgets.profile.init();
	
	
    if (window.location.hash.indexOf("#currency") != -1)
        $('.menuProfile #i4').click();
});
$(document).ready(function(){
    easyFinance.widgets.userCurrency.init();
});