easyFinance.widgets.budgetMaster = function(model,widget){

    /**
     * дата используемая в 1ом листе мастера
     */
    var _currentDate = new Date();

    var tplCategoryRow =
        '<tr id="{%catId%}">\
            <td class="w1"><a>{%catName%}<a></td>\
            <td class="w2">\
                <div class="cont">\
                    <input type="text" value="{%planValue%}"/>\
                    {%calendarPlanned%}\
                </div>\
            </td>\
            <td class="w4">\
                <span>{%budgetMean%}</span>\
            </td>\
        </tr>';

    var tplEmptyParentCategory =
        '<div class="line open nochild" id="{%parentId%}">\
            <a class="name nochild">{%parentName%}</a>\
            <div class="amount">\
                <input type="text" value="{%amount%}" />\
            </div>\
            <span class="mean">{%mean%}</span>\
        </div>'

    var tplParentCategory =
        '<div class="line open" id="{%parentId%}">\
            <a class="name">{%parentName%}</a>\
            <div class="amount">{%amount%}</div>\
            {%children%}\
        </div>';

    var tplWizardFooter =
        '<div class="income">Итого доходов: <span><b>{%profit%}</b> {%defaultCurrency%}</span></div>\
        <div class="waste">Итого расходов: <span><b>{%drain%}</b> {%defaultCurrency%}</span></div>\
        <div class="rest">Остаток: <span><b>{%remainder%}</b> {%defaultCurrency%}</span></div>';

    var tplTableCaptionProfit =
        '<td>Категория</td>\
        <td>Сумма, {%defaultCurrency%}</td>\
        <td>Сред. доход, {%defaultCurrency%}</td>';

    var tplTableCaptionDrain =
        '<td>Категория</td>\
        <td>Сумма, {%defaultCurrency%}</td>\
        <td>Сред. расход, {%defaultCurrency%}</td>';

    var tplCommonColumns =
        '<colgroup>\
            <col class="b-budgetwizard-columns__catname"/>\
            <col class="b-budgetwizard-columns__amount"/>\
            <col class="b-budgetwizard-columns__mean"/>\
            <col class="b-budgetwizard-columns__calendar"/>\
        </colgroup>'

    var tplStepHeader = 'Шаг {%stepNum%} из 3. {%stepType%} — Планирование бюджета на {%date%}';

    function _printMasterRow(category, bdgt, isDrain) {
        if ( (isDrain && category.type > 0) || (!isDrain && category.type < 0) ) {
            return '';
        }

        var vals = {
            catId: category.id,
            catName: category.name,
            planValue: '',
            budgetMean: '',
            calendarPlanned: ''
        };
        
        bdgt = $.extend({}, bdgt);

        if ("amount" in bdgt) {
            vals.planValue = formatCurrency( bdgt.amount )
        }
        if ("mean" in bdgt) {
            vals.budgetMean = formatCurrency( bdgt.mean )
        }

        if ("calendar_plan" in bdgt) {
            vals.calendarPlanned = '<small>' + formatCurrency( bdgt.calendar_plan ) + '</small>'
        }

        return templetor(tplCategoryRow, vals);
    }

    /**
     * формирует данные для 2х последних страниц мастера
     * @param type {int} тип (доход == 1(р)/расход == 0(d))
     * @return void
     */
    function _printMaster(type) {
        var prefix = (type == '1') ? 'p' : 'd';
        var _data = model.returnList()[prefix]
        var children,
            str = '',
            ret ='';

        var categoryType,
            parentId,
            parentName,
            catId,
            catName,
            budget,
            plan,
            bdgt,

            cat_rows = [],
            vals = {};

        var _categories = easyFinance.models.category.getUserCategoriesTreeOrdered();

        for (var key in _categories) {
            categoryType = _categories[key].type;
            if ( (type == 0 && categoryType < 1) || (type == 1 && categoryType > -1) ) {
                parentId = _categories[key].id
                parentName = _categories[key].name
                children = _categories[key].children

                for (var k in children) {
                    cat_rows.push( _printMasterRow(children[k], _data[children[k].id], type == 0) )
//                    categoryType = _categories[key].children[k].type;
//
//                    if ( (type == 0 && categoryType < 1) || (type == 1 && categoryType > -1) ) {
//                        catId = _categories[key].children[k].id;
//
//                        bdgt = $.extend({amount: 0, mean: 0}, _data[catId]);
//
//                        vals = {
//                            catId: catId,
//                            catName: _categories[key].children[k].name,
//                            planValue: formatCurrency( bdgt.amount ),
//                            budgetMean: formatCurrency( bdgt.mean ),
//                            calendarPlanned: ("calendar_plan" in bdgt) ? '<small>' + formatCurrency( bdgt.calendar_plan ) + '</small>' : ''
//                        }
//
//                        cat_rows.push(templetor(tplCategoryRow, vals));
//                    }
                }

                if (!cat_rows.length) {
                    vals = {
                        parentId: parentId,
                        parentName: parentName,
                        amount: formatCurrency(_data[parentId] ? _data[parentId]['amount'] : 0),
                        mean: formatCurrency(_data[parentId] ?_data[parentId]['amount'] : 0)
                    }
                    ret = templetor(tplEmptyParentCategory, vals);
                }
                else {

                    vals = {
                        parentId: parentId,
                        parentName: parentName,
                        amount: formatCurrency(_data[parentId] ? _data[parentId]['amount'] : 0),
                        children: '<table class="b-budgetwizard-table b-budgetwizard-table__content">' + cat_rows.join('') + '</table>'
                    }
                    ret = templetor(tplParentCategory, vals);
                }
            }
        }
        if (type) {
            $('#master #step2 .list.body').html(ret)
        }
        else {
            $('#master #step3 .list.body').html(ret)
        }
    }
    /**
     * подсчёт итоговой суммы
     * @param step {str} 'step2'||'step3'
     * @return float
     */
    function globalSum(step) {
        var ret = 0;
        $('#master #' + step + ' div.amount').each(function() {
            var str = $(this).find('input').val() || $(this).text();
            if( !isNaN(parseFloat( str.replace(/[^0-9\.]/gi, '') )) ){
                ret += parseFloat(str.replace(/[^0-9\.]/gi, ''));
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

        $('#master div.line').each(function() {
            if (!$(this).find('.amount input').length) {
                var ret = 0;
                $(this).find('input').each(function() {
                    tmp = parseFloat( $(this).val().toString().replace(/[^0-9\.]/gi,'') )
                    if (isNaN(tmp)) {
                        tmp = 0;
                    }
                    ret += tmp
                })
                $(this).find('.amount').text(formatCurrency(ret))
            }
        })

        var profit = globalSum('step2')
        var drain = globalSum('step3')
        $('#master .waste b').text(formatCurrency(drain))
        $('#master .income b').text(formatCurrency(profit))
        if (drain - profit > 0) {
            $('#master .rest b').css('color','#EB3C34')
        }
        else {
            $('#master .rest b').css('color','#309500')
        }
        $('#master .rest b').text(formatCurrency(profit - drain))

    }

    /**
     * Компилирует JSON для сообщения на сервер
     */
    function compileJSONRequest() {
        var tmp = {step3: '', step2: ''};

        $('#master .waste_list input').each(function() {
            var parent = $(this).closest('tr')
            if (!$(parent).length) {
                parent = $(this).closest('.line');
            }
            var id = $(parent).attr('id').toString().replace(/[^0-9]/gi, '');
            var val = $(this).val().toString().replace(/[^0-9\.]/gi, '');
            if (!isNaN(val)) {
                if ($(parent).closest('.step').attr('id') == 'step2') {
                    tmp.step2 += '{"' + id + '": "' + val + '"},';
                }
                else {
                    tmp.step3 += '{"' + id + '": "' + val + '"},';
                }
            }
        });
        var ret = '{"d": [' + tmp.step3 + '], "p": [' + tmp.step2 + ']}';
        while(ret.indexOf('},]') != -1) {
            ret = ret.replace('},]', '}]');
        }
        return ret;
    }

    /**
     * Центрирование окна мастера
     */
     function recenterDialog() {
        $('#master').dialog('option', 'position', ['center', 60]);
     }

    /**
     * Форматирование на лету
     */
    $('#master .waste_list input')
        .live('keypress', function(e) {
            if (e.keyCode == 13) {
                $(this).val(calculate($(this).val()));
            }
        })
        .live('click', function() {
            if ($(this).val() == '0.00'){
                $(this).val('');
            }
        }
    );

    $('#step2 div.master.body div.list.head tr').html(templetor(tplTableCaptionProfit, {defaultCurrency: easyFinance.models.currency.getDefaultCurrencyText()}));
    $('#step3 div.master.body div.list.head tr').html(templetor(tplTableCaptionDrain, {defaultCurrency: easyFinance.models.currency.getDefaultCurrencyText()}));
    

    /**
     * Скрытие-раскрытие ветки дерева
     */
    $('#master div.line a.name').live('click', function() {
        $(this).closest('.line').toggleClass('open').toggleClass('close');
    })


    /**
     * маска для инпута с годом
     */
    $('#master #step1 input#year').keyup(function() {
        var str = $('#step1 input#year').val();
        $('#step1 input#year').val(str.match(/[0-9]{0,4}/)[0]);
    });


    /**
     * переходы по листам мастера
     */
    $('#master .next, #master .prev').click(function() {
        var id = $(this).attr('id');
        var loadDate;
        switch(id) {
            case 'tostep1':
                $('#master .step').hide();
                $('#master #step1').show();
                var tempDate = _currentDate;
                recenterDialog();
                break;

            case 'tostep2':
                if (($(this).hasClass('next')) && (tempDate !== _currentDate)) {
                    fullSum(0);
                    _currentDate.setDate(1)
                    _currentDate.setYear($('#master #step1 #year').val());
                    _currentDate.setMonth($('#master #step1 #month').val() - 1);
                    if ($('#master #step1 input:[type="radio"][checked]').attr('plantype')=='new') {
                        loadDate = new Date(_currentDate);
                    }
                    else {
                        loadDate = new Date();
                        loadDate.setDate(1)
                        loadDate.setYear($('#master #step1 #copy_year').val());
                        loadDate.setMonth($('#master #step1 #copy_month').val() - 1);
                    }

                    var budgetDate = $('#master #step1 #month option[value="' + $('#master #step1 #month').val() + '"]').text() + ' ' + $('#master #step1 #year').val()

                    var headerVals = {
                        stepNum: 2,
                        stepType: 'Доходы',
                        date: budgetDate
                    }
                    $('#master #step2 .master.head h4').text( templetor(tplStepHeader, headerVals) );

                    headerVals.stepNum = 3;
                    headerVals.stepType = 'Расходы';
                    $('#master #step3 .master.head h4').text( templetor(tplStepHeader, headerVals) );

                    model.reload(loadDate, function(drain, profit) {
                        _printMaster(1);
                        _printMaster(0);

                        var footerVals = {
                            profit: formatCurrency(profit),
                            drain: formatCurrency(drain),
                            remainder: formatCurrency(profit - drain),
                            defaultCurrency: easyFinance.models.currency.getDefaultCurrencyText()
                        }

                        $('#master .f_field3').html(templetor(tplWizardFooter, footerVals));
                        fullSum(0);
                    })
                }
                else {
                    fullSum(0);
                    $('#master .waste b').text(formatCurrency(globalSum('step3')))
                    $('#master .rest b').text(formatCurrency(parseFloat($('#master  #step2 .income b').text().toString().replace(/[^0-9\.]/gi, '')) - globalSum('step3')))
                }
                $('#master .step').hide();
                $('#master #step2').show();
                recenterDialog();
                break;

            case 'tostep3':
                fullSum(0);
                $('#master .income b').text(formatCurrency(globalSum('step2')))
                $('#master .rest b').text(formatCurrency(globalSum('step2') - parseFloat($('#master #step2 .waste b').text().toString().replace(/[^0-9\.]/gi,''))))
                $('#master .step').hide();
                $('#master #step3').show();
                recenterDialog();
                break;

            case 'tosave':
                fullSum(0);
                model.save(compileJSONRequest(),_currentDate,function(date){widget.reload(date)});
                $('#master').dialog('close');
                break;
        }
        $('#master').closest('.ui-widget').find('#ui-dialog-title-master').html($('#master .step:visible .master.head').html());//$('#master .step:visible .master.head h4').text()  });
    })


    /**
     * инициализация мастера
     */
    $('#master').dialog({
        bgiframe: true,
        autoOpen: false,
        width: 650,
        resizable: false
    });

    utils.initControls( $('#master').dialog('widget') );

    function month(num){
        var str = num.toString();
        if (str.length == 1)
            str = '0' + str;
        return str;
    }

    /**
     * кнопочка для вызова мастера.
     */
    $('#btnBudgetWizard').click(function(e) {
        $('#master .step').hide();
        $('#master #step1').show();

        var tempDate = widget.getDate()
        $('#step1 select#copy_month').val( month(tempDate.getMonth() + 1) );
        $('#step1 #copy_year').val( tempDate.getFullYear() );
        tempDate.setMonth( tempDate.getMonth() + 1 );

        $('#step1 select#month').val( month(tempDate.getMonth() + 1) );
        $('#step1 #year').val( tempDate.getFullYear() );

        $('#master').dialog('open');
        $('#master').closest('.ui-widget').find('#ui-dialog-title-master').html($('#master .step:visible .master.head').html());

        recenterDialog();

        e.preventDefault(); // preventing scroll to anchor
        return false;
    });

    /**
     * переключение между "копировать" и "создать из"
     */
    $('#master #step1 input:[type="radio"]').click(function() {
        if ($('#master #step1 input:[type="radio"][checked]').attr('plantype') == 'new') {
            $('#master #step1 .copy').addClass('hidden');
        }
        else {
            $('#master #step1 .copy').removeClass('hidden');
        }
    });

    /**
     * Сумирование подкатегорий
     */
    $('#master').live('click', function() {fullSum(0)} );

    $('#master tr input').live('blur', function() {
        fullSum( $(this).closest('.line').attr('id'), $(this).closest('.step').attr('id') );
    });

    $('#master .amount input').live('blur', function() {
        var profit = globalSum('step2');
        var drain = globalSum('step3');

        $('#master .waste b').text(formatCurrency(drain));
        $('#master .income b').text(formatCurrency(profit));

        if (drain - profit > 0) {
            $('#master .rest b').css('color', '#EB3C34');
        }
        else {
            $('#master .rest b').css('color', '#309500');
        }

        $('#master .rest b').text(formatCurrency(profit - drain));
    });

    return {};
}