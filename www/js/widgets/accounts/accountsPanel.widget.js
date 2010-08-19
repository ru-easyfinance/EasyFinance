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

                    _model.addAccountToFavouriteById(id, function(data){
                        if (data.error && data.error.text) {
                            $.jGrowl(data.error.text, {theme: 'red'});
                        } else if (data.result && data.result.text) {
                            $.jGrowl(data.result.text, {theme: 'green'});
                        }
                    });
                } else if (parentClass == "del") {
                	var params = {};
                	params.id = $(this).closest(".account").find('div.id').attr('value').replace("edit", "");
                	params.state = $(this).closest(".account").find('div.state').attr('value').replace("edit", "");
                	confirmDeletion(params);
                }
            }
        });

        $('.accounts li .cont').live('click', function(){
            // #1349. do nothing!
            return false;
        });

        return this;
    }
    
    function confirmDeletion(params){
    	$(".account_deletion_confirm").dialog({
    		autoOpen: false,
    		title: "Предупреждение",
    		modal: true,
			buttons: {
				"Отмена": function() {
    				$(this).dialog('close');
				},
				"Удалить": function() {
					_model.deleteAccountById(params.id, function(data){
                        // выводим ошибку, если на счету зарегистрированы фин.цели.
                        if (data.error && data.error.text) {
                            $.jGrowl(data.error.text, {theme: 'red'});
                        } else if (data.result && data.result.text) {
                            $.jGrowl(data.result.text, {theme: 'green'});
                        }
                    });
				},
				"Скрыть": function() {
					var handler = function(data) {
			            if (data.result && data.result.text) {
			                $.jGrowl(data.result.text, {theme: 'green'});
			            }else if (data.error && data.error.text) {
			                $.jGrowl(data.error.text, {theme: 'red'});
			            }	
			            
			            $(".account_deletion_confirm").dialog("close")
			            
			        };
				        
			        $.jGrowl("Ждите", {theme: 'green'});
			        
			        _model.hideAccountById(params.id, handler);
				}
			}
    	});
    	$(".account_deletion_confirm").dialog("open");
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

        var i = 0;
        var total = 0;
        var str = '';
        var key;
        var s = '';
        for (key in data )
        {
            if (data[key].state == "2") {
                i = 'Archive';
            } else {
                i = g_types[data[key]['type']];
            }
            str = '<li class="account" title="' + getAccountTooltip(data[key].id) + '"><a>';
            str = str + '<div style="display:none" class="type" value="'+data[key]['type']+'" />';
            str = str + '<div style="display:none" class="id" value="'+data[key]['id']+'" />';
            str = str + '<div style="display:none" class="state" value="'+data[key]['state']+'" />';
            str = str + '<span>'+htmlEscape(shorter(data[key]['name'], 20))+'</span><br>';
            str = str + '<span class="noTextDecoration ' + (data[key]['totalBalance']>=0 ? 'sumGreen' : 'sumRed') + '">'
                + formatCurrency(data[key]['totalBalance'], true) + '</span>&nbsp;';
            str = str + _model.getAccountCurrencyText(data[key]['id']) + '</span></a>';

            str = str + '<div class="cont">'
                       + '<ul style="z-index: 1006;">'
                            + '<li title="Добавить операцию" class="operation"><a></a></li>'
                            + '<li title="Редактировать" class="edit"><a></a></li>'
                            + '<li title="Удалить" class="del"><a></a></li>'
                            + '<li title="В избранные" class="favourite"><a></a></li>'
                       + '</ul></div>';

            str = str + '</li>';

            if (data[key].state != "2") {
	            summByGroups[i] = summByGroups[i]+data[key]["totalBalance"] * _model.getAccountCurrencyCost(data[key]["id"]) / easyFinance.models.currency.getDefaultCurrencyCost();
	            if (!summByCurrencies[data[key]['currency']]) {
		           summByCurrencies[data[key]['currency']] = 0;
		        }

	            summByCurrencies[data[key]['currency']] =
	                parseFloat(summByCurrencies[data[key]['currency']])
	                + parseFloat(data[key]['totalBalance']);
            }

            innerHtmlByGroups[i] = innerHtmlByGroups[i] ?
                innerHtmlByGroups[i] + str : str;

            if (data[key].state == '1') {
                innerHtmlByGroups[0] = innerHtmlByGroups[i];
            }
        }
        total = 0;
        for(key in innerHtmlByGroups)
        {
            total = summByGroups[key] ?
                total + parseFloat(summByGroups[key]) : total;
            s='<ul class="efListWithTooltips">'+innerHtmlByGroups[key]+'</ul>';
            if (key>=0 && key <=7 || key == 'Archive') {
                _$node.find('#accountsPanelAcc'+key).html(s);
            }

            if (innerHtmlByGroups[key] != '') {
                _$node.find('#accountsPanelAcc'+key).show().prev().show();
            } else {
                _$node.find('#accountsPanelAcc'+key).hide().prev().hide();
            }
        }

        // формирование итогов
        str = '<ul>';

        for(key in summByCurrencies) {
            str = str+'<li><div class="' + (summByCurrencies[key]>=0 ? 'sumGreen' : 'sumRed') + '">'+formatCurrency(summByCurrencies[key], true, true)+' <span class="currency">&nbsp;'+ easyFinance.models.currency.getCurrencyTextById(key) +'</span></div></li>';
        }
        str = str+'<li><div class="' + (total>=0 ? 'sumGreen' : 'sumRed') + '"><strong style="color: black; position:relative; float: left;">Итого:</strong> <br>'+formatCurrency(total, true, true)+' <span class="currency"><br>&nbsp;'+easyFinance.models.currency.getDefaultCurrencyText()+'</span></div></li>';
        str = str + '</ul>';
        _$node.find('#accountsPanel_amount').html(str);

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

        if (!accountsPanel) {
            $('#accountsPanelAccArchive').prev().click();
        }

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
