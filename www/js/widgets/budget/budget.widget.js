easyFinance.widgets.budget = function(data) {
    // шаблоны
    var tplSummary =
    '<table>\
        <tr class="profit">\
            <td class="plan">\
                <div><strong>План</strong> доходов:</div>\
                <div class="right"><span>{%planProfit%} {%currencyName%}</span></div>\
            </td>\
            <td class="fact">\
                <div><strong>Факт</strong> доходов:</div>\
                <div class="right"><span>{%realProfit%} {%currencyName%}</span></div>\
            </td>\
            <td class="balance {%profitClassName%}">\
                <div><strong>Разница</strong>:</div>\
                <div class="right"><span>{%diffProfit%} {%currencyName%}</span></div>\
            </td>\
        </tr>\
        <tr class="drain">\
            <td class="plan">\
                <div><strong>План</strong> расходов:</div>\
                <div class="right"><span>{%planDrain%} {%currencyName%}</span></div>\
            </td>\
            <td class="fact">\
                <div><strong>Факт</strong> расходов:</div>\
                <div class="right"><span>{%realDrain%} {%currencyName%}</span></div>\
            </td>\
            <td class="balance {%drainClassName%}">\
                <div><strong>Разница</strong>:</div>\
                <div class="right"><span>{%diffDrain%} {%currencyName%}</span></div>\
            </td>\
        </tr>\
    </table>'

var tplBudgetRow =
    '<tr id="{%id%}" class="{%className%}" type="{%type%}" {%parent%}>\
        <td class="w1"><a>{%catName%}</a></td>\
        <td class="w2 efTdWithTooltips" title="{%title%}">{%indicator%}</td>\
        <td class="w3">\
            <div class="cont">\
                <span class="js-planned">{%strPlan%}</span>\
                <span class="js-planning hidden">\
                    <input type="text" value="{%planValue%}"/><br/>\
                    <small><em>В календаре: {%planValueCalendar%}</em></small>\
                </span>\
            </div>\
        </td>\
        <td class="w5">{%factValue%}</td>\
        <td class="w6 {%diffClass%}">{%diffValue%}{%diffMenu%}</td>\
    </tr>';

var tplbudgetHeader =
    '<thead class="budget-header">\
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
    _updateTimeLine();

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
            _printInfo();
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
    function _printInfo(){
        var _totalInfo =  _model.returnInfo();

        var vals = {
            currencyName: easyFinance.models.currency.getDefaultCurrencyText(),
            planProfit: formatCurrency(_totalInfo.plan_profit, true, false),
            realProfit: formatCurrency(_totalInfo.real_profit, true, false),
            diffProfit: formatCurrency(_totalInfo.real_profit - _totalInfo.plan_profit, true, false),
            planDrain: formatCurrency(_totalInfo.plan_drain, true, false),
            realDrain: formatCurrency(_totalInfo.real_drain, true, false),
            diffDrain: formatCurrency(_totalInfo.plan_drain - _totalInfo.real_drain, true, false),
            profitClassName: ( _totalInfo.plan_profit < _totalInfo.real_profit) ? 'green' : 'red',
            drainClassName: (_totalInfo.real_drain < _totalInfo.plan_drain) ? 'green' : 'red'
        }

        $('#budget .budget.info').html(templetor(tplSummary, vals));
        return false;
    }
    _printInfo();

    var _categories = easyFinance.models.category.getUserCategoriesTreeOrdered();

    var elapsedPercent;

    function _printList(type, categories, parentId) { // 0 == drain
        var prefix = (type == 1) ? 'p' : 'd'; // profit / drain

        var budgets = _model.returnList()[prefix],
            budget;

        var temp = {},
            dhtml = '',

            catId,
            catName,
            catType,

            amount,
            money,
            totalAmount = 0,
            totalMoney = 0,
            totalCalendarPlan = 0,
            totalNotCalendarPlan = 0,
            calendarPlan = 0,
            notCalendarPlan = 0;

        for (var key in categories) {
            catType = categories[key].type;

            if ((type == 0 && catType < 1) || (type == 1 && catType > -1)) {
                catId = categories[key].id;
                catName = categories[key].name;

                budget = {
                    money: 0,
                    amount: 0,
                    calendar_plan: 0,
                    not_calendar_plan: 0
                }

                budget = $.extend(budget, budgets[catId]);

                if (categories[key].children.length) {
                    temp = _printList(type, categories[key].children, catId);
                }
                else {
                    temp = {
                        xhtml: '',
                        totalAmount: 0,
                        totalMoney: 0,
                        totalCalendarPlan: 0,
                        totalNotCalendarPlan: 0
                    };
                }

                totalAmount += temp.totalAmount + budget.amount;

                totalMoney += temp.totalMoney + budget.money;

                totalCalendarPlan += temp.totalCalendarPlan + budget.calendar_plan;

                totalNotCalendarPlan += temp.totalNotCalendarPlan + budget.not_calendar_plan;

                amount = temp.totalAmount + budget.amount;

                money = temp.totalMoney + budget.money;

                calendarPlan = temp.totalCalendarPlan + budget.calendar_plan;

                notCalendarPlan = temp.notCalendarPlan + budget.not_calendar_plan;

                if (amount > 0 || money !== 0) {
                    var drainprc = Math.abs(Math.round(money*100/amount));

                    var cls = parentId ? 'child' : 'parent open';
                    if (cls == 'parent open' && !temp.xhtml) {
                        cls = 'nochild';
                    }

                    var params = {
                        id: catId,
                        type: prefix,
                        parent: parentId,
                        cls: cls,
                        cat: catName,
                        plan: amount,
                        fact: money,
                        diff: Math.abs(Math.abs(amount) - Math.abs(money)),
                        calendarPlan: calendarPlan,
                        notCalendarPlan: notCalendarPlan,
                        drain: drainprc
                    };

                    dhtml += _buildTableRow(params);

                    dhtml += temp.xhtml || '';
                }
            }
        }

        return {
            xhtml: dhtml,
            totalAmount: totalAmount,
            totalMoney: totalMoney,
        	totalCalendarPlan: totalCalendarPlan,
        	totalNotCalendarPlan: totalNotCalendarPlan
        };
    }

    function _buildTableRow(params) {
        var diff = 0,
            diffClass = '',
            strPlan = '';

        if (params.plan > 0) {
            strPlan = formatCurrency(params.plan, true, false);
        }
        else {
            strPlan = (params.cls != 'parent open') ? '<FONT COLOR="#FF0000"> запланировать </FONT>' : '0';
        }

        var tooltipParams = getTooltip(params);

        var vals = {
            id: params.id,
            className: params.cls,
            type: params.type,
            parent: params.parent != undefined ? 'parent="' + params.parent + '"' : '',
            catName: shorter(params.cat, 20),
            indicator: _buildIndicatorString(tooltipParams.color, params.drain),
            strPlan: strPlan,
            planValue: formatCurrency(params.plan, true, false),
            factValue: formatCurrency(params.fact, true, false),
            diffClass: diffClass,
            diffValue: formatCurrency(diff, true, false),
            diffMenu: (params.cls == 'nochild' || params.cls == 'child') ? '<div class="menuwrapper"><div class="menu"><a title="Редактировать" class="edit">&nbsp;</a><a title="Удалить" class="remove">&nbsp;</a></div></div>' : '',
            title: tooltipParams.title
        }

        return templetor(tplBudgetRow, vals);
    }

    function getTooltip(params) {
        // calendarPlan -- все то, что в календаре, подтв. и неподтв.
        // notCalendarPlan -- все, что подверждено не в календаре
        // fact -- все подтвежденное вообще

        var budgetTotal = params.plan;
        var adhoc = parseFloat(params.notCalendarPlan || 0);
        var calendarAccepted = params.fact - adhoc;
        var calendarPlanned = params.calendarPlan - calendarAccepted;
        var daysInMonth = _getMonthDays(_currentDate);
        var currentDay = _currentDate.getDate();

        var msg = {
            drain: {
                BudgetOverhead: "<span class='danger'>Внимание! Бюджет уже превышен на <strong>{%budgetLeft%}</strong></span>",
                PositiveMargin: "<span class='ok'>Поздравляем! Вы сэкономите <strong>{%marginTotal%}</strong> к концу месяца, если сохраните текущие темпы трат</span>",
                ZeroMargin: "<span class='warning'>Будьте аккуратны: бюджет расходуется точно по плану</span>",
                ChangeGeneral: "<span class='danger'>Внимание! Вам нужно снизить траты, чтобы уложиться в план.</span> Возможные действия:<ul><li>&bull; снизить в сумме на <strong>{%marginTotal%}</strong> внеплановые траты и траты, запланированные в календаре</li>",
                ChangeAdhoc: "<li>&bull; снизить внеплановые траты на <strong>{%changeAdhoc%}</strong> в день<li>",
                ChangeCalendar: "<li>&bull; снизить запланированные в календаре траты на <strong>{%changeCalendar%}</strong></li>",
                ChangeBoth: "",
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
        var message = [],
            color,
            resultMessage;

        //оставшийся бюджет
        var budgetLeft = budgetTotal - adhoc - calendarAccepted

        //сколько останется, если будем тратить с неизменной скоростью и календарем
        var marginTotal = budgetLeft - calendarPlanned - (daysInMonth - currentDay) / currentDay * adhoc

        //насколько урезать спонтанные траты, чтобы выйти в 0
        var changeAdhoc = Math.abs(marginTotal / (daysInMonth - currentDay))
        var canChangeAdhoc = changeAdhoc < adhoc / currentDay;

        //насколько урезать календарь, чтобы выйти в 0
        var changeCalendar = Math.abs(marginTotal);
        var canChangeCalendar = changeCalendar < calendarPlanned;

        var canChangeBoth = canChangeAdhoc == canChangeCalendar;

        var budgetOverheaded = budgetLeft < 0;
        var marginZero = marginTotal == 0;
        var marginPositive = marginTotal > 0;

        var messageSrc = (params.type == 'd' ? msg.drain : msg.profit);

        if (budgetOverheaded) {
            message = messageSrc.BudgetOverhead;
        }
        else if (marginZero) {
            message = messageSrc.ZeroMargin
        }
        else if (marginPositive) {
            message = messageSrc.PositiveMargin
        }
        else {
            message = messageSrc.ChangeGeneral;
            if (canChangeAdhoc) {
                message += messageSrc.ChangeAdhoc
            }
            if (canChangeCalendar) {
                message += messageSrc.ChangeCalendar
            }
            message += messageSrc.ChangeClosing
        }

        if (params.type == 'd') {
            color = (budgetOverheaded || marginTotal < 0) ? 'red' : marginTotal == 0 ? 'yellow' : 'green';
        }
        else {
            color = (budgetOverheaded || marginTotal < 0) ? 'green' : marginTotal == 0 ? 'yellow' : 'red';
        }

        resultMessage = templetor(message, {
            budgetLeft: formatCurrencyDefault(Math.abs(budgetLeft)),
            marginTotal: formatCurrencyDefault(Math.abs(marginTotal)),
            changeAdhoc: formatCurrencyDefault(changeAdhoc),
            changeCalendar: formatCurrencyDefault(changeCalendar)
        })

        return {color: color, title: resultMessage};
    }

    function _buildIndicatorString(color, drainPercent) {
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
            height: ($budgetBody.height()) + 'px'
        });

        var days = 32 - new Date(_currentDate.getFullYear(), _currentDate.getMonth(), 32).getDate();
        $(".budgetPeriodEnd").text(days + ' ' + getMonthName(_currentDate.getMonth()).substr(0, 3));
    }

    function printBudget(){
        _updateElapsed();

        _data = _model.returnList();

        var params = null,
            drainprc,
            str = '',
            temp = _printList(1, _categories, 0);

        if (temp.totalAmount > 0) {
            drainprc = Math.abs(Math.round(temp.totalMoney * 100 / temp.totalAmount));
        }
        else {
            drainprc = 100;
        }

        str = templetor(tplbudgetHeader, {currencyName: easyFinance.models.currency.getDefaultCurrencyText()})

        params = {
            id: "profit",
            type: "p",
            cat: "Доходы",
            cls: "open",
            drain: drainprc,
            plan: temp.totalAmount,
            fact: temp.totalMoney
        };

        str += _buildTableRow(params);

        str += temp.xhtml;

        temp = _printList(0, _categories, 0);
        if (temp.totalAmount > 0) {
            drainprc = Math.abs(Math.round(temp.totalMoney * 100 / temp.totalAmount));
        }
        else {
            drainprc = 100;
        }

        params = {
            id: "drain",
            type: "d",
            cat: "Расходы",
            cls: "open",
            drain: drainprc,
            plan: temp.totalAmount,
            fact: temp.totalMoney
        };

        str += _buildTableRow(params);

        str += temp.xhtml;

        $("#budgetTimeLine").show();

        $budgetBody.html('<table style="width: 100%;" class="efTableWithTooltips">' + str + '</table>');

        _updateTimeLine();
    }

    printBudget();
    
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

    $('#budget .list a.remove').live('click', function() {
        if (confirm('Вы действительно хотите удалить бюджет по данной категории?')) {
            var id = $(this).closest('tr').attr('id');
            var type = $(this).closest('tr').attr('type');
            _model.del(_currentDate, id, type, function(){
                _printInfo();
                printBudget();
            });
        }
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
                _printInfo();
                printBudget();
            });
        }
        else if (e.keyCode == 27) {
            parentEl.find('.js-planning').addClass('hidden');
            parentEl.find('.js-planned').removeClass('hidden');
        }
    });

    return {getDate : getDate, init : init, reload : reload};
};
