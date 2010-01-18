/**
 * @desc Accounts Journal Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.accountsJournal = function(){
    // private constants

    // private variables
    var _$node = null;
    var _model = null;

    var _accounts = null;

    // private functions
    function _initForm(){
        // show selection
        $('#operation_list tr.item').live('mouseover',
            function(){
                var g_types = [0,0,0,0,0,0,1,2,0,2,3,3,3,3,4,0];// Жуткий масив привязки типов к группам
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

                var str = '<table Stile="padding:3px">';
                str +=  '<tr style="line-height:19px;"><th> Название </th><td style="width:5px">&nbsp;</td><td>'+
                            account.name + '</td>';
                str +=  '<tr style="line-height:19px;"><th> Описание </th><td style="width:5px">&nbsp;</td><td>'+
                            account.comment + '</td>';
                str +=  '<tr style="line-height:19px;"><th> Остаток </th><td style="width:5px">&nbsp;</td><td style="width:95px">'+
                    formatCurrency(account.totalBalance) + ' ' + res.currency[account.currency]['text'] + '</td>';
                if (_accounts[id]["reserve"] != 0){
                    delta = (formatCurrency(account.totalBalance-_accounts[id]["reserve"]));
                    str +=  '<tr style="line-height:19px;"><th> Доступный остаток </th><td style="width:5px">&nbsp;</td><td>'+delta+' '+res.currency[account.currency]['text']+'</td>'
                    str +=  '<tr style="line-height:19px;"><th> Зарезервировано </th><td style="width:5px">&nbsp;</td><td>'+formatCurrency(_accounts[id]["reserve"])+' '+res.currency[account.currency]['text']+'</td>'
                }

                str +=  '<tr style="line-height:19px;"><th style="max-width:150px"> Остаток в валюте по умолчанию</th><td style="width:10px">&nbsp;</td><td>'+
                    formatCurrency(account.defCur) + ' '+d_cur+'</td>';

                // @todo: показывать % годовых и т.п.
                /*
                for(var key in spec)
                {
                    str +='<tr style="line-height:19px;">'+spec[key]+'<td /><td>'+account.special[key]+'</td></tr>'
                }
                */

                str += '<table>';
                _bigTip($(this).find('.totalBalance'), str);
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

/* --------- вызывает баг #499 ----------- */
        $('#operation_list tr.item').live('mouseout',
            function(){
                $('.qtip').remove();
                $(this).removeClass('act');
        });
/* --------- конец бага #499 ----------- */

        $('#operation_list tr.item').live('dblclick',
            function(){
                 // создание новой операции для выбранного счёта
                var acc = $(this).closest('tr').attr('id').split("_", 2)[1];
                if (easyFinance.widgets.operationEdit) {
                    easyFinance.widgets.operationEdit.setAccount(acc);
                    easyFinance.widgets.operationEdit.showForm();
                }
        });

        //del account click
        $('li.del').live('click',
            function(){
                if (confirm("Вы уверены что хотите удалить счёт?")) {
                    var id = $(this).closest('.item').attr('id').split("_", 2)[1];

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
                //easyFinance.widgets.accountEdit.setEditMode(true);
                $('#blockCreateAccounts').show();
                var id = $(this).closest('.item').attr('id').split("_", 2)[1];
                accounts_hash_api('#edit'+id);
            }
        );

        $('li.add').live('click',
            function(){
                $('#blockCreateAccounts').show();
                var id = $(this).closest('.item').attr('id').split("_", 2)[1];
                accounts_hash_api('#edit'+id, true);
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
                         tooltip: 'rightMiddle', // Use the corner...
                         target: 'leftMiddle' // ...and opposite corner
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
    function init(nodeSelector, model) {
        if (!model)
            return null;

        _$node = $(nodeSelector);

        _model = model;
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

        var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0]; // Жуткий масив привязки типов к группам
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
                            <th COLSPAN=2 Style="padding-left:40px"> \n\
                                Остаток \n\
                            </th>\n\
                            <th> \n\
                                Эквивалент в '+d_cur+' \n\
                            </th>\n\
                            <th></th>\n\
                        <tr>',
            s='';

            var curr = res['currency'];
            var num = curr['defa'];
            //debugger;

        // формирует массив с таблицей счетов по группам
        for (var key in account_list )
        {
            type = g_types[account_list[key]['type']];
            if (type == 2)
                colorClass = 'sumRed';
            else
                colorClass = account_list[key]["totalBalance"] >=0 ? 'sumGreen' : 'sumRed';
            

            if (!isNaN(type)){
                str = '<tr class="item" id="accountsJournalAcc_' + account_list[key]['id'] + '">';
                str = str + '<td class="name">' + account_list[key]["name"] + '</td>';
                if (type == 2) //для долга печатаем с противоположным знаком
                    str = str + '<td class="totalBalance ' + colorClass + '">' + formatCurrency(-account_list[key]["totalBalance"]) + '</td>';
                else
                    str = str + '<td class="totalBalance ' + colorClass + '">' + formatCurrency(account_list[key]["totalBalance"]) + '</td>';

                str = str + '<td class="cur">' + res.currency[account_list[key]["currency"]]['text'] + '</td>';
                if (type == 2)//для долга выводим с противоположным знаком
                    str = str + '<td class="def_cur ' + colorClass + '">' + formatCurrency(-account_list[key]["totalBalance"] * curr[account_list[key]["currency"]]['cost'] / curr[num]['cost']) + '</td>';
                else
                    str = str + '<td class="def_cur ' + colorClass + '">' + formatCurrency(account_list[key]["totalBalance"] * curr[account_list[key]["currency"]]['cost'] / curr[num]['cost']) + '</td>';
                summ[type] = summ[type]+account_list[key]['defCur'];
                if (!val[account_list[key]['currency']]) {
                    val[account_list[key]['currency']]=0;
                }
                //if (type != 2)
                val[account_list[key]['currency']] = val[account_list[key]['currency']]
                    + parseInt(account_list[key]['totalBalance']);
                /*else
                    val[account_list[key]['currency']] = val[account_list[key]['currency']]
                    - parseInt(account_list[key]['totalBalance']);*/
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
                    + ':</strong> ' + formatCurrency(tofloat(summ[key]))
                    + ' ' + d_cur+ '<table  class="noborder">' + head_tr+arr[key]
                    + '</table></div>';
                $('#operation_list').append(s);
            }
        }

        // формирование итогового поля
        str='<strong class="title">Итог</strong><table class="noborder">\n\
            <tr><th>Сумма</th><th>Валюта</th></tr>';
        for(key in val)
        {
            str = str+'<tr><td class="' + (val[key]>=0 ? 'sumGreen' : 'sumRed') + '">'+formatCurrency(val[key])+'</td><td>'+res.currency[key].text+'</td></tr>';
        }
        str = str+'<tr><td><b>Итого : </b>&nbsp;<span span class="' + (total>=0 ? 'sumGreen' : 'sumRed') + '">' + formatCurrency(total) + '</span></td><td> '+d_cur+'</td></tr>';
        str = str + '</table>';
        $('#total_amount').html(str);
        //$('#total_amount').append(str);

        // @todo перенести в цсс
        $('.item td.cur').css('width','50px');
        $('.item td.totalBalance').css('text-align','right').css('padding-right','0');
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object
