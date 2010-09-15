// #1338. глобальный хелпер для работы с тултипами в таблицах и списках
(function($) {
    // div с подсказкой
	var tooltip = null;

    // элемент, для которого показывается подсказка
    var source = null;

    // текущее содержимое подсказки
    var title = '';

    $.fn.efLiveTooltip = function(settings) {
        //var defaults = {'a': 'b'};
        //if (settings) $.extend(defaults, settings);
        var $this = $(this);

        if (!tooltip) {
            // создаём контейнер для подсказки
            tooltip = $('<div id="efLiveTooltip"></div>').hide().appendTo('body');
        }

        $(this).live('mouseover', function(event){
            var $node = $(this);

            title = $node.attr("title");
            $node.attr("title", "");

            if (title != "") {
                tooltip
                    .css({
                        top: $node.offset().top + $node.height() + 5,
                        left: $node.offset().left + $node.width()/2 - 100
                    })
                    .html('<div>' + title + '</div>')
                    .show();

                //setTimer();
            } else {
                tooltip.hide();
            }
        }).live('mouseout', function(event){
            var $node = $(this);

            $node.attr('title', title);
            tooltip.hide();
            //stopTimer();
        });

         /* Delay the fade-in animation of the tooltip */
         setTimer = function() {
             $this.showTipTimer = setInterval(showTip, 600);
         }

         stopTimer = function() {
             clearInterval($this.showTipTimer);
         }

         /* Stop timer and start fade-in animation */
         showTip = function(){
             stopTimer();
             tooltip.animate({"top": "+=20px", "opacity": "toggle"}, 200);
         }

        return this;
    };
})(jQuery);

$(document).ready(function() {
    // для всплывающих подсказок в таблицах
    // используется в журнале операций и журнале счетов
    $(".efTableWithTooltips tr").efLiveTooltip();

    // для всплывающих подсказок в списках
    // используется в списке счетов в левой панеи
    $(".efListWithTooltips li.account").efLiveTooltip();

    /* Тахометры (by Jet, тикет #552) */
    if (res.informers) {
        $('#divInformer0').attr("title", '<b>' + res.informers[0].title + '</b><br><br>' + res.informers[0].description);

        for (var i=0;i<5;i++) {
            $('#tdInformer' + i).attr("title", '<b>' + res.informers[i].title + '</b><br><br>' + res.informers[i].description);
        }
    }
    /* EOF Тахометры */

    // используем bassistance tooltip
    // для всех элементов с классом efTooltip,
    // содержание подсказок задаётся в атрибуте title
    $(".efTooltip").tooltip({
        showURL: false,
        showBody: " - ",
        extraClass: 'tahometers-tooltip'
    });

    /////////////////////////////////////////////////Cтили
    /**
     * Классическое отображение подсказки
     * реализовано как будующая фича
     */
    $.fn.qtip.styles.modern = { // Last part is the name of the style
        width: 200,
        background: '#FFFFFF',
        color: '#303030',
        textAlign: 'center',
        show: 'mouseover',
        hide: 'mouseout',
        border: {
            width: 1,
            radius: 2,
            color: '#666666'
        },
        style: {
            name: 'grey' // Inherit from preset style
        }
    }

    /**
     * Старое "голубое отображение"
     */
    $.fn.qtip.styles.mystyle = { // Last part is the name of the style
        width: 200,
        background: '#abcdef',
        color: 'black',
        textAlign: 'center',
        show: 'mouseover',
        hide: 'mouseout',
        border: {
            width: 3,
            radius: 2,
            color: '#f5f5ff'
        },
        tip: 'bottomRight',
        style: {
            name: 'blue' // Inherit from preset style
        }
    }

    //if($.cookie('tooltip') != '0'){
        //initToltips('modern')
    //}
})

// готовит содержимое всплывающей подсказки с информацией о счёте
function getAccountTooltip(accountId) {
    var _model = easyFinance.models.accounts;
    var defaultCurrency = easyFinance.models.currency.getDefaultCurrency();
    var account = _model.getAccounts()[accountId];

    var tip = '<table>';
    tip +=  '<tr><th> Название </th><td>&nbsp;</td><td>'+ htmlEscape(account.name) + '</td></tr>';
    tip +=  '<tr><th> Тип </th><td>&nbsp;</td><td>'+ _model.getAccountTypeString(account.id) + '</td></tr>';
    if (account.comment) {
        tip +=  '<tr><th> Описание </th><td>&nbsp;</td><td>'+ account.comment + '</td></tr>';
    }
    tip +=  '<tr><th> Остаток </th><td>&nbsp;</td><td>'+ formatCurrency(account.totalBalance) + ' ' + _model.getAccountCurrencyText(account.id) + '</td></tr>';

    if (account.reserve != 0){
        var delta = (formatCurrency(account.totalBalance-account.reserve));
        tip +=  '<tr><th> Доступный&nbsp;остаток </th><td>&nbsp;</td><td>'+delta+' '+_model.getAccountCurrencyText(account.id)+'</td></tr>';
        tip +=  '<tr><th> Зарезервировано </th><td>&nbsp;</td><td>'+formatCurrency(account.reserve)+' '+_model.getAccountCurrencyText(account.id)+'</td></tr>';
    }

    tip +=  '<tr><th> Остаток в валюте по умолчанию</th><td>&nbsp;</td><td>'+
        formatCurrency(account.totalBalance * _model.getAccountCurrencyCost(account.id) / defaultCurrency.cost) + ' '+defaultCurrency.text+'</td></tr>';

    tip += '</table>';

    return tip;
}

