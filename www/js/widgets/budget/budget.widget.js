
easyFinance.widgets.budget = function(data){

    var _model = data;

    var $budgetBody = $('#budget .list .body');

    var _currentDate = new Date();
    var date = new Date();

    _updateElapsed();
    _updateTimeLine();

    // #1388. обновляем данные после добавления операций
    $(document).bind('accountsLoaded', function() { reload(_currentDate) });

    function init(data){
        _model = data;
    }

    /**
     * Перегружает виджет на заданной дате
     * @param date {date}
     */
    function reload(date){
        _model.reload(date, function() {
            _currentDate = date;
            _printInfo();
            printBudget();
        });
    }

    /**
     * Возвращает используюмую дату в сторонние виджеты
     */
    function getDate(){
        return new Date(_currentDate);
    }
    
    ///////////////////////////////////////////////////////////////////////////
    //                          infobar                                      //
    ///////////////////////////////////////////////////////////////////////////


    /**
     * Печатает инфо-блок
     * @todo производить расчёт из списка бюджета
     */
    function _printInfo(){
        var _totalInfo =  _model.returnInfo();
        var profitCls = ( _totalInfo.plan_profit < _totalInfo.real_profit) ? 'green' : 'red';
        var drainCls = (_totalInfo.real_drain < _totalInfo.plan_drain) ? 'green' : 'red';
        var table =
            "<table>"+
                "<tr class='profit'>"+
                    "<td class='plan'><div><b>План</b> доходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.plan_profit, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='fact'><div><b>Факт</b> доходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.real_profit, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='balance " + profitCls + "'><div><b>Разница</b>:</div><div class='right'><span>"+formatCurrency(_totalInfo.real_profit-_totalInfo.plan_profit, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                "</tr>"+
                "<tr class='drain'>"+
                    "<td class='plan'><div><b>План</b> расходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.plan_drain, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='fact'><div><b>Факт</b> расходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.real_drain, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='balance " + drainCls + "'><div><b>Разница</b>:</div><div class='right'><span>"+formatCurrency(_totalInfo.plan_drain - _totalInfo.real_drain, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                "</tr>"+
            "</table>";
        $('#budget .budget.info').html(table);
        return false;
    }
    _printInfo();

    ///////////////////////////////////////////////////////////////////////////
    //                          list                                         //
    ///////////////////////////////////////////////////////////////////////////
    var _categories = easyFinance.models.category.getUserCategoriesTreeOrdered();
    var _data;

    var elapsedPercent;

    function _getMonthDays(d){
        var m = d.getMonth();
        var t = new Date(d);
        for (var i = 29;i<32;i++)
        {
            t.setDate(i);
            if (m != t.getMonth()){return (i-1);}
        }
        return (i);
    }

    function _printList(type, categoryes, parentId)//0 drain
    {
        var prefix = (type == '1')? 'p':'d';
        var budgets = _model.returnList()[prefix];
        var key, temp = {}, catId, catName, catType, amount, money, totalAmount = 0, totalMoney = 0, dhtml ='';

        for (key in categoryes){
            catType = categoryes[key].type;
            if ((type === 0 && catType < 1)||(type == 1 && catType > -1)){
                catId = categoryes[key].id;
                catName = categoryes[key].name;

                if (categoryes[key].children.length){
                    temp = _printList(type, categoryes[key].children, catId);
                }else{
                    temp = {};
                }

                totalAmount += parseFloat(isNaN(temp.totalAmount)?0:temp.totalAmount) + parseFloat((budgets[catId]?Math.abs(budgets[catId].amount):0));
                totalMoney += parseFloat(isNaN(temp.totalMoney)?0:temp.totalMoney) + parseFloat((budgets[catId]?Math.abs(budgets[catId].money):0));

                amount =  parseFloat(isNaN(temp.totalAmount)?0:temp.totalAmount) + parseFloat((budgets[catId]?Math.abs(budgets[catId].amount):0));
                money = parseFloat(isNaN(temp.totalMoney)?0:temp.totalMoney) + parseFloat((budgets[catId]?Math.abs(budgets[catId].money):0));
                if (amount > 0 || money !==0){
                    ////////// coompil html
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
                        diff: Math.abs(Math.abs(amount)-Math.abs(money)),
                        drain: drainprc
                    };

                    dhtml += _buildTableRow(params);

                    //////////////////////
                    dhtml += temp.xhtml || '';
                }
            }
        }
        if (isNaN(totalAmount)){totalAmount = 0;}
        if (isNaN(totalMoney)){totalMoney = 0;}
        return {xhtml : dhtml,totalAmount : totalAmount,totalMoney : totalMoney};
    }

    function _buildTableRow(params) {
        var color;
        var diff = 0;
        var diffClass = '';
        var strPlan = '';

        params.plan = parseInt(params.plan);
        params.fact = parseInt(params.fact);
        

        // определяем цвет ползунков
        if (params.type == "p") {
            // для доходов
            diff = params.fact - params.plan;

            if (elapsedPercent < params.drain) {
                color = 'green';
            } else {
                color = 'red';
            }

            // при превышении доходов
            if (elapsedPercent < params.drain) {
                diffClass = 'sumGreen';
            }
        } else {
            // для расходов
            diff = params.plan - params.fact;

            if (elapsedPercent > params.drain) {
                color = 'green';
            } else {
                color = 'red';
            }

            // при превышении расходов
            if (elapsedPercent < params.drain) {
                diffClass = 'sumRed';
            }
        }

        if (params.plan > 0) {
            strPlan = formatCurrency(params.plan, true, false);
        } else {
            strPlan = (params.cls != 'parent open') ? '<FONT COLOR="#FF0000"> запланировать </FONT>' : '0';
        }

        return '<tr id="' + params.id
                + '" type="' + params.type
                + '" class="' + params.cls
                + '" ' + (params.parent !== undefined ? 'parent="' + params.parent + '"' : '') + '>'
                    + '<td class="w1">'
                        + '<a>' + shorter(params.cat, 20) + '</a>'
                    + '</td>'
                    + '<td class="w2">'
                        + _buildIndicatorString(color, params.drain)
                    + '</td>'
                    + '<td class="w3">'
                        + '<div class="cont">'
                            + '<span>' + strPlan + '</span>'
                            + '<input type="text" value="' + formatCurrency(params.plan, true, false)+ '"/>'
                        + '</div>'
                    + '</td>'
                    + '<td class="w5">'
                        + formatCurrency(params.fact, true, false)
                    + '</td>'
                    + '<td class="w6 ' + diffClass + '">'
                        + formatCurrency(diff, true, false) + ((params.cls == 'nochild' || params.cls == 'child') ? '<div class="menuwrapper"><div class="menu"><a title="Редактировать" class="edit">&nbsp;</a><a title="Удалить" class="remove">&nbsp;</a></div></div>' : '')
                    + '</td>'
                + '</tr>';
    }

    function _buildIndicatorString(color, drainPercent) {
        drainPercent = drainPercent > 100 ? 100 : drainPercent;

        return '<div class="indicator">'
                    + '<div class="' + color + '" style="width: ' + drainPercent + '%;"></div>' +
                '</div>';
    }

    function _updateElapsed() {
        elapsedPercent = 0;

        if (_currentDate.getMonth() == date.getMonth()){
            elapsedPercent = Math.round(date.getDate()*100/_getMonthDays(date));
        } else {
            if(_currentDate > date) {
                elapsedPercent = 0;
            } else {
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

        var params = null;
        var drainprc;
        var str='';
        var temp = _printList(1, _categories, 0);
        if (temp.totalAmount > 0) {
            drainprc = Math.abs(Math.round(temp.totalMoney*100/temp.totalAmount));
        }else{
            drainprc = 0;
        }

        str = '<thead class="budget-header"><tr><th class="w1">Категория</th>'
        + '<th class="w2">Состояние</th>'
        + '<th class="w3">План, ' + easyFinance.models.currency.getDefaultCurrencyText() + '</th>'
        + '<th class="w5">Факт, ' + easyFinance.models.currency.getDefaultCurrencyText() + '</th>'
        + '<th class="w6">Разница, ' + easyFinance.models.currency.getDefaultCurrencyText() + '</th></tr></thead>'

        str += '<tr><td style="height: 20px;"></td></tr>'

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

        //////////////////////
        str += temp.xhtml;

        temp = _printList(0, _categories, 0);
        if (temp.totalAmount > 0) {
            drainprc = Math.abs(Math.round(temp.totalMoney*100/temp.totalAmount));
        }else{
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

        //////////////////////
        str += temp.xhtml;

        $("#budgetTimeLine").show();

        $budgetBody.html('<table style="width: 100%;">' + str + '</table>');

        _updateTimeLine();
    }

    printBudget();
    
    ///////////////////////////////////////////////////////////////////////////
    //                          general                                      //
    ///////////////////////////////////////////////////////////////////////////

    $('#budget .list.budget .parent.open .w1 a').live('click',function(){
        var id = $(this).closest('tr').attr('id');
        var type = $(this).closest('tr').attr('type');
        $('#budget .list.budget .child[type="' + type + '"][parent="' + id + '"]').hide();
        $(this).closest('tr').removeClass('open').addClass('close');
    });

    $('#budget .list.budget .parent.close .w1 a').live('click',function(){
        var id = $(this).closest('tr').attr('id');
        var type = $(this).closest('tr').attr('type');
        $('#budget .list.budget .child[type="' + type + '"][parent="' + id + '"]').show();
        $(this).closest('tr').addClass('open').removeClass('close');
    });

    $('#budget .list a.remove').live('click',function(){
        if (confirm('Вы действительно хотите удалить бюджет по данной категории?')){
            var id = $(this).closest('tr').attr('id');
            var type = $(this).closest('tr').attr('type');
            _model.del(_currentDate, id, type, function(){
                _printInfo();
                printBudget();
            });
        }
    });

    $('#budget .list a.edit').live('click',function(){
        $(this).closest('tr').click();
    });

    $('#budget .list.budget #profit .w1 a').live('click',function(){
        $(this).closest('tr').toggleClass('open').toggleClass('close');
        if($(this).closest('tr').hasClass('open')){
            $('#budget .list.budget [type="p"][parent]').show();
            $('#budget .list.budget .parent[type="p"]').addClass('open').removeClass('close');
        }else{
            $('#budget .list.budget [type="p"][parent]').hide();
            $('#budget .list.budget .parent[type="p"]').addClass('close').removeClass('open');
        }

    });

    $('#budget .list.budget #drain .w1 a').live('click',function(){
        $(this).closest('tr').toggleClass('open').toggleClass('close');
        if($(this).closest('tr').hasClass('open')){
            $('#budget .list.budget [type="d"][parent]').show();
            $('#budget .list.budget .parent[type="d"]').addClass('open').removeClass('close');
        }else{
            $('#budget .list.budget [type="d"][parent]').hide();
            $('#budget .list.budget .parent[type="d"]').addClass('close').removeClass('open');
        }
    });

    $('#op_btn_Save').click(function(){
        setTimeout(function(){$('#budget li.cur').click();},1000);
    });

    $('#budget .list tr[parent]').live('click',function(){
        var parent = $(this).attr('parent');
        var id = $(this).attr('id');
        id = isNaN(id)?'0':id;
        if (!parent || !$(this).closest('table').find('tr[parent="'+id+'"]').length) {
                $('#budget .list tr .w3 input').hide();
                $('#budget .list tr .w3 span').show();
                var v = formatCurrency($(this).find('.w3 span').text().replace(/[^0-9\.]/g,''), true, false);
                $(this).find('.w3 input').val(v == "0" ? '' : v).show().focus();
                $(this).find('.w3 span').hide();
        }
    });
    
    $('#budget .list tr input').live('keypress',function(e){
        if (e.keyCode == 13){
            var id = $(this).closest('tr').attr('id');
            var type = $(this).closest('tr').attr('type');
            var value = calculate($(this).val());
            $('#budget .list tr .w3 input').hide();
            $('#budget .list tr .w3 span').show();
            _model.edit(_currentDate, type, id, value, function(){
                _printInfo();
                printBudget();
            });
        } else if (e.keyCode == 27) {
            $('#budget .list tr .w3 input').hide();
            $('#budget .list tr .w3 span').show();
        }
    });

    return {getDate : getDate, init : init, reload : reload};
};
