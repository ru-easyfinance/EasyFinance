/**
 * @desc Accounts Panel Widget
 * appears in left column
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.accountsPanel = function(){
    // private constants

    // private variables
    var _model = null;
    var _modelCurrency = null;
    var _$node = null;

    var _accounts = null;

    // private functions
    function _initBigTip(){
        $('.bill_list li.account:not(.add)').each(function(){
            var defaultCurrency = _modelCurrency.getDefaultCurrency();
            var id =$(this).find('div.id').attr('value').replace("edit", "");
            _accounts = _model.getAccounts();
            var account = _accounts[id];

            if (account) {
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
        _modelCurrency = easyFinance.models.currency;
        _accounts = _model.getAccounts();

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
                    document.location='/accounts/#edit'+id;
                    // для события на странице /accounts
                    accounts_hash_api('#edit'+id);
                } else if (parentClass == "add") {
                    document.location='/accounts/#copy'+id;
                    // для события на странице /accounts
                    accounts_hash_api('#edit'+id, true);
                } else if (parentClass == "del") {
                    if (confirm("Вы уверены что хотите удалить счёт?")) {
                        var id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");

                        _model.deleteAccountById(id, function(data){
                            // выводим ошибку, если на счету зарегистрированы фин.цели.
                            if (data.error && data.error.text) {
                                $.jGrowl(data.error.text, {theme: 'red'});
                            } else if (data.result && data.result.text) {
                                $.jGrowl(data.result.text, {theme: 'green'});
                            }
                        });
                    }
                }
            }
        });

        $('.accounts li .cont').live('click', function(){
            // #1349. do nothing!
            return false;
        });

        return this;
    }

    function redraw(){
        var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0,0];
//        var g_name = ['Деньги','Мне должны','Я должен','Инвестиции','Имущество'];//названия групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var val = {};//сумма средств по каждой используемой валюте

        if (!_model)
            return;

        _accounts = _model.getAccounts();

        var data = $.extend({},_model.getAccountsOrdered());

        if (!data){
            data = {};
        }

        var i = 0;
        var total = 0;
        var str = '';
        var key;
        var s = '';
        for (key in data )
        {
            i = g_types[data[key]['type']];
            str = '<li class="account"><a>';
            str = str + '<div style="display:none" class="type" value="'+data[key]['type']+'" />';
            str = str + '<div style="display:none" class="id" value="'+data[key]['id']+'" />';
            str = str + '<span>'+shorter(data[key]['name'], 20)+'</span><br>';
            str = str + '<span class="noTextDecoration ' + (data[key]['totalBalance']>=0 ? 'sumGreen' : 'sumRed') + '">'
                + formatCurrency(data[key]['totalBalance']) + '</span>&nbsp;';
            str = str + _model.getAccountCurrencyText(data[key]['id']) + '</span></a>';

            str = str + '<div class="cont">'
                       + '<ul style="z-index: 1006;">'
                            + '<li title="Добавить операцию" class="operation"><a></a></li>'
                            + '<li title="Редактировать" class="edit"><a></a></li>'
                            + '<li title="Удалить" class="del"><a></a></li>'
                            + '<li title="Копировать" class="add"><a></a></li>'
                       + '</ul></div>';

            str = str + '</li>';

            //if ( i!=2 ){
            // перевод в валюту по умолчанию
                summ[i] = summ[i]+data[key]["totalBalance"] * _model.getAccountCurrencyCost(data[key]["id"]) / easyFinance.models.currency.getDefaultCurrencyCost();
            /*}else{
                summ[i] = summ[i]-data[key]['defCur'];
            }*/

            if (!val[data[key]['currency']]) {
                val[data[key]['currency']]=0;
            }

            //if ( i!=2 ){
            val[data[key]['currency']] = parseFloat( val[data[key]['currency']] )
                + parseFloat(data[key]['totalBalance']);
            /*}else{
                 val[data[key]['currency']] = parseFloat( val[data[key]['currency']] )
                - parseFloat(data[key]['totalBalance']);
            }*/

            arr[i] = arr[i]+str;
        }
        total = 0;
        for(key in arr)
        {
            total = total+parseFloat(summ[key]);
            s='<ul>'+arr[key]+'</ul>';
            if (key>=0 && key <=6)
                _$node.find('#accountsPanelAcc'+key).html(s);
            if (s!='<ul></ul>')
                _$node.find('#accountsPanelAcc'+key).show().prev().show();
            else
                _$node.find('#accountsPanelAcc'+key).hide().prev().hide();
        }

        // формирование итогов
        str = '<ul>';

        i = 0;
        for(key in val) {
            i++;
            str = str+'<li><div class="' + (val[key]>=0 ? 'sumGreen' : 'sumRed') + '">'+formatCurrency(val[key])+' <span class="currency">&nbsp;'+ easyFinance.models.currency.getCurrencyTextById(key) +'</span></div></li>';
        }
        str = str+'<li><div class="' + (total>=0 ? 'sumGreen' : 'sumRed') + '"><strong style="color: black; position:relative; float: left;">Итого:</strong> <br>'+formatCurrency(total)+' <span class="currency"><br>&nbsp;'+easyFinance.models.currency.getDefaultCurrencyText()+'</span></div></li>';
        str = str + '</ul>';
        _$node.find('#accountsPanel_amount').html(str);
        _initBigTip();
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
