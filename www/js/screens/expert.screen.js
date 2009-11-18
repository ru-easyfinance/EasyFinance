/**
 * @desc Expert Screen
 * @author Andrey [Jet] Zharikov
 */

$(document).ready(function(){
    // init top menu
    var topmenu = '<div class="menu3 expert"> \
        <ul class="dropdown"> \
            <li id="m1"> \
                    <a href="/mail/"></a> \
            </li> \
            <li id="m2"> \
                    <a href="/expert/"></a> \
                    <ul> \
                            <li><span/><a href="/accounts/">Счета</a></li> \
                            <li><span/><a href="/operation/">Операции</a></li> \
                            <li><span/><a href="/category/">Категории</a></li> \
                            <li class="last"><img src="/img/i/menu3_submenu_bottom.png"/></li> \
                    </ul> \
            </li> \
            <li id="m3"> \
                    <a href="/budget/"></a> \
                    <ul> \
                            <li><span/><a href="/budget/">Бюджет</a></li> \
                            <li><span/><a href="/targets/">Фин. цели</a></li> \
                            <li class="last"><img src="/img/i/menu3_submenu_bottom.png"/></li> \
                    </ul> \
            </li> \
        </ul> \
    </div>';

    $('#mainwrap').prepend(topmenu);

    var pathtoid = {
        '/accounts/' :'m2',
        '/budget/':'m3',
        '/calendar/':'m5',
        '/category/':'m2',
        '/experts/':'m6',
        '/info/':'m1',
        '/mail/':'m0',
        '/operation/':'m2',
        '/periodic/':'m5',
        '/profile/':'m0',
        '/targets/':'m3',
        '/report/':'m4'};

    var page_mid = pathtoid[pathName];
    $('div#mainwrap #'+page_mid).addClass('cur act').children('a').addClass('cur');
    $('.menu3 ul li ul li a[href*=' + pathName +']').parent().addClass('selected');

    // код для переключения внешнего вида вкладок
    $('.dropdown').children('li')
        .mouseover(
            function(){
                // act - делает вкладку активной
                // over - показывает подменю
                $(this).addClass('act over');

                // если мышь на закладке раздела, отличного от текущего
                // подсвечиваем вкладку текущего раздела зелёным
                if (!$(this).hasClass('cur'))
                    $(this).siblings('.cur').removeClass('act');
            })
        .mouseout(
            function(){
                // скрываем подменю
                $(this).removeClass('over');

                // если мышь на закладке раздела, отличного от текущего
                // делаем вкладку текущего раздела активной
                if (!$(this).hasClass('cur')){
                    $(this).removeClass('act');
                    $(this).siblings('.cur').addClass('act');
                }
            }
    );

    // init widgets
    easyFinance.models.expert.load(function(model){
        easyFinance.widgets.expertEditInfo.init('#widgetExpertEditInfo', model);
        easyFinance.widgets.expertEditPhoto.init('#widgetExpertEditPhoto', model);
        easyFinance.widgets.expertEditCertificates.init('#widgetExpertEditCertificates', model);
        easyFinance.widgets.expertEditServices.init('#widgetExpertEditServices', model);
    });
})