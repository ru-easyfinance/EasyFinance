easyFinance.widgets.budget = function(data) {
    // шаблоны

    var tplBudgetRow =
        '<tr id="{%id%}" class="{%className%}" type="{%type%}" {%parent%}>\
            <td class="w1"><a>{%catName%}</a></td>\
            <td class="w2 efTdWithTooltips" title="{%title%}">{%indicator%}</td>\
            <td class="w3">\
                <div class="cont">\
                    <span class="js-planned">{%strPlan%}</span>\
                    <span class="js-planning hidden">\
                        <input type="text" value="{%planValue%}"/><br/>\
                        <em>В календаре: {%planValueCalendar%}</em>\
                    </span>\
                </div>\
            </td>\
            <td class="w5">{%factValue%}</td>\
            <td class="w6 {%diffClass%}">{%diffValue%}{%diffMenu%}</td>\
        </tr>';
    
    var tplbudgetHeader =
        '<tbody>\
            <tr class="b-income">\
                <td class="w1"><span>Доходы</span></td>\
                <td class="w2">{%incomeIndicator%}</td>\
                <td class="w3">{%planProfit%}</td>\
                <td class="w5">{%realProfit%}</td>\
                <td class="w6">{%diffProfit%}</td>\
            </tr>\
            <tr class="b-costs">\
                <td class="w1"><span>Расходы</span></td>\
                <td class="w2">{%costsIndicator%}</td>\
                <td class="w3">{%planDrain%}</td>\
                <td class="w5">{%realDrain%}</td>\
                <td class="w6">{%diffDrain%}</td>\
            </tr>\
        </tbody>\
        <thead class="budget-header">\
            <tr>\
                <th class="w1">Категория</th>\
                <th class="w2">Состояние</th>\
                <th class="w3">План, {%currencyName%}</th>\
                <th class="w5">Факт, {%currencyName%}</th>\
                <th class="w6">Разница, {%currencyName%}</th>\
            <tr>\
        </thead>\
        <tr><td style="height: 20px;"><!-- чтобы вместить линеечку от "1 ... 31 окт" --></td></tr>';

    var _model = data;

    var $budgetBody = $('#budget .list .body');

    var _currentDate = new Date();
    var date = new Date();

    _updateElapsed();

    $(document).bind('accountsLoaded', function() {reload(_currentDate)}); // обновляем данные после добавления операций

    function init(data) {
        _model = data;
    }

    /**
     * Перегружает виджет на заданной дате
     * @param date {date}
     */
    function reload(date) {
        _model.reload(date, function() {
            _currentDate = date;
            printBudget();
        });
    }

    /**
     * Возвращает используемую дату в сторонние виджеты
     */
    function getDate() {
        return new Date(_currentDate);
    }

    /**
     * Возвращает число дней в месяце
     */
    function _getMonthDays(d) {
        var m = d.getMonth();
        var t = new Date(d);
        for (var i = 29; i < 32; i++) {
            t.setDate(i);
            if (m != t.getMonth()){
                return (i-1)
            }
        }
        return (i);
    }

    /**
     * Печатает инфо-блок
     */
    function printBudget() {
        var _totalInfo =  _model.getTotal(),
            str = renderArticlesTree(_model.getArticlesTree()),
            profitClassName = ( _totalInfo.planProfit < _totalInfo.realProfit) ? 'green' : 'red',
            drainClassName = (_totalInfo.realDrain < _totalInfo.planDrain) ? 'green' : 'red',
            vals = {
                currencyName: easyFinance.models.currency.getDefaultCurrencyText(),

                planProfit: formatCurrency(_totalInfo.planProfit, true, false),
                realProfit: formatCurrency(_totalInfo.realProfit, true, false),
                diffProfit: formatCurrency(_totalInfo.realProfit - _totalInfo.planProfit, true, false),
                planDrain: formatCurrency(_totalInfo.planDrain, true, false),
                realDrain: formatCurrency(_totalInfo.realDrain, true, false),
                diffDrain: formatCurrency(_totalInfo.planDrain - _totalInfo.realDrain, true, false),

                profitClassName: profitClassName,
                drainClassName: drainClassName,

                incomeIndicator: renderIndicator(profitClassName, _model.getArticlesTree()[0].getCompletePercent()),
                costsIndicator: renderIndicator(drainClassName, _model.getArticlesTree()[1].getCompletePercent())
            }

        _updateElapsed();

        $("#budgetTimeLine").show();

        $budgetBody.html(
            '<table style="width: 100%;" class="efTableWithTooltips">'
            + utils.templator(tplbudgetHeader, vals) 
            + str + '</table>');

        _updateTimeLine();

        return false;
    }

    var _categories = easyFinance.models.category.getUserCategoriesTreeOrdered();

    var elapsedPercent;


    function renderArticlesTree(articlesTree, parentId) {
        var tail = '',
            article,
            vals = {},
            diffClass = '',
            className = '',
            id;

        for (var articleIndex in articlesTree) {
            article = articlesTree[articleIndex];
            if (article.isEmpty() && !article.isTopLevel()) {
                continue;
            }

            var recomend = article.getRecomendation(getDate());

            if (article.isProfit) {
                diffClass = recomend.budgetOverheaded ? 'sumGreen' : 'sumRed';
            }
            else {
                diffClass = recomend.budgetOverheaded ? 'sumRed' : 'sumGreen';
            }

            if (parseInt(parentId)) {
                className = 'child';
            }
            else if (!article.children.length) {
                className = 'nochild'
            }
            else if (!article.isTopLevel()) {
                className = 'parent open'
            }
            else {
                className = 'open'
            }

            if (article.id == 'd') {
                id = 'drain'
            }
            else if (article.id == 'p') {
                id = 'profit'
            }
            else {
                id = article.id
            }
            
            vals = {
                id: id,
                catName: article.name,
                type: article.isProfit ? 'p' : 'd',
                parent: parentId != undefined ? 'parent="' + parentId + '"' : '',
                className: className,

                strPlan: formatCurrency(article.getPlan(), true, false),
                planValue: article.getPlan(),
                planValueCalendar: article.getTotalCalendar(),
                factValue: formatCurrency(article.getFact(), true, false),

                diffValue: formatCurrency(recomend.budgetLeft, true, false),
                diffMenu: article.isEditable() ? '<div class="menuwrapper"><div class="menu"><a title="Редактировать" class="edit"></a></div></div>' : '',
                diffClass: diffClass,

                title : renderRecommendation(article),

                indicator: renderIndicator(recomend.color, article.getCompletePercent())
            }

            tail += utils.templator(tplBudgetRow, vals);

            tail += renderArticlesTree(article.children, article.id)
        }


        return tail;
    }

    function renderRecommendation(article) {
        if (article.isEmpty()) {
            return "<span class='warning'>Ничего не запланировано</span>"
        }

        var rec = article.getRecomendation(getDate());

        var msg = {
            drain: {
                BudgetOverhead: "<span class='danger'>Внимание! Бюджет уже превышен на <strong>{%budgetLeft%}</strong></span>",
                PositiveMargin: "<span class='ok'>Поздравляем! Вы сэкономите <strong>{%marginTotal%}</strong> к концу месяца, если сохраните текущие темпы трат</span>",
                ZeroMargin: "<span class='warning'>Будьте аккуратны: бюджет расходуется точно по плану</span>",
                ChangeGeneral: "<span class='danger'>Внимание! Вам нужно снизить траты, чтобы уложиться в план.</span> Возможные действия:<ul>",
                ChangeAdhoc: "<li>&bull; снизить внеплановые траты на <strong>{%changeAdhoc%}</strong> в день<li>",
                ChangeCalendar: "<li>&bull; снизить запланированные в календаре траты на <strong>{%changeCalendar%}</strong></li>",
                ChangeBoth: "<li>&bull; снизить в сумме на <strong>{%marginTotal%}</strong> внеплановые траты и траты, запланированные в календаре</li>",
                ChangeClosing: "</ul>"
            },
            profit: {
                BudgetOverhead: "<span class='ok'>Поздравляем! Вы уже перевыполнили бюджет на <strong>{%budgetLeft%}</strong></span>",
                PositiveMargin: "<span class='danger'>Внимание! Вы недополучите <strong>{%marginTotal%}</strong> за этот месяц при текущих темпах доходов</span>",
                ZeroMargin: "<span class='warning'>Бюджет наполняется точно по плану</span>",
                ChangeGeneral: "<span class='ok'>Так держать! Вы перевыполните бюджет на <strong>{%marginTotal%}</strong> при текущих темпах доходов</span>",
                ChangeAdhoc: "",
                ChangeCalendar: "",
                ChangeBoth: "",
                ChangeClosing: ""
            }
        }
        var message = [];

        var messageSrc = (article.isProfit ? msg.profit : msg.drain);

        if (rec.budgetOverheaded) {
            message = messageSrc.BudgetOverhead;
        }
        else if (rec.marginZero) {
            message = messageSrc.ZeroMargin
        }
        else if (rec.marginPositive) {
            message = messageSrc.PositiveMargin
        }
        else {
            message = messageSrc.ChangeGeneral;

            rec.changeAdhocOnlyIsEnough && (message += messageSrc.ChangeAdhoc);
            rec.changeCalendarOnlyIsEnough && (message += messageSrc.ChangeCalendar);
            rec.needChangeBoth && (message += messageSrc.ChangeBoth);

            message += messageSrc.ChangeClosing
        }

        return utils.templator(message, {
            budgetLeft: formatCurrencyDefault(Math.abs(rec.budgetLeft)),
            marginTotal: formatCurrencyDefault(Math.abs(rec.marginTotal)),
            changeAdhoc: formatCurrencyDefault(Math.abs(rec.changeAdhoc)),
            changeCalendar: formatCurrencyDefault(Math.abs(rec.changeCalendar))
        });
    }

    function renderIndicator(color, drainPercent) {
        drainPercent = drainPercent > 100 ? 100 : drainPercent;
        var width;

        if (drainPercent == 0) {
            width = "1px";
        }
        else {
            width = drainPercent + "%"
        }

        return '<div class="indicator">'
                    + '<div class="' + color + '" style="width: ' + width + ';"></div>' +
                '</div>';
    }

    /**
     * @param plan сумма запанированная в бюджете
     * @param calendarPlan сумма запанированная в календаре
     * @param notCalendarPlan сумма не запанированная в бюджете, но потраченная
     * @return -1 если план меньше факта, 0 если равен и 1 если больше
     */
    function _calculateIsOverrun(plan, calendarPlan, notCalendarPlan) {
    	var elapsed = 0;
    	if (_currentDate.getMonth() == date.getMonth()) {
            elapsed = date.getDate() / _getMonthDays(date);
        }
        else {
            elapsed = (_currentDate > date) ? 0 : 1;
        }

    	var a = (plan - calendarPlan) * elapsed;
    	var b = notCalendarPlan;
    	return (a == b ? 0 : (a < b ? -1 : 1) );
    }

    function _updateElapsed() {
        elapsedPercent = 0;

        if (_currentDate.getMonth() == date.getMonth()) {
            elapsedPercent = Math.round(date.getDate() * 100 / _getMonthDays(date));
        }
        else {
            if (_currentDate > date) {
                elapsedPercent = 0;
            }
            else {
                elapsedPercent = 100;
            }
        }
    }

    function _updateTimeLine() {
        $('div.timeline').removeClass('hidden');
        $("#budgetTimeLine").css({
            left: elapsedPercent + '%',
            height: $budgetBody.height() - 50 + 'px'
        });

        var days = 32 - new Date(_currentDate.getFullYear(), _currentDate.getMonth(), 32).getDate();
        $(".budgetPeriodEnd").text(days + ' ' + getMonthName(_currentDate.getMonth()).substr(0, 3));
    }

    ///////////////////////////////////////////////////////////////////////////
    //                          general                                      //
    ///////////////////////////////////////////////////////////////////////////

    $('#budget .list.budget .parent.open .w1 a').live('click', function() {
        var id = $(this).closest('tr').attr('id');
        var type = $(this).closest('tr').attr('type');
        $('#budget .list.budget .child[type="' + type + '"][parent="' + id + '"]').hide();
        $(this).closest('tr').removeClass('open').addClass('close');
    });

    $('#budget .list.budget .parent.close .w1 a').live('click', function() {
        var id = $(this).closest('tr').attr('id');
        var type = $(this).closest('tr').attr('type');
        $('#budget .list.budget .child[type="' + type + '"][parent="' + id + '"]').show();
        $(this).closest('tr').addClass('open').removeClass('close');
    });

    $('#budget .list a.edit').live('click', function() {
        $(this).closest('tr').click();
    });

    $('#budget .list.budget #profit .w1 a').live('click', function() {
        $(this).closest('tr').toggleClass('open').toggleClass('close');
        if ($(this).closest('tr').hasClass('open')) {
            $('#budget .list.budget [type="p"][parent]').show();
            $('#budget .list.budget .parent[type="p"]').addClass('open').removeClass('close');
        }
        else {
            $('#budget .list.budget [type="p"][parent]').hide();
            $('#budget .list.budget .parent[type="p"]').addClass('close').removeClass('open');
        }
    });

    $('#budget .list.budget #drain .w1 a').live('click', function() {
        $(this).closest('tr').toggleClass('open').toggleClass('close');
        if ($(this).closest('tr').hasClass('open')) {
            $('#budget .list.budget [type="d"][parent]').show();
            $('#budget .list.budget .parent[type="d"]').addClass('open').removeClass('close');
        }
        else {
            $('#budget .list.budget [type="d"][parent]').hide();
            $('#budget .list.budget .parent[type="d"]').addClass('close').removeClass('open');
        }
    });

    $('#op_btn_Save').click(function() {
        setTimeout(function(){$('#budget li.cur').click();}, 1000);
    });

    $('#budget .list tr[parent]').live('click', function() {
        var parent = $(this).attr('parent');
        var id = $(this).attr('id');
        id = isNaN(id) ? '0' : id;

        if (!parent || !$(this).closest('table').find('tr[parent="'+id+'"]').length) {
            $(this).find('.w3 .js-planned').addClass('hidden');
            $(this).find('.w3 .js-planning').removeClass('hidden');
            var v = formatCurrency($(this).find('.w3 .js-planned').text().replace(/[^0-9\.]/g,''), true, false);
            $(this).find('.w3 input').val(v == "0" ? '' : v).show().focus();
        }
    });
    
    $('#budget .list tr input').live('keypress', function(e) {
        if (e.keyCode == 13) {
            var parentEl = $(this).closest('.w3');
            var tr = parentEl.parent('tr');

            var id = tr.attr('id');
            var type = tr.attr('type');
            var value = calculate(this.value);

            parentEl.find('.js-planning').addClass('hidden');
            parentEl.find('.js-planned').removeClass('hidden');

            _model.edit(_currentDate, type, id, value, function() {
                reload(_currentDate)
            });
        }
        else if (e.keyCode == 27) {
            parentEl.find('.js-planning').addClass('hidden');
            parentEl.find('.js-planned').removeClass('hidden');
        }
    });

    return {getDate : getDate, init : init, reload : reload};
};
