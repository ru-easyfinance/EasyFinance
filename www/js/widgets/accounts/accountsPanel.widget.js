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

    var li_template =
        '<li class="account" title="{%tooltip_title%}">\
            <a class="b-accountitem">\
                <div style="display:none" class="type" value="{%type%}" />\
                <div style="display:none" class="id" value="{%id%}" />\
                <div style="display:none" class="state" value="{%state%}" />\
                <div class="b-accountitem-name">{%shorter_name%}</div>\
                <div class="b-accountitem-sum {%balance_color%}">\
                    <span class="b-accountitem-amount">{%totalBalance%}</span>\
                    <span class="b-accountitem-currency">{%currencyName%}</span>\
                </div>\
            </a>\
            <div class="cont cont-acc_panel">\
                <ul style="z-index: 1006;">\
                    <li title="Добавить операцию" class="operation"><a></a></li>\
                    <li title="Редактировать" class="edit"><a></a></li>\
                    <li title="Удалить" class="del"><a></a></li>\
                    <li title="В избранные" class="favourite"><a></a></li>\
                 </ul>\
            </div>\
        </li>';

    var header_sum_template =
        '<span class="b-accountgroup-amount">{%amount%}</span>\
        <span class="b-accountgroup-currency">{%currency%}</span>';

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

        $('div.listing dl.bill_list dt').live('click', function() {
            $(this).toggleClass('closed').next().toggleClass('hidden', $(this).hasClass('closed'));
            saveState();
            return false;
        });

        $('div.listing dd.amount').live('click', function() {
            $(this).prev().click();
            return false;
        });

        return this;
    }

    function confirmDeletion(id){
        var self = this;
        self.accountId = id;

        $("#accountDeletionConfirm").dialog({
            autoOpen: false,
            title: "Удалить или скрыть?",
            modal: true,
            buttons: {
                "Отмена": function() {
                    $(this).dialog('close');
                },
                "Удалить": function() {
                    _model.deleteAccountById(self.accountId, function(data){
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

                    _model.changeAccountStateById(self.accountId, 2, handler);
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

    function getSumTotal(data) {
        var summ = {}; // ключи -- id валют, значения -- суммы по валютам
        var acc;
        var overall = 0;

        for (var key in data) {
            acc = data[key];

            if (parseInt(acc.state) != 2) {
                if (!summ[acc.currency]) { // если в этой валюте еще ничего нет...
                   summ[acc.currency] = 0;
                }

                summ[acc.currency] = summ[acc.currency] + parseFloat(acc.totalBalance);
                overall += acc.totalBalance * _model.getAccountCurrencyCost(acc.id) / easyFinance.models.currency.getDefaultCurrencyCost();
            }
        }

        return {
            summ: summ,
            overall: overall
        }
    }

    function getSumGroup(group) {
        var balance = 0,
            acc;

        for (var i = 0, l = group.length; i < l; i++) {
            acc = group[i];
            balance += acc.totalBalance * _model.getAccountCurrencyCost(acc.id) / easyFinance.models.currency.getDefaultCurrencyCost();
        }
        return balance;
    }

    function renderAccount(accountDatum) {
        var val = {
            "tooltip_title": getAccountTooltip(accountDatum.id),
            "type": accountDatum.type,
            "id": accountDatum.id,
            "state": accountDatum.state,
            "shorter_name": htmlEscape(accountDatum.name),
            "balance_color": accountDatum.totalBalance >= 0 ? 'sumGreen' : 'sumRed',
            "totalBalance": formatCurrency(accountDatum.totalBalance, true),
            "currencyName": _model.getAccountCurrencyText(accountDatum.id)
        }

        return templetor(li_template, val);
    }

    function clearSubPanels() {
        _$node.find('dd:not(.amount)').each(function(){
            $(this).html('').html('').addClass('hidden').prev().addClass('hidden')
        })
    }

    function renderGroup(group, id) { log(id, group.length)
        var currentPanel = _$node.find('.js-type-' + id).next();

        var groupBalance = getSumGroup(group);

        var accounts = [];

        for (var i = 0, l = group.length; i < l; i++) {
            accounts.push(renderAccount(group[i]));
        }

        currentPanel
            .html('<ul class="efListWithTooltips">' + accounts.join('') + '</ul>')
            .removeClass('hidden')
            .prev().removeClass('hidden');

        var sumContainer = currentPanel.prev().find('span.js-acc-sum');

        sumContainer.removeClass('sumGreen sumRed');
        sumContainer.addClass( groupBalance > 0 ? 'sumGreen': 'sumRed' );
        sumContainer.html(
            templetor(header_sum_template, {
                'amount': formatCurrency(groupBalance),
                'currency': easyFinance.models.currency.getDefaultCurrency().text
            })
        );
    }

    function renderTotal(amount) {
        var rows = [];
        for(var currency in amount.summ) {
            rows.push(
                templetor(
                    total_row_template,
                    {
                        color: amount.summ[currency] >= 0 ? 'sumGreen' : 'sumRed',
                        amount: formatCurrency(amount.summ[currency], true, true),
                        currency_name: easyFinance.models.currency.getCurrencyTextById(currency)
                    }
                )
            )
        }

        var values = {
            summColor: amount.overall >= 0 ? 'sumGreen' : 'sumRed',
            amount: formatCurrency(amount.overall, true, true),
            currencyName: easyFinance.models.currency.getDefaultCurrencyText(),
            rows: rows.join('')
        }

        _$node.find('#accountsPanel_amount').html(templetor(total_template, values));

    }

    function groupAccountsByType(accounts) {
        var groups = {};
        var grouptype = '';
        var acc;

        // поскольку избранные отображаются дважды -- один раз в избранных и один раз в своей группе
        // то обходим исходные данные дважды:
        //      один раз выбираем някотку,
        //      второй раз просто группируем, невзирая на "любимость"

        groups['favourite'] = [];
        for (var key in accounts) {
            acc = accounts[key];
            if (acc.state == '1') {
                groups['favourite'].push(acc);
            }
        }

        for (key in accounts) {
            acc = accounts[key];
            grouptype = _model.getTypeNameForced(acc.id);
            if (!(grouptype in groups)) {
                groups[grouptype] = []
            }
            groups[grouptype].push( acc );

        }

        return groups;
    }

    function redraw() {
        if (!_model)
            return;

        var data = $.extend({},_model.getAccountsOrdered());

        if (!data){
            data = {};
        }

        var summByCurrencies = getSumTotal(data); // сумма средств по каждой используемой валюте и общая сумма

        var groupedAccounts = groupAccountsByType(data);

        clearSubPanels();

        for (var groupid in groupedAccounts) {
            renderGroup(groupedAccounts[groupid], groupid)
        }

        renderTotal(summByCurrencies);

        loadState();
    }

    function saveState() { //запоминание состояние захлопнутых групп в куку
        var accountsPanel = '';
        $('div.listing dl.bill_list dt.closed').each(function() {
            accountsPanel += $(this).next().attr('id'); // сохраняем список id захлопнутых групп
        })

        var isSecure = window.location.protocol == 'https' ? 1 : 0;
        $.cookie('accountsPanel_stated', accountsPanel, {expire: 100, path : '/', domain: false, secure : isSecure});
    }

    function loadState() { //загружает состояние из куки
        var accountsPanel = $.cookie('accountsPanel_stated');

        if (accountsPanel) {
            accountsPanel = accountsPanel.toString();
            $('div.listing dl.bill_list dt').each(function(){
                if (accountsPanel.indexOf($(this).next().attr('id')) > -1) {
                    $(this).addClass('closed').next().addClass('hidden');
                }
            })
        }
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object
