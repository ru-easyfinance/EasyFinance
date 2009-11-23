
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
     * Перегружает виджет на заданной дате
     * @param date {date}
     */
    function reload(date){
        _model.reload(date,function(){
            _currentDate = date
            _printDate();
            _printInfo();
            $('#budget .list.budget .body').html(printBudget());
        })
    }
    /**
     * Возвращает используюмую дату в сторонние виджеты
     */
    function getDate(){
        return new Date(_currentDate);
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
        $(bar).find('li.m_prev a').text(monthName[(month-1 == -1)?11:(month-1)]);
        $(bar).find('li.m_next a').text(monthName[(month+1 == 12)? 0:(month+1)]);
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
        }else if($(this).hasClass('m_prev')){
            _currentDate.setMonth(month-1);
        }else if($(this).hasClass('m_next')){
            _currentDate.setMonth(month+1);
        }else if($(this).hasClass('y_next')){
            _currentDate.setYear(year+1);
        }else if($(this).hasClass('cur')){
            _currentDate = new Date();

        }
        _printDate();
        _model.reload(_currentDate, function(){
            _printDate();
            _printInfo();
            $('#budget .list.budget .body').html(printBudget());
        })
    })
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
                    "<td class='profit'><b>План</b> доходов: <span>"+formatCurrency(_totalInfo.plan_profit)+" руб.</span></td>"+
                    "<td class='drain'><b>План</b> расходов: <span>"+formatCurrency(_totalInfo.plan_drain)+" руб.</span></td>"+
                    "<td class='balance "+planCls+"'>Остаток:<span>"+formatCurrency(_totalInfo.plan_profit-_totalInfo.plan_drain)+" руб.</span></td>"+
                "</tr>"+
                "<tr class='real'>"+
                    "<td class='profit'><b>Факт</b> доходов: <span>"+formatCurrency(_totalInfo.real_profit)+" руб.</span></td>"+
                    "<td class='drain'><b>Факт</b> расходов: <span>"+formatCurrency(_totalInfo.real_drain)+" руб.</span></td>"+
                    "<td class='balance "+realCls+"'>Остаток:<span>"+formatCurrency(_totalInfo.real_profit-_totalInfo.real_drain)+" руб.</span></td>"+
                "</tr>"+
            "</table>";
        $('#budget .budget.info').html(table);
        return false;
    }
    _printInfo();
    
    ///////////////////////////////////////////////////////////////////////////
    //                          list                                         //
    ///////////////////////////////////////////////////////////////////////////
    var _categories = easyFinance.models.category.getUserCategoriesTree();
    var _data;
    var date = new Date();
    var dateprc;

    function _getMonthDays(d){
        var m = d.getMonth()
        var t = new Date(d);
        for (var i = 29;i<32;i++)
        {
            t.setDate(i)
            if (m != t.getMonth()){return (i-1)}
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

                if (categoryes[key].children.length){
                    temp = _printList(type, categoryes[key].children, catId)
                }else{
                    temp = {};
                }


                totalAmount += parseFloat(isNaN(temp.totalAmount)?0:temp.totalAmount) + parseFloat((budgets[catId]?Math.abs(budgets[catId].amount):0));
                totalMoney += parseFloat(isNaN(temp.totalMoney)?0:temp.totalMoney) + parseFloat((budgets[catId]?Math.abs(budgets[catId].money):0));

                amount =  parseFloat(isNaN(temp.totalAmount)?0:temp.totalAmount) + parseFloat((budgets[catId]?Math.abs(budgets[catId].amount):0));
                money = parseFloat(isNaN(temp.totalMoney)?0:temp.totalMoney) + parseFloat((budgets[catId]?Math.abs(budgets[catId].money):0));
                if (amount > 0){
                    ////////// coompil html
                    var drainprc = Math.abs(Math.round(money*100/amount))
                    
                    
                    var b_color;


                    if (type == '1'){
                        b_color =(dateprc > drainprc)?'red':'green';
                    }else{
                        b_color =(dateprc < drainprc)?'red':'green';
                    }
                    var cls = !parentId?'parent open':'child'
                    if (cls == 'parent open'){
                        if (!temp.xhtml){
                            cls = 'nochild'
                        }
                    }
                    dhtml += '<tr id="'+catId+'" type="'+prefix+'" parent="'+parentId+'" class="'+cls+'">\n\
                                <td class="w1">\n\
                                    <a>' + catName + '</a>\n\
                                </td>\n\
                                <td class="w2">\n\
                                    <div class="cont">\n\
                                        <span>'+formatCurrency(amount)+'</span><input type="text" value="'+formatCurrency(amount)+'"/>\n\
                                    </div>\n\
                                </td>\n\
                                <td class="w3">\n\
                                    <div class="indicator">\n\
                                        <div class="'+b_color+'" style="width: '+drainprc+'%;"></div>\n\
                                        <div class="strip" style="width: '+dateprc+'%;"></div>\n\
                                    </div>\n\
                                </td>\n\
                                <td class="w5">'+formatCurrency(Math.abs(amount)-Math.abs(money))+'</td><td class="w6"><div><a title="Редактировать" class="edit"> </a><a title="Удалить" class="remove"> </a></div></td>\n\
                            </tr>';
                    //////////////////////
                    dhtml += temp.xhtml || '';
                }
            }
        }
        if (isNaN(totalAmount)){totalAmount = 0}
        if (isNaN(totalMoney)){totalMoney = 0}
        return {xhtml : dhtml,totalAmount : totalAmount,totalMoney : totalMoney};
    }


    function printBudget(){
        _data = _model.returnList()

        if (_currentDate.getMonth() == date.getMonth()){
            dateprc = Math.round(date.getDate()*100/_getMonthDays(date))
        }
        else{
            if(_currentDate > date){
                dateprc = 100
            }else{
                dateprc = 0
            }
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
        str += '<tr id="profit" type="p" class="open">\n\
                    <td class="w1">\n\
                        <a>Доходы</a>\n\
                    </td>\n\
                    <td class="w2">\n\
                        <div class="cont">\n\
                            <span>'+formatCurrency(temp.totalAmount)+'<span> <!-- <input type="text" value="'+formatCurrency(temp.totalAmount)+'" readonly="readonly" /> --> \n\
                        </div>\n\
                    </td>\n\
                    <td class="w3">\n\
                        <div class="indicator">\n\
                            <div class="'+b_color+'" style="width: '+drainprc+'%;"></div>\n\
                            <div class="strip" style="width: '+dateprc+'%;"></div>\n\
                        </div>\n\
                    </td>\n\
                    <td class="w5">'+formatCurrency(Math.abs(temp.totalAmount)-Math.abs(temp.totalMoney))+'</td><td class="w6"></td>\n\
                </tr>';
        str += temp.xhtml;

        temp = _printList(0, _categories, 0);
        if (temp.totalAmount >0){
            drainprc = Math.abs(Math.round(temp.totalMoney*100/temp.totalAmount))
        }else{
            drainprc = 0;
        }

        b_color = (dateprc < drainprc)?'red':'green';


        str += '<tr id="drain" type="d" class="open">\n\
                    <td class="w1">\n\
                        <a>Расходы</a>\n\
                    </td>\n\
                    <td class="w2">\n\
                        <div class="cont">\n\
                            <span>'+formatCurrency(temp.totalAmount)+'<span> <!-- <input type="text" value="'+formatCurrency(temp.totalAmount)+'" readonly="readonly" /> --> \n\
                        </div>\n\
                    </td>\n\
                    <td class="w3">\n\
                        <div class="indicator">\n\
                            <div class="'+b_color+'" style="width: '+drainprc+'%;"></div>\n\
                            <div class="strip" style="width: '+dateprc+'%;"></div>\n\
                        </div>\n\
                    </td>\n\
                    <td class="w5">'+formatCurrency(Math.abs(temp.totalAmount)-Math.abs(temp.totalMoney))+'</td><td class="w6"></td>\n\
                </tr>';
        //////////////////////
        str += temp.xhtml;
        return '<table>' + str + '</table>';
    }
    $('#budget .list.budget .body').html(printBudget());
    ///////////////////////////////////////////////////////////////////////////
    //                          general                                      //
    ///////////////////////////////////////////////////////////////////////////
    

    $('#budget .list.budget .parent.open .w1 a').live('click',function(){
        var id = $(this).closest('tr').attr('id');
        var type = $(this).closest('tr').attr('type');
        $('#budget .list.budget .child[type="'+type+'"][parent="'+id+'"]').hide()
        $(this).closest('tr').removeClass('open').addClass('close')
    })

    $('#budget .list.budget .parent.close .w1 a').live('click',function(){
        var id = $(this).closest('tr').attr('id');
        var type = $(this).closest('tr').attr('type');
        $('#budget .list.budget .child[type="'+type+'"][parent="'+id+'"]').show()
        $(this).closest('tr').addClass('open').removeClass('close')
    })



    $('#budget .list.budget .child .w6 a.remove').live('click',function(){
        if (confirm('Вы действительно хатите удалить бюджет по данной категории?')){
            var id = $(this).closest('tr').attr('id')
            var type = $(this).closest('tr').attr('type')
            _model.del(_currentDate, id, type, function(){
                _printInfo();
                $('#budget .list.budget .body').html(printBudget());
            })
        }
    })
    $('#budget .list.budget .nochild .w6 a.remove').live('click',function(){
        if (confirm('Вы действительно хатите удалить бюджет по данной категории?')){
            var id = $(this).closest('tr').attr('id')
            var type = $(this).closest('tr').attr('type')
            _model.del(_currentDate, id, type, function(){
                _printInfo();
                $('#budget .list.budget .body').html(printBudget());
            })
        }
    })

$('#budget .list.budget .w6 a.edit').live('click',function(){
        $(this).closest('tr').click()
    })

    $('#budget .list.budget #profit a').live('click',function(){
        $(this).closest('tr').toggleClass('open').toggleClass('close')
        if($(this).closest('tr').hasClass('open')){
            $('#budget .list.budget [type="p"][parent]').show();
            $('#budget .list.budget .parent[type="p"]').addClass('open').removeClass('close');
        }else{
            $('#budget .list.budget [type="p"][parent]').hide();
            $('#budget .list.budget .parent[type="p"]').addClass('close').removeClass('open')
        }

    })

    $('#budget .list.budget #drain a').live('click',function(){
        $(this).closest('tr').toggleClass('open').toggleClass('close')
        if($(this).closest('tr').hasClass('open')){
            $('#budget .list.budget [type="d"][parent]').show();
            $('#budget .list.budget .parent[type="d"]').addClass('open').removeClass('close')
        }else{
            $('#budget .list.budget [type="d"][parent]').hide();
            $('#budget .list.budget .parent[type="d"]').addClass('close').removeClass('open')
        }
    })

    $('#op_btn_Save').click(function(){
        setTimeout(function(){$('#budget li.cur').click();},1000);
    })

    $('#budget .list tr[parent]').live('click',function(){
        var parent = $(this).attr('parent');
        var id = $(this).attr('id');
        id = isNaN(id)?'0':id
        if (!parent || !$(this).closest('table').find('tr[parent="'+id+'"]').length){
            $('#budget .list tr .w2 input').hide();
            $('#budget .list tr .w2 span').show();
            $(this).find('.w2 input').val($(this).find('.w2 span').text()).show().focus();
            $(this).find('.w2 span').hide();
        }
    })
    $('#budget .list tr input').live('keyup',function(e){
        floatFormat($(this),String.fromCharCode(e.which) + $(this).val())
        if (e.keyCode == 13){
            var id = $(this).closest('tr').attr('id');
            var type = $(this).closest('tr').attr('type');
            var value = $(this).val();
            $('#budget .list tr .w2 input').hide();
            $('#budget .list tr .w2 span').show();
            _model.edit(_currentDate, type, id, value, function(){
                _printInfo();
                $('#budget .list.budget .body').html(printBudget());
            })
        }else if (e.keyCode == 27){
            $('#budget .list tr .w2 input').hide();
            $('#budget .list tr .w2 span').show();
        }
        
    })
    
    $('#budget .list tr input').click(function(){return false})

    $('body').click(function(){
        $('#budget .list tr .w2 input').hide();
        $('#budget .list tr .w2 span').show();
    })

    return {getDate : getDate, init : init, reload : reload};
}
