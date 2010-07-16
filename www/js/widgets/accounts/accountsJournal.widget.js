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
                easyFinance.widgets.accountEdit.showForm();
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

                easyFinance.widgets.accountEdit.showForm();
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

        var g_types = [1,1,1,1,1,1,2,3,3,3,4,4,4,4,5,1,1,1,1]; // Жуткий масив привязки типов к группам
        var g_name = ['Избранные','Деньги','Мне должны','Я должен','Инвестиции','Имущество','Архив'];//названия групп
        var arr = ['','','','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0,0,0];// сумма средств по каждой группе
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
        {
            // Если счёт избранный
            if (account_list_ordered[row]['state'] == "1") {
                type = 0;
            // Если это архив
            } else if(account_list_ordered[row]['state'] == "2") {
                type = 6;
            } else {
            type = g_types[account_list_ordered[row]['type']];
            }

            colorClass = account_list_ordered[row]["totalBalance"] >=0 ? 'sumGreen' : 'sumRed';
            var id = account_list_ordered[row]['id'];

            if (!isNaN(type)){
                str = '<tr title="' + getAccountTooltip(id) + '" class="item child" id="accountsJournalAcc_' + account_list_ordered[row]['id'] + '">';
                str = str + '<td class="name"><span style="white-space:nowrap;">' + shorter(account_list_ordered[row]["name"], 25) + '</span></td>';
                str = str + '<td class="totalBalance money"><div class="abbr">' + _model.getAccountCurrencyText(account_list_ordered[row]["id"]) + '</div>'+'<div class="number '+colorClass+'">' + formatCurrency(account_list_ordered[row]["totalBalance"], true, false) + '</div>';
                str = str + '</td>';
                str = str + '<td class="def_cur mark money"><div class="number '+colorClass+'">' + formatCurrency( account_list_ordered[row]["totalBalance"] * _model.getAccountCurrencyCost(account_list_ordered[row]['id']) / defaultCurrency['cost'], true, false) + '';
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
                    + ':</strong> ' + formatCurrency(summ[key], true, false)
                    + '</tr>' +arr[key]; //head_tr+
            }
        }

        // обновляем содержимое таблицы
        $('#accountsJournalTable').html(s);
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object
