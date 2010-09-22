/**
 * @desc Accounts Panel Widget
 * appears in left column
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.accountsPanel = function(){
    // private constants

    // private variables
    var _model = null;
    var _$node = null;

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
        $(document).bind('accountDeleted', redraw);

       $('.accounts .add,.accounts .addaccountlink').click(function(){
            // #1095. создание счёта по ссылке из экрана профиля
            if (document.location.pathname.indexOf("profile") != -1) {
                // переходим на страницу счетов и открываем диалог
                document.location = '/accounts/#add';
            } else {
                // отображает форму создания счёта
                easyFinance.widgets.accountEdit.addAccount();
            }
       })

       $('.accounts li a').live('click',function(evt){
            var id;

            if ($(this).parent().hasClass("account")) {
                // add operation
                if ($(this).parent().hasClass("account") == "account")
                    id = $(this).find('div.id').attr('value').replace("edit", "");
                else
                    id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");

                document.location='/operation/#account='+id;
                $('tr.item#'+$(this).find('div.id').attr('value')).dblclick();
                //временный хак до полного перехода на события

                if (easyFinance.widgets.operationEdit && pathName == '/operation/'){
                    easyFinance.widgets.operationEdit.setAccount(id);
                }

                if (easyFinance.widgets.operationsJournal){
                    easyFinance.widgets.operationsJournal.setAccount(id);
                    easyFinance.widgets.operationsJournal.loadJournal();
                }
            } else {
                // menu action
                id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                var parentClass = $(this).parent().attr("class");
                if ($(this).parent().hasClass("operation")) {
                    if (easyFinance.widgets.operationEdit) {
                        easyFinance.widgets.operationEdit.showForm();
                        easyFinance.widgets.operationEdit.setAccount(id);
                    } else {
                        document.location='/operation/#account='+id;
                    }
                } else if (parentClass == "edit") {
                    if (document.location.pathname.indexOf("profile") == -1) {
                        easyFinance.widgets.accountEdit.editAccountById(id);
                    } else {
                        document.location='/accounts/#edit'+id;
                    }
                } else if (parentClass == "add") {
                    document.location='/accounts/#copy'+id;
                    // для события на странице /accounts
                    accounts_hash_api('#edit'+id, true);
                }else if (parentClass == "favourite") {
                    var id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");

                    _model.changeAccountStateById(id, 1, function(data){
                        if (data.error && data.error.text) {
                            $.jGrowl(data.error.text, {theme: 'red'});
                        } else if (data.result && data.result.text) {
                            $.jGrowl(data.result.text, {theme: 'green'});
                        }
                    });
                } else if (parentClass == "del") {
                    var id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                    confirmDeletion(id);
                }
            }
        });

        $('.accounts li .cont').live('click', function(){
            // #1349. do nothing!
            return false;
        });

        return this;
    }

    function confirmDeletion(id){
        $("#accountDeletionConfirm").dialog({
            autoOpen: false,
            title: "Предупреждение",
            modal: true,
            buttons: {
                "Отмена": function() {
                    $(this).dialog('close');
                },
                "Удалить": function() {
                    _model.deleteAccountById(id, function(data){
                        // выводим ошибку, если на счету зарегистрированы фин.цели.
                        if (data.error && data.error.text) {
                            $.jGrowl(data.error.text, {theme: 'red'});
                        } else if (data.result && data.result.text) {
                            $.jGrowl(data.result.text, {theme: 'green'});
                        }
                    });
                    $(this).dialog('close');
                },
                "Скрыть": function() {
                    var handler = function(data) {
                        if (data.result && data.result.text) {
                            $.jGrowl(data.result.text, {theme: 'green'});
                        }else if (data.error && data.error.text) {
                            $.jGrowl(data.error.text, {theme: 'red'});
                        }

                        $("#accountDeletionConfirm").dialog("close")

                    };

                    $.jGrowl("Ждите", {theme: 'green'});

                    _model.changeAccountStateById(id, 2, handler);
                }
            }
        });
        $("#accountDeletionConfirm").dialog("open");
    }

    function getAccountsWithoutArchive(accounts){
        for(k in accounts){
            if (accounts[k].state == "2") {
                delete (accounts[k]);
            }
        }
        return accounts;
    }

    function redraw(){
//        var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0,0];
        var g_types = [1,1,1,1,1,1,2,3,3,3,4,4,4,4,5,1,1];
//        var g_name = ['Деньги','Мне должны','Я должен','Инвестиции','Имущество'];//названия групп
        var innerHtmlByGroups = ['','','','','',''];//содержимое каждой группы
        innerHtmlByGroups['Archive'] = '';
        var summByGroups = [0,0,0,0,0,0];// сумма средств по каждой группе
        var summByCurrencies = {};//сумма средств по каждой используемой валюте

        if (!_model)
            return;

        var data = $.extend({},_model.getAccountsOrdered());

        if (!data){
            data = {};
        }

        var i = 0,
            total = 0,
            str = '',
            key,
            s = '',
            datum,
            current_acc_panel,
            groupSum;


        var li_template =
            '<li class="account" title="{%tooltip_title%}">\
                <a>\
                    <div style="display:none" class="type" value="{%type%}" />\
                    <div style="display:none" class="id" value="{%id%}" />\
                    <div style="display:none" class="state" value="{%state%}" />\
                    <span>{%shorter_name%}</span><br/>\
                    <span class="{%balance_color%}">{%totalBalance%}</span>&nbsp;\
                    {%currencyName%}\
                </a>\
                <div class="cont">\
                    <ul style="z-index: 1006;">\
                        <li title="Добавить операцию" class="operation"><a></a></li>\
                        <li title="Редактировать" class="edit"><a></a></li>\
                        <li title="Удалить" class="del"><a></a></li>\
                        <li title="В избранные" class="favourite"><a></a></li>\
                     </ul>\
                </div>\
            </li>';

        var val = {};

        for (key in data ) {
            datum = data[key];
            if (data[key].state == "2") {
                i = 'Archive';
            }
            else {
                i = g_types[datum['type']];
            }

            val = {
                "tooltip_title": getAccountTooltip(datum.id),
                "type": datum['type'],
                "id": datum['id'],
                "state": datum['state'],
                "shorter_name": htmlEscape(shorter(datum['name'], 20)),
                "balance_color": datum['totalBalance'] >= 0 ? 'sumGreen' : 'sumRed',
                "totalBalance": formatCurrency(datum['totalBalance'], true),
                "currencyName": _model.getAccountCurrencyText(datum['id'])
            }

            str = templetor(li_template, val);

            if (datum.state != "2") {
                summByGroups[i] = summByGroups[i] +
                    datum["totalBalance"] * _model.getAccountCurrencyCost(datum["id"]) / easyFinance.models.currency.getDefaultCurrencyCost();
                if (!summByCurrencies[datum['currency']]) {
                   summByCurrencies[datum['currency']] = 0;
                }

                summByCurrencies[datum['currency']] =
                    parseFloat(summByCurrencies[datum['currency']])
                    + parseFloat(datum['totalBalance']);
            }

            innerHtmlByGroups[i] = (innerHtmlByGroups[i] ? innerHtmlByGroups[i] + str : str);

            if (datum.state == '1') {
                innerHtmlByGroups[0] = innerHtmlByGroups[0] ? innerHtmlByGroups[0] + str : str;
            }
        }

        total = 0;
        for(key in innerHtmlByGroups) {

            total = summByGroups[key] ? total + parseFloat(summByGroups[key]) : total;
            s = '<ul class="efListWithTooltips">' + innerHtmlByGroups[key] + '</ul>';

            current_acc_panel = _$node.find('#accountsPanelAcc' + key);

            if (key >= 0 && key <=7 || key == 'Archive') {
                current_acc_panel.html(s);
            }

            if (innerHtmlByGroups[key] != '') {
                current_acc_panel
                    .removeClass('hidden')
                    .prev().removeClass('hidden');

                groupSum = current_acc_panel.prev().find('.sum')
                groupSum
                    .removeClass('sumGreen sumRed')
                    .addClass( summByGroups[key] > 0 ? 'sumGreen': 'sumRed' )
                    .html(
                        formatCurrency(summByGroups[key]) +
                        '<span class="currency">' +
                            easyFinance.models.currency.getDefaultCurrency().text +
                        '</span>'
                        );
            }
            else {
                current_acc_panel
                    .addClass('hidden')
                    .prev().addClass('hidden');
            }
        }

        // формирование итогов
        var total_template =
            '<ul>\
                {%rows%}\
                <li>\
                    <div class="{%summColor%}">\
                        <strong style="color: black; position:relative; float: left;">Итого:</strong><br/>\
                        {%amount%}\
                        <span class="currency"><br\>&nbsp;{%currencyName%}</span>\
                    </div>\
                </li>\
            </ul>';

        var total_row_template =
            '<li>\
                <div class="{%color%}">\
                    {%amount%}\
                    <span class="currency">&nbsp;{%currency_name%}</span>\
                </div>\
            </li>'


        var rows = [];
        for(key in summByCurrencies) {
            rows.push(
                templetor(
                    total_row_template,
                    {
                        color: summByCurrencies[key] >= 0 ? 'sumGreen' : 'sumRed',
                        amount: formatCurrency(summByCurrencies[key], true, true),
                        currency_name: easyFinance.models.currency.getCurrencyTextById(key)
                    }
                )
            )
        }

        val = {
            summColor: total>=0 ? 'sumGreen' : 'sumRed',
            amount: formatCurrency(total, true, true),
            currencyName: easyFinance.models.currency.getDefaultCurrencyText(),
            rows: rows.join('')
        }

        _$node.find('#accountsPanel_amount').html(templetor(total_template, val));

        $('div.listing dl.bill_list dt').addClass('open');
        $('div.listing dl.bill_list dt').live('click', function(){
            $(this).toggleClass('open').next().toggle();
            //запоминание состояние в куку
            var accountsPanel = '';
            $('div.listing dl.bill_list dd:visible').each(function(){
                accountsPanel += $(this).attr('id');
            })
            var isSecure = window.location.protocol == 'https'? 1:0
            $.cookie('accountsPanel_stated', accountsPanel, {expire: 100, path : '/', domain: false, secure : isSecure});
            return false;
        });
        //загружает состояние из
        var accountsPanel = $.cookie('accountsPanel_stated');

        if (accountsPanel){
            $('div.listing dl.bill_list dt:visible').each(function(){
                if (accountsPanel.toString().indexOf($(this).next().attr('id')) == -1)
                    $(this).click()
            })
        } else {
            $('div.listing dl.bill_list dd#accountsPanelAccArchive').prev().click();
        }

        $('div.listing dd.amount').live('click', function(){
            $(this).prev().click();
            return false;
        });
        //$('div.listing dl.bill_list dt').click();
        //$('div.listing dl.bill_list dt:last').click().addClass('open');
        //$('div.listing dl.bill_list dt').click().addClass('open');
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object
