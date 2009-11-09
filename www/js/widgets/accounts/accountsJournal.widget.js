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
        $('tr.item').live('mouseover',
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
                            account.description + '</td>';
                str +=  '<tr style="line-height:19px;"><th> Остаток </th><td style="width:5px">&nbsp;</td><td style="width:95px">'+
                    formatCurrency(account.total_balance) + ' ' +account.cur + '</td>';
                if (_accounts[id]["reserve"] != 0){
                    delta = (formatCurrency(account.total_balance-_accounts[id]["reserve"]));
                    str +=  '<tr style="line-height:19px;"><th> Доступный остаток </th><td style="width:5px">&nbsp;</td><td>'+delta+' '+account.cur+'</td>'
                    str +=  '<tr style="line-height:19px;"><th> Зарезервировано </th><td style="width:5px">&nbsp;</td><td>'+formatCurrency(_accounts[id]["reserve"])+' '+account.cur+'</td>'
                }

                str +=  '<tr style="line-height:19px;"><th style="max-width:150px"> Остаток в валюте по умолчанию</th><td style="width:10px">&nbsp;</td><td>'+
                    formatCurrency(account.def_cur) + ' '+account.cur+'</td>';

                for(var key in spec)
                {
                    str +='<tr style="line-height:19px;">'+spec[key]+'<td /><td>'+account.special[key]+'</td></tr>'
                }

                str += '<table>';
                _bigTip($(this).find('.total_balance'), str);
                $('tr.item').removeClass('act');
                $(this).addClass('act');

        });

        $('tr.item').live('dblclick',
            function(){
                 //$(this).find('li.edit').click();
                 // создание новой операции для выбранного счёта
                 $(".op_addoperation").show();
                 $('#op_account').val($(this).closest('tr').attr('id').split("_", 2)[1]);
        });

        $('body').mousemove(function(){
            if (!$('ul:hover').length && !$('.act:hover').length) {
                $('.qtip').remove();
                $('tr.item').removeClass('act');
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
                        $('#op_account option').each(function(){
                            val = $(this).val();
                            if (val == id) {
                                $(this).remove();
                            }
                        })
                        
                        // update table
                        $('#accountsJournalAcc_'+id).remove();

                        $.jGrowl("Счёт удалён", {theme: 'green'});
                    });

                }
            }
        );

        //edit account click
        $('li.edit').live('click',
            function(){
                easyFinance.widgets.accountEdit.setEditMode(true);
                $('#blockCreateAccounts').show();
                var id = $(this).closest('.item').attr('id').split("_", 2)[1];
                hash_api('#edit'+id);
            }
        );

        $('li.add').live('click',
            function(){
                var flag = 1;
                $('#blockCreateAccounts').show();
                var id = $(this).closest('.item').attr('id').split("_", 2)[1];
                hash_api('#edit'+id,flag);
                easyFinance.widgets.accountEdit.setEditMode(false);
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

                   hide: false, // Don't specify a hide event
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

        _initForm();

        redraw();

        return this;
    }

    function redraw(){
        _accounts = _model.getAccounts();
        var account_list = _accounts;
        if (!account_list || account_list.length == 0)
            return;

        var g_types = [0,0,0,0,0,0,1,2,0,2,3,3,3,3,4,0]; // Жуткий масив привязки типов к группам
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
        var type, str='';
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
            
        // формирует массив с таблицей счетов по группам
        for (var key in account_list )
        {
            type = g_types[account_list[key]['type']];
            if (!isNaN(type)){
                str = '<tr class="item" id="accountsJournalAcc_' + account_list[key]['id'] + '">';
                str = str + '<td class="name">' + account_list[key]["name"] + '</td>';
                str = str + '<td class="total_balance ' + (account_list[key]["total_balance"]>=0 ? 'sumGreen' : 'sumRed') + '">' + formatCurrency(account_list[key]["total_balance"]) + '</td>';
                str = str + '<td class="cur">' + account_list[key]["cur"] + '</td>';
                str = str + '<td class="def_cur ' + (account_list[key]["def_cur"]>=0 ? 'sumGreen' : 'sumRed') + '">' + formatCurrency(account_list[key]["def_cur"]) + '</td>';
                summ[type] = summ[type]+account_list[key]['def_cur'];
                if (!val[account_list[key]['cur']]) {
                    val[account_list[key]['cur']]=0;
                }
                if (type != 2)
                    val[account_list[key]['cur']] = val[account_list[key]['cur']]
                    + parseInt(account_list[key]['total_balance']);
                else
                    val[account_list[key]['cur']] = val[account_list[key]['cur']]
                    - parseInt(account_list[key]['total_balance']);
                str = str + '<td class="mark no_over">' + div + '</td></tr>';
                arr[type] = arr[type] + str;
            }
        }
        
        for(key in arr)//выводит конечный массив
        {
            if (arr[key]){
                // учесть долги
                if (key != 2)
                    total = total+summ[key];
                else
                    total = total-summ[key];

                s='<div><strong class="title">'+ g_name[key]
                    + '</strong> : ' + formatCurrency(tofloat(summ[key]))
                    +d_cur+ '<table  class="noborder">' + head_tr+arr[key]
                    + '</table></div>';
                $('#operation_list').append(s);
            }
        }

        // формирование итогового поля
        str='<strong class="title"> Итог </strong><table class="noborder">\n\
            <tr><th>Сумма</th><th>Валюта</th></tr>';
        for(key in val)
        {
            str = str+'<tr><td class="' + (val[key]>=0 ? 'sumGreen' : 'sumRed') + '">'+formatCurrency(val[key])+'</td><td>'+key+'</td></tr>';
        }
        str = str+'<tr><td><b>Итого : </b>&nbsp;<span span class="' + (total>=0 ? 'sumGreen' : 'sumRed') + '">' + formatCurrency(total) + '</span></td><td>'+d_cur+'</td></tr>';
        str = str + '</table>';
        $('#total_amount').html(str);
        //$('#total_amount').append(str);

        // @todo перенести в цсс
        $('.item td.cur').css('width','50px');
        $('.item td.total_balance').css('text-align','right').css('padding-right','0');
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object