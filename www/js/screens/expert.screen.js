/**
 * @desc Expert Screen
 * @author Andrey [Jet] Zharikov
 */

var expertModel = null;

var widgetMail = null;
var widgetExpertEditInfo = null;
var widgetExpertEditPhoto = null;
var widgetExpertEditServices = null;
var widgetExpertEditCertificates = null;

$(document).ready(function(){
    // init top menu
    var topmenu = '<div class="menu3 expert"> \
        <ul class="dropdown"> \
            <li id="m1"> \
                    <a id="linkMail" href="#"></a> \
            </li> \
            <li id="m2"> \
                    <a id="linkExpert" href="#"></a> \
                    <ul> \
                            <li><span/><a id="linkAbout" href="#">О себе</a></li> \
                            <li><span/><a id="linkCertificates" href="#">Сертификаты</a></li> \
                            <li><span/><a id="linkServices" href="#">Услуги</a></li> \
                            <li class="last"><img src="/img/i/menu3_submenu_bottom.png"/></li> \
                    </ul> \
            </li> \
        </ul> \
    </div>';

    $('#mainwrap').prepend(topmenu);

    var pathtoid = {
        '/mail/' :'m1',
        '/expert/' :'m1',
        '/expert/about' :'m2',
        '/expert/sertificates' :'m2',
        '/expert/services' :'m2'
    };

    var page_mid = pathtoid[pathName];
    var _pathName = pathName;

    //$('div#mainwrap #'+page_mid).addClass('cur act').children('a').addClass('cur');
    //$('.menu3 ul li ul li a[href$=' + pathName +']').parent().addClass('selected');

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

    initExpertMenu();

    // init mail widget first
    $('div#mainwrap #m1').addClass('cur act');
    widgetMail = easyFinance.widgets.mail;
    widgetMail.init('#widgetMail', easyFinance.models.mail);

    // preload expert model for future usage
    easyFinance.models.expert.load(function(model){
        expertModel = model;
    });
})

function initExpertMenu() {
    $('#m1').click(showMail);

    $('#m2').click(showAbout);
    $('#linkAbout').click(showAbout);
    $('#linkServices').click(showServices);
    $('#linkCertificates').click(showCertificates);
}

function showMail(){
    // switch to mail widget
    $('#m2').removeClass('cur');
    $('#m1').addClass('cur');
    
    $('.dropdown li ul li').removeClass('selected');

    $('.block2 .ramka3').hide();
    $('#divMail').show();

    // prevent event bubbling
    return false;
}

function showAbout(){
    // switch to about & photo widgets
    $('#m1').removeClass('cur');
    $('#m2').addClass('cur');

    $('.dropdown li ul li').removeClass('selected');
    $('#linkAbout').parent().addClass('selected');

    $('.block2 .ramka3').hide();
    $('#divExpertEditPhoto').show();
    $('#divExpertEditInfo').show();

    if (!widgetExpertEditInfo) {
        widgetExpertEditInfo = easyFinance.widgets.expertEditInfo;
        widgetExpertEditInfo.init('#widgetExpertEditInfo', expertModel);
    }

    if (!widgetExpertEditPhoto) {
        widgetExpertEditPhoto = easyFinance.widgets.expertEditPhoto;
        widgetExpertEditPhoto.init('#widgetExpertEditPhoto', expertModel);
    }

    // prevent event bubbling
    return false;
}

function showCertificates(){
    // switch to certificates
    $('#m1').removeClass('cur');
    $('#m2').addClass('cur');

    $('.dropdown li ul li').removeClass('selected');
    $('#linkCertificates').parent().addClass('selected');

    if (!widgetExpertEditCertificates) {
        widgetExpertEditCertificates = easyFinance.widgets.expertEditCertificates;
        widgetExpertEditCertificates.init('#widgetExpertEditCertificates', expertModel);
    }

    $('.block2 .ramka3').hide();
    $('#divExpertEditCertificates').show();

    // prevent event bubbling
    return false;
}

function showServices() {
    // switch to services
    $('div#mainwrap #m1').removeClass('cur');
    $('div#mainwrap #m2').addClass('cur');

    $('.block2 .ramka3').hide();
    $('#divExpertEditServices').show();

    if (!widgetExpertEditServices) {
        widgetExpertEditServices = easyFinance.widgets.expertEditServices;
        widgetExpertEditServices.init('#widgetExpertEditServices', expertModel);
    }

    $('.dropdown li ul li').removeClass('selected');
    $('#linkServices').parent().addClass('selected');

    // prevent event bubbling
    return false;
}