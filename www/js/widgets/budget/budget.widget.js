
easyFinance.widgets.budget = function(data){
    
    var _model = data;

    function init(data){
        _model = data
    }

    ///////////////////////////////////////////////////////////////////////////
    //                      datebar && reload                                //
    ///////////////////////////////////////////////////////////////////////////

    var _currentDate = new Date()

    /**
     * Возвращает используюмую дату в сторонние виджеты
     */
    function getDate(){
        return _currentDate;
    }
    /**
     * Печатает текст ссылок для дата-менялки
     */
    function _printDate()
    {
        var monthName = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь']
        var year = _currentDate.getFullYear();
        var month = _currentDate.getMonth();
        var bar = $('#budget.budget .month_cal');
        $(bar).find('li.y_prev a').text(year-1);
        $(bar).find('li.y_next a').text(year+1);
        $(bar).find('li.m_prev a').text(monthName[month-1]);
        $(bar).find('li.m_next a').text(monthName[month+1]);
        $(bar).find('li.cur').text(monthName[month]+' '+year);
    }

    _printDate();

    /**
     * Меняем дату
     */
    $('#budget.budget .month_cal li').click(function(){
        var year = _currentDate.getFullYear();
        var month = _currentDate.getMonth();
        if ($(this).hasClass('y_prev')){
            _currentDate.setYear(year-1);
        }
        else if($(this).hasClass('m_prev')){
            _currentDate.setMonth(month-1);
        }
        else if($(this).hasClass('m_next')){
            _currentDate.setMonth(month+1);
        }
        else if($(this).hasClass('y_next')){
            _currentDate.setYear(year+1);
        }
        _printDate();
        //loadBudget()

    })
    ///////////////////////////////////////////////////////////////////////////
    //                          infobar                                      //
    ///////////////////////////////////////////////////////////////////////////
    var _totalInfo =  _model.returnInfo();

    /**
     * Печатает инфо-блок
     * @todo производить расчёт из списка бюджета
     */
    function _printInfo()
    {
        var planCls = (_totalInfo.plan_profit>_totalInfo.plan_drain)?'green':'red';
        var realCls = (_totalInfo.real_profit>_totalInfo.real_drain)?'green':'red';
        var table = 
            "<table>"+
                "<tr class='plan'>"+
                    "<td class='profit'><b>План</b> доходов: "+formatCurrency(_totalInfo.plan_profit)+" руб.</td>"+
                    "<td class='drain'><b>План</b> расходов: "+formatCurrency(_totalInfo.plan_drain)+" руб.</td>"+
                    "<td class='balance "+planCls+"'>Остаток:"+formatCurrency(_totalInfo.plan_profit-_totalInfo.plan_drain)+" руб.</td>"+
                "</tr>"+
                "<tr class='real'>"+
                    "<td class='profit'><b>План</b> доходов: "+formatCurrency(_totalInfo.real_profit)+" руб.</td>"+
                    "<td class='drain'><b>План</b> расходов: "+formatCurrency(_totalInfo.real_drain)+" руб.</td>"+
                    "<td class='balance "+realCls+"'>Остаток:"+formatCurrency(_totalInfo.real_profit-_totalInfo.real_drain)+" руб.</td>"+
                "</tr>"+
            "</table>";
        $('#budget .budget.info').html(table);
    }

    //_printInfo()
    
    ///////////////////////////////////////////////////////////////////////////
    //                          list                                         //
    ///////////////////////////////////////////////////////////////////////////
    var _categories = easyFinance.models.category.getUserCategoriesTree()
    var _data;
    var date = new Date();
    var dateprc;

    function _getMonthDays(d){
        var m = d.getMonth()
        for (var i = 29;i<32;i++)
        {
            d.setDate(i)
            if (m != d.getMonth()){return (i-1)}
        }
        return (i)
    }

    function _printList(type, categoryes, parentId)//0 drain
    {
        var prefix = (type == '1')? 'p':'d'; 
        var budgets = _data[prefix]
        var key, temp = {}, catId, catName, catType, amount, money, totalAmount = 0, totalMoney = 0, dhtml ='';


        for (key in categoryes){
            catType = categoryes[key].type;
            if ((type == 0 && catType < 1)||(type == 1 && catType > -1)){
                catId = categoryes[key].id;
                catName = categoryes[key].name;

                if (categoryes[key].children.count){
                    temp = _printList(type, categoryes[key].children, catId)
                }else{
                    temp = {};
                }


                totalAmount += parseFloat(temp.totalAmount) + parseFloat((budgets[catId]?Math.abs(budgets[catId].amount):0));
                totalMoney += parseFloat(temp.totalMoney) + parseFloat((budgets[catId]?Math.abs(budgets[catId].money):0));

                amount =  parseFloat(temp.totalAmount) + parseFloat((budgets[catId]?Math.abs(budgets[catId].amount):0));
                money = parseFloat(temp.totalMoney) + parseFloat((budgets[catId]?Math.abs(budgets[catId].money):0));
                if (amount > 0){
                    ////////// coompil html
                    var drainprc = Math.abs(Math.round(money*100/amount))
                    
                    
                    var b_color;


                    if (type == '1'){
                        b_color =(dateprc > drainprc)?'red':'green';
                    }else{
                        b_color =(dateprc < drainprc)?'red':'green';
                    }

                    dhtml += '<tr id="'+catId+'" type="'+prefix+'" parent="'+parentId+'">\n\
                                <td class="w1">\n\
                                    <a>' + catName + '</a>\n\
                                </td>\n\
                                <td class="w2">\n\
                                    <div class="cont">\n\
                                        <input type="text" value="'+formatCurrency(amount)+'" readonly="readonly" />\n\
                                    </div>\n\
                                </td>\n\
                                <td class="w3">\n\
                                    <div class="indicator">\n\
                                        <div class="'+b_color+'" style="width: '+drainprc+'%;"></div>\n\
                                        <div class="strip" style="width: '+dateprc+'%;"></div>\n\
                                    </div>\n\
                                </td>\n\
                                <td class="w4">'+formatCurrency(Math.abs(amount)-Math.abs(money))+'</td>\n\
                            </tr>';
                    //////////////////////
                    dhtml += temp.xhtml;
                }
            }
        }
        if (isNaN(totalAmount)){totalAmount = 0}
        if (isNaN(totalMoney)){totalMoney = 0}
        return {xhtml : dhtml,totalAmount : totalAmount,totalMoney : totalMoney };
    }


    function printBudget(){
        _data = _model.returnList()
        if (_currentDate.getMonth() == date.getMonth()){
            dateprc = Math.round(date.getDate()*100/_getMonthDays(date))
        }
        else{
            dateprc = 0
        }
        var drainprc;
        var str='';
        var temp = _printList(1, _categories, 0);
        if (temp.totalAmount >0){
            drainprc = Math.abs(Math.round(temp.totalMoney*100/temp.totalAmount))
        }else{
            drainprc = 0;
        }
        var b_color = (dateprc > drainprc)?'red':'green';
        str += '<tr id="profit" type="p" parent="0">\n\
                    <td class="w1">\n\
                        <a>Доходы</a>\n\
                    </td>\n\
                    <td class="w2">\n\
                        <div class="cont">\n\
                            <input type="text" value="'+formatCurrency(temp.totalAmount)+'" readonly="readonly" />\n\
                        </div>\n\
                    </td>\n\
                    <td class="w3">\n\
                        <div class="indicator">\n\
                            <div class="'+b_color+'" style="width: '+drainprc+'%;"></div>\n\
                            <div class="strip" style="width: '+dateprc+'%;"></div>\n\
                        </div>\n\
                    </td>\n\
                    <td class="w4">'+formatCurrency(Math.abs(temp.totalAmount)-Math.abs(temp.totalMoney))+'</td>\n\
                </tr>';
        str += temp.xhtml;

        temp = _printList(0, _categories, 0);
        if (temp.totalAmount >0){
            drainprc = Math.abs(Math.round(temp.totalMoney*100/temp.totalAmount))
        }else{
            drainprc = 0;
        }

        b_color = (dateprc < drainprc)?'red':'green';


        str += '<tr id="drain" type="d" parent="0">\n\
                    <td class="w1">\n\
                        <a>Расходы</a>\n\
                    </td>\n\
                    <td class="w2">\n\
                        <div class="cont">\n\
                            <input type="text" value="'+formatCurrency(temp.totalAmount)+'" readonly="readonly" />\n\
                        </div>\n\
                    </td>\n\
                    <td class="w3">\n\
                        <div class="indicator">\n\
                            <div class="'+b_color+'" style="width: '+drainprc+'%;"></div>\n\
                            <div class="strip" style="width: '+dateprc+'%;"></div>\n\
                        </div>\n\
                    </td>\n\
                    <td class="w4">'+formatCurrency(Math.abs(temp.totalAmount)-Math.abs(temp.totalMoney))+'</td>\n\
                </tr>';
        //////////////////////
        str += temp.xhtml;
        return str;
    }
    printBudget()
    ///////////////////////////////////////////////////////////////////////////
    //                          general                                      //
    ///////////////////////////////////////////////////////////////////////////
    return {getDate : getDate, init : init};
}