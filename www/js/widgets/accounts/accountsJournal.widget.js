/**
 * @desc Accounts Journal Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.accountsJournal = function(){
    // private constants

    // private variables
    var _$node = null;
    var _model = null;
    var _modelCurrency = null;

    var _accounts = null;

    // private functions
    function _initForm(){
        // show selection
        $('#operation_list tr.item').live('mouseover',
            function(){
                _accounts = _model.getAccounts();
                var account_list = _accounts;
                var defaultCurrency = _modelCurrency.getDefaultCurrency();
                var g_types = [0,0,0,0,0,0,1,2,0,2,3,3,3,3,4,0,0];// Жуткий масив привязки типов к группам
                var spec_th = [ [],
                            ['<th>% годовых</th>',
                                '<th>Доходность, % годовых</th>'],
                            ['<th>% годовых</th>',
                                '<th>Доходность, % годовых</th>',
                                '<th>Изменение с даты открытия</th>'],
                            ['<th>% годовых</th>'],
                            ['<th>Доходность, % годовых</th>',
                                '<th>Изменение с даты открытия</th>']];//доп графы для групп
                var id =$(this).attr('id').split("_", 2)[1];
                var account = _model.getAccounts()[id];
                var spec = spec_th[g_types[account.type]];

                var str = '<table style="padding:3px">';
                str +=  '<tr style="line-height:19px;"><th> Название </th><td style="width:5px">&nbsp;</td><td>'+
                            account.name + '</td></tr>';
                str +=  '<tr style="line-height:19px;"><th> Тип </th><td style="width:5px">&nbsp;</td><td>'+
                            _model.getAccountTypeString(account.id) + '</td></tr>';							
                str +=  '<tr style="line-height:19px;"><th> Описание </th><td style="width:5px">&nbsp;</td><td>'+
                            account.comment + '</td></tr>';
                str +=  '<tr style="line-height:19px;"><th> Остаток </th><td style="width:5px">&nbsp;</td><td style="width:95px">'+
                    formatCurrency(account.totalBalance) + ' ' + _model.getAccountCurrencyText(id) + '</td></tr>';
                if (_accounts[id]["reserve"] != 0){
                    var delta = (formatCurrency(account.totalBalance-_accounts[id]["reserve"]));
                    str +=  '<tr style="line-height:19px;"><th> Доступный&nbsp;остаток </th><td style="width:5px">&nbsp;</td><td>'+delta+' '+_model.getAccountCurrencyText(id)+'</td></tr>';
                    str +=  '<tr style="line-height:19px;"><th> Зарезервировано </th><td style="width:5px">&nbsp;</td><td>'+formatCurrency(_accounts[id]["reserve"])+' '+_model.getAccountCurrencyText(id)+'</td></tr>';
                }

                str +=  '<tr style="line-height:19px;"><th style="max-width:150px"> Остаток в валюте по умолчанию</th><td style="width:10px">&nbsp;</td><td>'+
                    formatCurrency(account.totalBalance * _model.getAccountCurrencyCost(id) / defaultCurrency.cost) + ' '+defaultCurrency.text+'</td></tr>';

                // @todo: показывать % годовых и т.п.
                /*
                for(var key in spec)
                {
                    str +='<tr style="line-height:19px;">'+spec[key]+'<td /><td>'+account.special[key]+'</td></tr>'
                }
                */

                str += '</table>';
                $('.qtip').remove();
                _bigTip($(this).find('.name'), str);
                
                $('#operation_list tr.item').removeClass('act');
                $(this).addClass('act');
        });

        // hide selection
        /*
        $('.mid').mousemove(function(){
            if (!$('ul:hover').length && !$('.act:hover').length)
                $('#operation_list tr').removeClass('act').find('.cont ul').hide();
        });
        */

        $('#operation_list').mouseout(
            function(){
                $('.qtip').remove();
                $('#operation_list tr.item.act').removeClass('act');
                return false;
        });

        $('#operation_list tr td:not(.mark)').live('click',
            function(){
                 // создание новой операции для выбранного счёта
                var acc = $(this).closest('tr').attr('id').split("_", 2)[1];
		document.location='/operation/#account=' + acc;
		document.location='/operation/#account=' + acc;
                if (easyFinance.widgets.operationEdit) {
                    //easyFinance.widgets.operationEdit.showForm();
                    easyFinance.widgets.operationEdit.setAccount(acc);
                }
                $(document).scrollTop(200);
        });

        //del account click
        $('li.del').live('click',
            function(){
                if (confirm("Вы уверены что хотите удалить счёт?")) {
                    var id;
                    if ($(this).parent().parent().parent().attr("class") == "account")
                        id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                    else
                        id = $(this).closest('.item').attr('id').split("_", 2)[1];

                    _model.deleteAccountById(id, function(data){
                        // @todo : kick this to operationEdit.widget on event accountDeleted
                        var val;

                        // выводим ошибку, если на счету зарегистрированы фин.цели.
                        if (data.error) {
                            if (data.error.text)
                                $.jGrowl(data.error.text, {theme: 'red'});
                        } else if (data.result) {
                            $('#op_account option').each(function(){
                                val = $(this).val();
                                if (val == id) {
                                    $(this).remove();
                                }
                            });

                            // update table
                            $('#accountsJournalAcc_'+id).remove();

                            if (data.result.text)
                                $.jGrowl(data.result.text, {theme: 'green'});
                        }
                    });
                }
            }
        );

        //edit account click
        $('li.edit').live('click',
            function(){
                var id;
                if ($(this).parent().parent().parent().attr("class") == "account")
                    id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                else
                    id = $(this).closest('.item').attr('id').split("_", 2)[1];
                
                //easyFinance.widgets.accountEdit.setEditMode(true);
                $('#blockCreateAccounts').show();
                accounts_hash_api('#edit'+id);
            }
        );

        $('li.add').live('click',
            function(){
                var id;
                if ($(this).parent().parent().parent().attr("class") == "account")
                    id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                else
                    id = $(this).closest('.item').attr('id').split("_", 2)[1];

                $('#blockCreateAccounts').show();
                accounts_hash_api('#edit'+id, true);
            }
        );

        $('li.operation').live('click',
            function(){
                var id;
                if ($(this).parent().parent().parent().attr("class") == "account")
                    id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                else
                    id = $(this).closest('.item').attr('id').split("_", 2)[1];

                if (easyFinance.widgets.operationEdit){
                    easyFinance.widgets.operationEdit.showForm();
                    easyFinance.widgets.operationEdit.setAccount(id);
                }
            }
        );
    }

    /**
     * Красивый тултип для таблицы счетов
     * @param line JQuery link to elem
     * @param text текст для подсказки
     * @return void
     */
    function _bigTip(line,text)
    {
        $('.qtip').remove();
        $(line).qtip({
            content: text.toString(), // Set the tooltip content to the current corner
            position: {
              corner: {
                 tooltip: 'topMiddle', // Use the corner...
                 target: 'bottomMiddle' // ...and opposite corner
              }
            },
            show: {
              when: false, // Don't specify a show event
              ready: true // Show the tooltip when ready
            },
            hide: {
              when: 'mouseout'
            }, // Don't specify a hide event
            style: {
              width: {max: 300},
              name: 'light',
              tip: true // Give them tips with auto corner detection
            }
        });
    }

    // public variables

    // public functions
    /**
     * @desc init widget
     * @usage init(nodeSelector, model)
     */
    function init(nodeSelector, model, modelCurrency) {
        if (!model || !modelCurrency)
            return null;

        _$node = $(nodeSelector);

        _model = model;
        _modelCurrency = modelCurrency;
        $(document).bind('accountsLoaded', redraw);
        $(document).bind('accountAdded', redraw);
        $(document).bind('accountEdited', redraw);
        $(document).bind('accountDeleted', redraw);

        _initForm();

        redraw();

        return this;
    }

    function redraw(){
        _accounts = _model.getAccounts();
        var account_list = _accounts;
        if (!account_list || account_list.length == 0)
            return;

        var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0,0]; // Жуткий масив привязки типов к группам
        var g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
        var spec_th = [ '',
                    '<th Style="display:none">% годовых</th><th Style="display:none">Доходность, % годовых</th>',
                    '<th Style="display:none">% годовых</th><th Style="display:none">Доходность, % годовых</th><th Style="display:none">Изменение с даты открытия</th>',
                    '<th Style="display:none">% годовых</th>',
                    '<th Style="display:none">Доходность, % годовых</th><th Style="display:none">Изменение с даты открытия</th>'];//доп графы для групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var val = {};//сумма средств по каждой используемой валюте
        var div = "<div class='cont'><ul style='z-index: 1006'>\n\
                        <li class='operation' title='Добавить операцию'><a></a></li>\n\
                        <li class='edit' title='Редактировать'><a></a></li>\n\
                        <li class='del' title='Удалить'><a></a></li>\n\
                        <li class='add' title='Копировать'><a></a></li>\n\
                    </ul></div>";
        $('#operation_list').empty();
        var type, str='', colorClass;
        var total = 0,
            head_tr='   <tr>\n\
                            <th> \n\
                                Имя \n\
                            </th>\n\
                            <th COLSPAN=2 Style="padding-left:90px"> \n\
                                Остаток \n\
                            </th>\n\
                            <th COLSPAN=2 Style="padding-left:40px">\n\
                                Эквивалент&nbsp;в&nbsp;'+ _modelCurrency.getDefaultCurrencyText() +' \n\
                            </th>\n\
                        <tr>',
            s='';

            var defaultCurrency = _modelCurrency.getDefaultCurrency();

        // формирует массив с таблицей счетов по группам
        for (var key in account_list )
        {
            type = g_types[account_list[key]['type']];
            colorClass = account_list[key]["totalBalance"] >=0 ? 'sumGreen' : 'sumRed';
            
            if (!isNaN(type)){
                str = '<tr class="item" id="accountsJournalAcc_' + account_list[key]['id'] + '">';
                str = str + '<td class="name"><span style="white-space:nowrap;">' + shorter(account_list[key]["name"], 25) + '</span></td>';
                str = str + '<td class="totalBalance ' + colorClass + '" style="width: 115px; max-width: 115px;">' + formatCurrency(account_list[key]["totalBalance"] ) + '</td>';

                str = str + '<td class="cur">' + _model.getAccountCurrencyText(key) + '</td>';
                str = str + '<td class="def_cur ' + colorClass + '" style="width: 115px; max-width: 115px;">' + formatCurrency( account_list[key]["totalBalance"] * _model.getAccountCurrencyCost(key) / defaultCurrency['cost']) + '</td>';
                summ[type] = summ[type] + (account_list[key]["totalBalance"] * _model.getAccountCurrencyCost(key) / defaultCurrency['cost']);
                if (!val[account_list[key]['currency']]) {
                    val[account_list[key]['currency']]=0;
                }
                //if (type != 2)
                val[account_list[key]['currency']] = val[account_list[key]['currency']]
                    + parseFloat(account_list[key]['totalBalance']);
                /*else
                    val[account_list[key]['currency']] = val[account_list[key]['currency']]
                    - parseFloat(account_list[key]['totalBalance']);*/
                str = str + '<td class="mark no_over">' + div + '</td></tr>';
                arr[type] = arr[type] + str;
            }
        }
        
        for(key in arr)//выводит конечный массив
        {
            if (arr[key]){
                // учесть долги
                //if (key != 2)
                    total = total+summ[key];
                //else
                    //total = total-summ[key];

                s='<div><strong class="title">'+ g_name[key]
                    + ':</strong> ' + formatCurrency( summ[key] )
                    + ' ' + _modelCurrency.getDefaultCurrencyText() + '<table  class="noborder">' + head_tr+arr[key]
                    + '</table></div>';
                $('#operation_list').append(s);
            }
        }

        // @todo перенести в цсс
        $('.item td.cur').css('width','10px');
        $('.item td.totalBalance').css('text-align','right').css('padding-right','0');
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object
