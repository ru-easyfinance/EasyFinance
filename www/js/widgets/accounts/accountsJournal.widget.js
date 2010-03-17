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
        // создание новой операции для выбранного счёта
        $('#accountsJournal .content tr.child td:not(.mark)').live('click',
            function(){
                var acc = $(this).closest('tr').attr('id').split("_", 2)[1];
		window.location = '/operation/#account=' + acc;
        });

        //del account click
        $('#accountsJournal li.del').live('click',
            function(){
                if (confirm("Вы уверены что хотите удалить счёт?")) {
                    var id;
                    if ($(this).parent().parent().parent().hasClass("account"))
                        id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                    else
                        id = $(this).closest('.item').attr('id').split("_", 2)[1];

                    _model.deleteAccountById(id, function(data){

                        // выводим ошибку, если на счету зарегистрированы фин.цели.
                        if (data.error) {
                            if (data.error.text)
                                $.jGrowl(data.error.text, {theme: 'red'});
                        } else if (data.result) {
                            $('#op_account option').each(function(){
                                if ($(this).val() == id) {
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
        $('#accountsJournal li.edit').live('click',
            function(){
                var id;
                if ($(this).parent().parent().parent().hasClass("account"))
                    id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                else
                    id = $(this).closest('.item').attr('id').split("_", 2)[1];
                
                //easyFinance.widgets.accountEdit.setEditMode(true);
                $('#blockCreateAccounts').show();
                accounts_hash_api('#edit'+id);
            }
        );

        $('#accountsJournal li.add').live('click',
            function(){
                var id;
                if ($(this).parent().parent().parent().hasClass("account"))
                    id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                else
                    id = $(this).closest('.item').attr('id').split("_", 2)[1];

                $('#blockCreateAccounts').show();
                accounts_hash_api('#edit'+id, true);
            }
        );

        $('#accountsJournal li.operation').live('click',
            function(){
                var id;
                if ($(this).parent().parent().parent().hasClass("account"))
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

    function _initBigTip(){
        $('#accountsJournal .content .item').each(function(){
            _accounts = _model.getAccounts();
            var defaultCurrency = _modelCurrency.getDefaultCurrency();
            var id = $(this).attr('id').split("_", 2)[1];
            var account = _model.getAccounts()[id];

            var str = '<table>';
            str +=  '<tr><th> Название </th><td>&nbsp;</td><td>'+
                        account.name + '</td></tr>';
            str +=  '<tr><th> Тип </th><td>&nbsp;</td><td>'+
                        _model.getAccountTypeString(account.id) + '</td></tr>';
            str +=  '<tr><th> Описание </th><td>&nbsp;</td><td>'+
                        account.comment + '</td></tr>';
            str +=  '<tr><th> Остаток </th><td>&nbsp;</td><td>'+
            formatCurrency(account.totalBalance) + ' ' + _model.getAccountCurrencyText(id) + '</td></tr>';
            if (account.reserve != 0){
                var delta = (formatCurrency(account.totalBalance-account.reserve));
                str +=  '<tr><th> Доступный&nbsp;остаток </th><td>&nbsp;</td><td>'+delta+' '+_model.getAccountCurrencyText(id)+'</td></tr>';
                str +=  '<tr><th> Зарезервировано </th><td>&nbsp;</td><td>'+formatCurrency(account.reserve)+' '+_model.getAccountCurrencyText(id)+'</td></tr>';
            }

            str +=  '<tr><th> Остаток в валюте по умолчанию</th><td>&nbsp;</td><td>'+
                formatCurrency(account.totalBalance * _model.getAccountCurrencyCost(id) / defaultCurrency.cost) + ' '+defaultCurrency.text+'</td></tr>';


            str += '</table>';
            $(this).qtip({
                content: str, // Set the tooltip content to the current corner
                position: {
                  corner: {
                     tooltip: 'topMiddle', // Use the corner...
                     target: 'bottomMiddle' // ...and opposite corner
                  }
                },
                style: {
                  width: {max: 300},
                  name: 'light',
                  tip: true // Give them tips with auto corner detection
                }
            });
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
        _initForm();

        var head_tr='<tr><th class="name"> Название </th>' +
            '<th class="money totalBalance"><div>Остаток</div></th>' +
            '<th COLSPAN=2 class="money">Эквивалент&nbsp;в&nbsp;' + _modelCurrency.getDefaultCurrencyText() +' </th>' +
            '<th class="scroll"><div>&nbsp;</div></th><tr>';
        $('#accountsJournal .head').html(head_tr);
        
        redraw();
        
        $(document).bind('accountsLoaded', redraw);
        //$(document).bind('accountAdded', redraw);
        $(document).bind('accountEdited', redraw);
        $(document).bind('accountDeleted', redraw);

        return this;
    }

    function redraw(){
        var account_list_ordered = _model.getAccountsOrdered();
        _accounts = _model.getAccounts();
        var account_list = _accounts;
        if (!account_list || account_list.length == 0){
            return;
        }
        var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0,0]; // Жуткий масив привязки типов к группам
        var g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var div = "<div class='cont'><ul style='z-index: 1006'>\n\
                        <li class='operation' title='Добавить операцию'><a></a></li>\n\
                        <li class='edit' title='Редактировать'><a></a></li>\n\
                        <li class='del' title='Удалить'><a></a></li>\n\
                        <li class='add' title='Копировать'><a></a></li>\n\
                    </ul></div>";

        var type, str='', colorClass;

            var defaultCurrency = _modelCurrency.getDefaultCurrency();

        // формирует массив с таблицей счетов по группам
        for (var row in account_list_ordered)
   //for (var key in account_list )
        {
            type = g_types[account_list_ordered[row]['type']];
            colorClass = account_list_ordered[row]["totalBalance"] >=0 ? 'sumGreen' : 'sumRed';
            
            if (!isNaN(type)){
                str = '<tr class="item child" id="accountsJournalAcc_' + account_list_ordered[row]['id'] + '">';
                str = str + '<td class="name"><span style="white-space:nowrap;">' + shorter(account_list_ordered[row]["name"], 25) + '</span></td>';
                str = str + '<td class="totalBalance money"><div class="abbr">' + _model.getAccountCurrencyText(account_list_ordered[row]["id"]) + '</div>'+'<div class="number '+colorClass+'">' + formatCurrency(account_list_ordered[row]["totalBalance"] ) + '</div>';
                str = str + '</td>';
                str = str + '<td class="def_cur mark money"><div class="number '+colorClass+'">' + formatCurrency( account_list_ordered[row]["totalBalance"] * _model.getAccountCurrencyCost(account_list_ordered[row]['id']) / defaultCurrency['cost']) + '';
                summ[type] += (account_list_ordered[row]["totalBalance"] * _model.getAccountCurrencyCost(account_list_ordered[row]['id']) / defaultCurrency['cost']);
                str = str + '</div>' + div + '</td></tr>';
                arr[type] = arr[type] + str;
            }
        }
        var s = ''
        for(key in arr)//выводит конечный массив
        {
            if (arr[key]){
                s += '<tr class="parent '+(summ[key]>=0? 'sumGreen' : 'sumRed' )+'"><td colspan="4" class="name money"><strong style="color:black;display:block;float:left;position:relative">'+ g_name[key]
                    + ':</strong> ' + formatCurrency( summ[key] )
                    + '</tr>' +arr[key]; //head_tr+
            }
        }
        $('#accountsJournal .content').html('<table>' + s + '</table>');
//        $('.qtip').remove();
        _initBigTip();
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object
 