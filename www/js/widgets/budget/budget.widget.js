
easyFinance.widgets.budget = function(data){

    var _model = data;

    $('div.budget.list div.head tr').html(
        "<td class='w1 '>Категория</td>"
        + "<td class='w2 '>Состояние</td>"
        + "<td class='w3 '>План, " + easyFinance.models.currency.getDefaultCurrencyText() + "</td>"
        + "<td class='w4 '>Факт, " + easyFinance.models.currency.getDefaultCurrencyText() + "</td>"
        + "<td class='w5 '>Разница, " + easyFinance.models.currency.getDefaultCurrencyText() + "</td>"
        + "<td class='w6'></td>"
    );

    // #1388. обновляем данные после добавления операций
    $(document).bind('accountsLoaded', function() { reload(_currentDate) });

    function init(data){
        _model = data;
    }

    var _currentDate = new Date();

    /**
     * Перегружает виджет на заданной дате
     * @param date {date}
     */
    function reload(date){
        _model.reload(date, function() {
            _currentDate = date;
            _printDate();
            _printInfo();
            $('#budget .list.budget .body').html(printBudget());
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
        var planCls = ( _totalInfo.plan_profit >_totalInfo.plan_drain)?'green':'red';
        var realCls = (_totalInfo.real_profit>_totalInfo.real_drain)?'green':'red';
        var table =
            "<table>"+
                "<tr class='plan'>"+
                    "<td class='profit'><div><b>План</b> доходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.plan_profit, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='drain'><div><b>План</b> расходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.plan_drain, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='balance "+planCls+"'><div>Разница:</div><div class='right'><span>"+formatCurrency(_totalInfo.plan_profit-_totalInfo.plan_drain, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                "</tr>"+
                "<tr class='real'>"+
                    "<td class='profit'><div><b>Факт</b> доходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.real_profit, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='drain'><div><b>Факт</b> расходов: </div><div class='right'><span>"+formatCurrency(_totalInfo.real_drain, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
                    "<td class='balance "+realCls+"'><div>Разница:</div><div class='right'><span>"+formatCurrency(_totalInfo.real_profit-_totalInfo.real_drain, true, false)+" "+easyFinance.models.currency.getDefaultCurrencyText()+"</span><div></td>"+
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
    var date = new Date();
    var dateprc;

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

                    var b_color;

                    if (type == '1'){ // WTF? wipe out
                        b_color = (dateprc > drainprc) ? 'red' : 'green';
                    }else{
                        b_color = (dateprc < drainprc)?'red' : 'green';
                    }
                    var cls = !parentId?'parent open':'child';
                    if (cls == 'parent open'){
                        if (!temp.xhtml){
                            cls = 'nochild';
                        }
                    }

                    var amountStr = (amount > 0) ?
                            formatCurrency(amount, true, false) :
                            (cls != 'parent open' ? '<FONT COLOR="#FF0000"> запланировать </FONT>' : '0');

                    var params = {
                        id: catId,
                        type: prefix,
                        parent: parentId,
                        cls: cls,
                        cat: catName,
                        plan: amountStr,
                        drain: drainprc,
                        date: dateprc
                    };
// @TODO: replace with _buildTableRow(params)
                    dhtml += '<tr id="' + catId + '" type="' + prefix + '" parent="' + parentId + '" class="' + cls + '"><td class="w1"><a>' +
                        shorter(catName, 20) + '</a></td><td class="w2"><div class="cont"><span>' +
                        amountStr+'</span><input type="text" value="'+
                        formatCurrency(amount, true, false)+'"/></div></td><td class="w3">'
                            + _buildIndicatorString(b_color, drainprc, dateprc) +
                        '</td><td class="w5 '+ ((Math.abs(amount) < Math.abs(money))?(type == 1 ? 'sumGreen' : 'sumRed') : '') +'">' + ((type == 1) ? '' : '-') +
                        formatCurrency(Math.abs(Math.abs(amount)-Math.abs(money)), true, false) + '</td><td class="w6">' +
                        ((cls == 'nochild'||cls == 'child') ? '<div><a title="Редактировать" class="edit">&nbsp;</a><a title="Удалить" class="remove">&nbsp;</a></div>':'') +
                        '</td></tr>';
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

        if (params.type == "p") {
            color = (params.date > params.drain) ? 'red' : 'green';
        } else {
            color = (params.date < params.drain) ? 'red' : 'green';
        }

        return '<tr id="' + params.id + '" type="' + params.type + '" class="' + params.cls + '">' +
                    '<td class="w1">' +
                        '<a>' + shorter(params.cat, 20) + '</a>'
                    + '</td>' +
                    '<td class="w2">' +
                        _buildIndicatorString(color, params.drain, params.date)
                    + '</td>' +
                    '<td class="w3">' +
                        '<div class="cont">' +
                            '<span>' + params.plan + '<span>' +
                        '</div>'
                    + '</td>' +
                    '<td class="w5">' +
                        params.fact
                    + '</td>' +
                    '<td class="w6 ' + params.diffclass + '">' +
                        params.diff
                    + '</td>' +
                '</tr>';
        
        /*
        dhtml += '<tr id="' + catId + '" type="' + prefix + '" parent="' + parentId + '" class="' + cls + '"><td class="w1"><a>' +
            shorter(catName, 20) + '</a></td><td class="w2"><div class="cont"><span>' +
            amountStr+'</span><input type="text" value="'+
            formatCurrency(amount, true, false)+'"/></div></td><td class="w3">'
                + _buildIndicatorString(b_color, drainprc, dateprc) +
            '</td><td class="w5 '+ ((Math.abs(amount) < Math.abs(money))?(type == 1 ? 'sumGreen' : 'sumRed') : '') +'">' + ((type == 1) ? '' : '-') +
            formatCurrency(Math.abs(Math.abs(amount)-Math.abs(money)), true, false) + '</td><td class="w6">' +
            ((cls == 'nochild'||cls == 'child') ? '<div><a title="Редактировать" class="edit">&nbsp;</a><a title="Удалить" class="remove">&nbsp;</a></div>':'') +
            '</td></tr>';
            */
    }

    function _buildIndicatorString(color, drainPercent, datePercent) {
        return '<div class="indicator">' +
                    '<div class="' + color + '" style="width: ' + drainPercent + '%;"></div>' +
                    '<div class="strip" style="width: ' + datePercent + '%;"></div>' +
                '</div>';
    }


    function printBudget(){
        _data = _model.returnList();
        if (_currentDate.getMonth() == date.getMonth()){
            dateprc = Math.round(date.getDate()*100/_getMonthDays(date));
        }
        else{
            if(_currentDate > date){
                dateprc = 0;
            }else{
                dateprc = 100;
            }
        }

        var params = null;
        var drainprc;
        var str='';
        var temp = _printList(1, _categories, 0);
        if (temp.totalAmount > 0) {
            drainprc = Math.abs(Math.round(temp.totalMoney*100/temp.totalAmount));
        }else{
            drainprc = 0;
        }

        params = {
            id: "profit",
            type: "p",
            cat: "Доходы",
            cls: "open",
            drain: drainprc,
            date: dateprc,
            plan: formatCurrency(temp.totalAmount, true, false),
            fact: formatCurrency(Math.abs(Math.abs(temp.totalAmount)-Math.abs(temp.totalMoney)), true, false),
            diff: "",
            diffclass: (Math.abs(temp.totalAmount) < Math.abs(temp.totalMoney))?'sumGreen' : ''
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
            date: dateprc,
            plan: formatCurrency(temp.totalAmount, true, false),
            fact: formatCurrency(Math.abs(Math.abs(temp.totalAmount)-Math.abs(temp.totalMoney)), true, false),
            diff: "",
            diffclass: (Math.abs(temp.totalAmount) < Math.abs(temp.totalMoney))?'sumGreen' : ''
        };

        str += _buildTableRow(params);

        //////////////////////
        str += temp.xhtml;

        $("#budgetTimeLine").show();

        return '<table>' + str + '</table>';
    }

    $('#budget .list.budget .body').html(printBudget());
    
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
                $('#budget .list .body').html(printBudget());
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
        if (!parent || !$(this).closest('table').find('tr[parent="'+id+'"]').length){

                $('#budget .list tr .w2 input').hide();
                $('#budget .list tr .w2 span').show();
                var v = formatCurrency($(this).find('.w2 span').text().replace(/[^0-9\.]/g,''), true, false);
                $(this).find('.w2 input').val(v == "0" ? '' : v).show().focus();
                $(this).find('.w2 span').hide();
        }
    });
    
    $('#budget .list tr input').live('keypress',function(e){
        if (e.keyCode == 13){
            var id = $(this).closest('tr').attr('id');
            var type = $(this).closest('tr').attr('type');
            var value = calculate($(this).val());
            $('#budget .list tr .w2 input').hide();
            $('#budget .list tr .w2 span').show();
            _model.edit(_currentDate, type, id, value, function(){
                _printInfo();
                $('#budget .list.budget .body').html(printBudget());
            });
        }else if (e.keyCode == 27){
            $('#budget .list tr .w2 input').hide();
            $('#budget .list tr .w2 span').show();
        }

    });

    return {getDate : getDate, init : init, reload : reload};
};
