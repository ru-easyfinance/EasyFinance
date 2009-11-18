/**
 * @deprecated
 */
function floatFormat(obj, in_string ){
    var l = in_string.length;
    var rgx = /[0-9]/;
    var c=0;
    var p =1;
    var newstr ='';
    var i = 0;
    for(var a=1;a<=l;a++){
        i=l-a+1;
        if (rgx.test(in_string.substr(i,1))){
            if (c == 3){
                newstr = ' ' + newstr;
                c = 0
            }
            newstr =in_string.substr(i,1)+newstr
            c++;
        }
        if (in_string.substr(i,1)=='.' || in_string.substr(i,1)==','){
            if (p){
                newstr = newstr.substr(0,2)
                newstr ='.'+newstr;
            }
            c=0;
            p = 0;
        }
    }
    $(obj).val(newstr)
}
/**
 * @author Alexander *rewle* Ilichov
 */
easyFinance.widgets.budgetMaster = function(model,widget){

    /**
     * дата используемая в 1ом листе мастера
     */
    var _currentDate =new Date();

    /**
     * формирует данные для 2х последних страниц мастера
     * @param type {int} тип (доход == 1(р)/расход == 0(d))
     * @return void
     */
    function _printMaster(type){
        var prefix = (type == '1')? 'p':'d'; 
        var _data = model.returnList()[prefix]
        var children, str = '', ret ='';
        var k, key;
        var categoryType, parentId, parentName, catId, catName, budget;
        var _categories = easyFinance.models.category.getUserCategoriesTree();
        for (key in _categories){
            categoryType = _categories[key].type;
            if( (type == 0 && categoryType < 1)||(type == 1 && categoryType > -1)){
                parentId = _categories[key].id
                parentName=_categories[key].name
                children = _categories[key].children
                str = '<table>';
                for (k in children){
                    categoryType = _categories[key].children[k].type;
                    if( (type == 0 && categoryType < 1)||(type == 1 && categoryType > -1)){
                        catId = _categories[key].children[k].id;
                        catName=_categories[key].children[k].name;
                        budget = _data[catId] ||{amount : 0, money : 0}
                        str += '<tr id="'+catId+'"><td class="w1"><a>';
                        str += catName+'</a></td><td class="w2"><div class="cont">';
                        str += '<input type="text" value="'+formatCurrency(budget['amount'])+'"/></div></td>';
                        str += '<td class="w4"><span>'+formatCurrency(budget['mean']||'0')+' </span></td>';
                        str += '</tr>';
                    }
                }
                str+='</table>';
                if (str=='<table></table>')
                {
                    ret += '<div class="line open nochild" id="'+parentId+'">';
                    ret += '<a class="name nochild">'+parentName+'</a>';
                    ret += '<div class="amount"><input type="text" value="'+formatCurrency(_data[parentId]?_data[parentId]['amount']:0)+'" /></div>\n\
                            <span class="mean">'+formatCurrency(_data[parentId]?_data[parentId]['mean']:'0')+' </span></div>';
                }
                else
                {
                    ret += '<div class="line open" id="'+parentId+'">';//@todo
                    ret += '<a class="name">'+parentName+'</a>';
                    ret += '<div class="amount">'+formatCurrency(_data[parentId]?_data[parentId]['amount']:0)+'</div>'+str+'</div>';
                }
            }
        }
        if (type){
            $('#master #step2 .list.body').html(ret)
        }else{
            $('#master #step3 .list.body').html(ret)
        }
    }
    /**
     * подсчёт итоговой суммы
     * @param step {str} 'step2'||'step3'
     * @return float
     */
    function globalSum(step){
        var ret = 0;
        $('#master #'+step+' div.amount').each(function(){
            var str = $(this).find('input').val() || $(this).text();
            if(!isNaN(parseFloat(str.replace(/[^0-9\.]/gi,'')))){
                ret += parseFloat(str.replace(/[^0-9\.]/gi,''));
            }
        })
        return ret;
    }
    /**
     * расчет сумм по категориям
     * @param id {int} ид категории
     * @param step {str} 'step2'||'step3'
     * @return void
     */
    function fullSum(id,step){
        var tmp;
        //if(!id){
            $('#master div.line').each(function(){
                if (!$(this).find('.amount input').length){
                    var ret = 0;
                    $(this).find('input').each(function(){
                        tmp = parseFloat($(this).val().toString().replace(/[^0-9\.]/gi,''))
                        if (isNaN(tmp)){tmp = 0;}
                        ret += tmp
                    })
                    $(this).find('.amount').text(formatCurrency(ret))
                }
            })
//        }else{ @todo
//            var sel = $('#master #'+step+' div.line#'+id)
//            if (!$(sel).find('.amount input').length){
//                var ret = 0;
//                $(sel).find('input').each(function(){
//                    tmp = parseFloat($(this).val().toString().replace(/[^0-9\.]/gi,''))
//                    if (isNaN(tmp)){tmp = 0;}
//                    ret += tmp
//                })
//                $(sel).find('.amount').text(formatCurrency(ret))
//            }
//        }
        var profit = globalSum('step2')
        var drain = globalSum('step3')
        $('#master .waste b').text(formatCurrency(drain))
        $('#master .income b').text(formatCurrency(profit))
        if (drain - profit > 0){
            $('#master .rest b').css('color','#EB3C34')
        }else{
            $('#master .rest b').css('color','#309500')
        }
        $('#master .rest b').text(formatCurrency(profit - drain))
        
    }

    /**
     * Компилирует джейсон для сообщения на сервер
     */
    function _compilReturnJSON(){
        var tmp = {step3 : '', step2 : ''}
        $('#master .waste_list input').each(function(){
            var parent = $(this).closest('tr')
            if (!$(parent).length){
                parent = $(this).closest('.line')
            }
            var id = $(parent).attr('id').toString().replace(/[^0-9]/gi,'');
            var val = $(this).val().toString().replace(/[^0-9\.]/,'');
            if (!isNaN(val) && val > 0){
                if ($(this).closest('.step').attr('id')=='step2'){
                    tmp.step2+= '{"'+id+'": "'+val+'"},';
                }else{
                    tmp.step3+= '{"'+id+'": "'+val+'"},';
                }
            }
        })
        var ret = '{"d": ['+tmp.step3+'], "p": ['+tmp.step2+']}';
        while(ret.indexOf('},]') != -1){
            ret = ret.replace('},]', '}]', 'gi');
        }
        return ret;
    }
    /**
     * Форматирование на лету
     */
    $('#master .waste_list input')
        .live('keyup',function(e){
            floatFormat($(this),String.fromCharCode(e.which) + $(this).val())
        })
        .live('click',function(){
            if ($(this).val() == '0.00'){
                $(this).val('');
            }
        }
    );

    /**
     * Скрытие-раскрытие ветки дерева
     */
    $('#master div.line a.name').live('click',function(){
        $(this).closest('.line').toggleClass('open').toggleClass('close');
        return false;
    })

    /**
     * маска для инпута с годом
     */
    $('#master #step1 input#year').keyup(function(){
        var str = $('#step1 input#year').val();
        $('#step1 input#year').val(str.match(/[0-9]{0,4}/)[0]);
    });



    /**
     * переходы по листам мастера
     */
    $('#master .next,#master .prev').click(function(){
        var id = $(this).attr('id');
        switch(id){
            case 'tostep1':
                $('#master .step').hide();
                $('#master #step1').show();
                var tempDate = _currentDate;
                break;
            case 'tostep2':
                if (($(this).hasClass('next')) && (tempDate !== _currentDate))
                {
                    fullSum(0);
                    _currentDate.setDate(1)
                    _currentDate.setYear($('#master #step1 #year').val());
                    _currentDate.setMonth($('#master #step1 #month').val()-1);
                    $('#master #step2 .master.head h4').text('Шаг 2 из 3. Доходы - Планирование бюджета на '+$('#master #step1 #month option[value="'+$('#master #step1 #month').val()+'"]').text() +' '+$('#master #step1 #year').val())
                    $('#master #step3 .master.head h4').text('Шаг 3 из 3. Расходы - Планирование бюджета на '+$('#master #step1 #month option[value="'+$('#master #step1 #month').val()+'"]').text() +' '+$('#master #step1 #year').val())
                    model.reload(_currentDate,function(drain,profit){
                        _printMaster(1);
                        _printMaster(0);
                        var str = '<div class="income">Итого доходов: <span><b>'+formatCurrency(profit)+'</b> руб.</span></div>';
                        str += '<div class="waste">Итого расходов: <span><b>'+formatCurrency(drain)+'</b> руб.</span></div>';
                        str += '<div class="rest">Остаток: <span><b>'+formatCurrency(profit - drain)+'</b> руб.</span></div>';
                        $('#master .f_field3').html(str);
                        fullSum(0);
                    })
                }else{
                    fullSum(0);
                    $('#master .waste b').text(formatCurrency(globalSum('step3')))
                    $('#master .rest b').text(formatCurrency(parseFloat($('#master  #step2 .income b').text().toString().replace(/[^0-9\.]/gi,'')) - globalSum('step3')))
                }
                $('#master .step').hide();
                $('#master #step2').show();
                break;
            case 'tostep3':
                fullSum(0);
                $('#master .income b').text(formatCurrency(globalSum('step2')))
                $('#master .rest b').text(formatCurrency(globalSum('step2')-parseFloat($('#master #step2 .waste b').text().toString().replace(/[^0-9\.]/gi,''))))
                $('#master .step').hide();
                $('#master #step3').show();
                break;
            case 'tosave':
                fullSum(0);
                model.save(_compilReturnJSON(),_currentDate,function(date){widget.reload(date)});
                $('#master').dialog('close');
                break;
        }
        $('#master').closest('.ui-widget').find('#ui-dialog-title-master').html($('#master .step:visible .master.head').html());//$('#master .step:visible .master.head h4').text()  });
    })


    /**
     * инициализация мастера
     */
    $('#master').dialog({bgiframe: true,autoOpen: false,width: 520, modal: true, resizable: false});

    /**
     * кнопочка для вызова мастера.
     */
    $('#btnBudgetWizard').click(function(){ 
        $('#master .step').hide();
        $('#master #step1').show();
        var tempDate = widget.getDate()
        tempDate.setMonth(tempDate.getMonth()+1);
        $('#step1 #month').val(tempDate.getMonth()+1);
        $('#step1 #year').val(tempDate.getFullYear());
        $('#master').dialog('open');
        $('#master').closest('.ui-widget').find('#ui-dialog-title-master').html($('#master .step:visible .master.head').html());
    })

    /**
     * Ссумирование подкатегорий
     */
    $('#master').live('click',function(){fullSum(0)})
//    $('#master tr input').live('blur',function(){
//        alert('123')
//        fullSum($(this).closest('.line').attr('id'),$(this).closest('.step').attr('id'))
//    })
//    $('#master .amount input').live('blur',function(){
//
//        var profit = globalSum('step2')
//        var drain = globalSum('step3')
//        $('#master .waste b').text(formatCurrency(drain))
//        $('#master .income b').text(formatCurrency(profit))
//        if (drain - profit > 0){
//            $('#master .rest b').css('color','#EB3C34')
//        }else{
//            $('#master .rest b').css('color','#309500')
//        }
//        $('#master .rest b').text(formatCurrency(profit - drain))
//    })
    return {};
}
