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
    '<tr id="{%id%}" class="{%className%} js-tooltipped" type="{%type%}" {%parent%} title="{%title%}">\
        <td class="w1"><a>{%catName%}</a></td>\
        <td class="w2">{%indicator%}</td>\
        <td class="w3">\
            <div class="cont">\
                <span>{%strPlan%}</span>\
                <input type="text" value="{%planValue%}"/>\
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
     * Возвращает используюмую дату в сторонние виджеты
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

                budget = budgets[catId];

                if (categories[key].children.length) {
                    temp = _printList(type, categories[key].children, catId);
                }
                else {
                    temp = {};
                }

                totalAmount += parseFloat(isNaN(temp.totalAmount) ? 0 : temp.totalAmount)
                    + parseFloat(budget ? Math.abs(budget.amount) : 0);

                totalMoney += parseFloat(isNaN(temp.totalMoney) ? 0 : temp.totalMoney)
                    + parseFloat(budget ? Math.abs(budget.money) : 0);

                totalCalendarPlan += parseFloat(isNaN(temp.totalCalendarPlan) ? 0 : temp.totalCalendarPlan)
                	+ parseFloat(budget ? Math.abs(budget.calendar_plan) : 0);

                totalNotCalendarPlan += parseFloat(isNaN(temp.totalNotCalendarPlan) ? 0 : temp.totalNotCalendarPlan)
                	+ parseFloat(budget ? Math.abs(budget.not_calendar_plan) : 0);

                amount = parseFloat(isNaN(temp.totalAmount) ? 0 : temp.totalAmount)
                    + parseFloat(budget ? Math.abs(budget.amount) : 0);

                money = parseFloat(isNaN(temp.totalMoney) ? 0 : temp.totalMoney)
                    + parseFloat(budget ? Math.abs(budget.money):0);

                calendarPlan = parseFloat(isNaN(temp.totalCalendarPlan) ? 0 : temp.totalCalendarPlan)
                	+ parseFloat(budget ? Math.abs(budget.calendar_plan) : 0);

                notCalendarPlan = parseFloat(isNaN(temp.notCalendarPlan) ? 0 : temp.notCalendarPlan)
                	+ parseFloat(budget ? Math.abs(budget.not_calendar_plan) : 0);

                if (amount > 0 || money !== 0) {
                    var drainprc = Math.abs(Math.round(money*100/amount));

                    var cls = !parentId ? 'parent open':'child';
                    if (cls == 'parent open'){
                        if (!temp.xhtml){
                            cls = 'nochild';
                        }
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
        if (isNaN(totalAmount)) {
            totalAmount = 0
        }
        if (isNaN(totalMoney)){
            totalMoney = 0
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
        var color,
            diff = 0,
            diffClass = '',
            strPlan = '';

        var title;

        params.plan = parseFloat(params.plan);
        params.fact = parseFloat(params.fact);
        params.calendarPlan = parseFloat(params.calendarPlan);
        params.notCalendarPlan = parseFloat(params.notCalendarPlan);
        

        // определяем цвет ползунков
        if (params.type == "p") { // для доходов
            diff = params.fact - params.plan;

            if (0 >= _calculateIsOverrun(params.plan, params.calendarPlan, params.notCalendarPlan)) {
                color = 'green';
                diffClass = 'sumGreen';
                title = 'Сохраняя текущий уровень доходов, вы не выйдете за рамки бюджета.'
            }
            else {
                color = 'red';
                title = 'Сохраняя текущий уровень доходов, вы не уложитесь в бюджет.'
            }
        }
        else { // для расходов
            diff = params.plan - params.fact;

            if (0 <= _calculateIsOverrun(params.plan, params.calendarPlan, params.notCalendarPlan)) {
                color = 'green';
                title = 'Сохраняя текущий уровень расходов, вы не выйдете за рамки бюджета.'
            }
            else {
                color = 'red';
                diffClass = 'sumRed';
                title = 'Сохраняя текущий уровень расходов, вы не уложитесь в бюджет.'
            }
        }

        if (params.plan > 0) {
            strPlan = formatCurrency(params.plan, true, false);
        }
        else {
            strPlan = (params.cls != 'parent open') ? '<FONT COLOR="#FF0000"> запланировать </FONT>' : '0';
        }

        var vals = {
            id: params.id,
            className: params.cls,
            type: params.type,
            parent: params.parent != undefined ? 'parent="' + params.parent + '"' : '',
            catName: shorter(params.cat, 20),
            indicator: _buildIndicatorString(color, params.drain),
            strPlan: strPlan,
            planValue: formatCurrency(params.plan, true, false),
            factValue: formatCurrency(params.fact, true, false),
            diffClass: diffClass,
            diffValue: formatCurrency(diff, true, false),
            diffMenu: (params.cls == 'nochild' || params.cls == 'child') ? '<div class="menuwrapper"><div class="menu"><a title="Редактировать" class="edit">&nbsp;</a><a title="Удалить" class="remove">&nbsp;</a></div></div>' : '',
            title: title
        }
        return templetor(tplBudgetRow, vals);
    }

    function _buildIndicatorString(color, drainPercent) {
        drainPercent = drainPercent > 100 ? 100 : drainPercent;

        return '<div class="indicator">'
                    + '<div class="' + color + '" style="width: ' + drainPercent + '%;"></div>' +
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
            drainprc = 0;
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
            drainprc = 0;
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
            $('#budget .list tr .w3 input').hide();
            $('#budget .list tr .w3 span').show();
            var v = formatCurrency($(this).find('.w3 span').text().replace(/[^0-9\.]/g,''), true, false);
            $(this).find('.w3 input').val(v == "0" ? '' : v).show().focus();
            $(this).find('.w3 span').hide();
        }
    });
    
    $('#budget .list tr input').live('keypress', function(e){
        if (e.keyCode == 13){
            var id = $(this).closest('tr').attr('id');
            var type = $(this).closest('tr').attr('type');
            var value = calculate($(this).val());
            $('#budget .list tr .w3 input').hide();
            $('#budget .list tr .w3 span').show();
            _model.edit(_currentDate, type, id, value, function() {
                _printInfo();
                printBudget();
            });
        }
        else if (e.keyCode == 27) {
            $('#budget .list tr .w3 input').hide();
            $('#budget .list tr .w3 span').show();
        }
    });

    return {getDate : getDate, init : init, reload : reload};
};
