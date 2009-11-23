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
                            <li><span/><a href="/expert/about/">О себе</a></li> \
                            <li><span/><a href="/expert/sertificates/">Сертификаты</a></li> \
                            <li><span/><a href="/expert/services/">Услуги</a></li> \
                            <li class="last"><img src="/img/i/menu3_submenu_bottom.png"/></li> \
                    </ul> \
            </li> \
        </ul> \
    </div>';

    $('#mainwrap').prepend(topmenu);

    var pathtoid = {
        '/mail/' :'m1',
        '/expert/' :'m2',
        '/expert/about' :'m2',
        '/expert/sertificates' :'m2',
        '/expert/services' :'m2'
    };

    var page_mid = pathtoid[pathName];
    $('div#mainwrap #'+page_mid).addClass('cur act').children('a').addClass('cur');
    $('.menu3 ul li ul li a[href$=' + pathName +']').parent().addClass('selected');

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