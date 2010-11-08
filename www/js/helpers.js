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
    $(".efTableWithTooltips tr, .efTdWithTooltips").efLiveTooltip();

    // для всплывающих подсказок в списках
    // используется в списке счетов в левой панеи
    $(".efListWithTooltips li.account").efLiveTooltip();


    /* Тахометры */
    if (res.informers) {
        var tahometers = $('div.flash td.informerGauge');
        tahometers.each(function(index, elem){
            var title = '<b>' + res.informers[index].title + '</b><br><br>' + res.informers[index].description
            tahometers.eq(index).attr('title', title)
        });

        $('.b-rightpanel .informerGauge').attr('title', tahometers.eq(0).attr('title'));
    }

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

    var tip_template =
        '<table>\
            <tr>\
                <th>Название</th>\
                <td>&nbsp;</td>\
                <td>{%name%}</td>\
            </tr>\
            <tr>\
                <th>Тип</th>\
                <td>&nbsp;</td>\
                <td>{%account_type%}</td>\
            </tr>\
            {%comment%}\
            <tr>\
                <th>Остаток</th>\
                <td>&nbsp;</td>\
                <td>{%leftover%}</td>\
            </tr>\
            {%reserved%}\
            <tr>\
                <th>Остаток в валюте по умолчанию</th>\
                <td>&nbsp;</td>\
                <td>{%default_currency_balance%} {%currency_name%}</td>\
            </tr>\
        </table>';
    var tip_row_template =
        '<tr>\
            <th>{%head%}</th>\
            <td>&nbsp;</td>\
            <td>{%val%}</td>\
        </tr>'

    var val = {
        'name': htmlEscape(account.name),
        'account_type': _model.getAccountTypeString(account.id),
        'comment': account.comment ? htmlEscape(templetor(tip_row_template, {head: 'Описание', val: account.comment})) : '',
        'leftover': formatCurrency(account.totalBalance) + ' ' + _model.getAccountCurrencyText(account.id),
        'reserved': account.reserve != 0 ?
            templetor(
                tip_row_template, {
                    head: 'Доступный&nbsp;остаток',
                    val: formatCurrency(account.totalBalance-account.reserve) + ' ' +_model.getAccountCurrencyText(account.id)
                }
            ) +
            templetor(
                tip_row_template, {
                    head: 'Зарезервировано',
                    val: formatCurrency(account.reserve)+ ' ' +_model.getAccountCurrencyText(account.id)
                }
            )
            : '',
        'default_currency_balance': formatCurrency(account.totalBalance * _model.getAccountCurrencyCost(account.id) / defaultCurrency.cost),
        'currency_name': defaultCurrency.text
    }

    return templetor(tip_template, val);
}

