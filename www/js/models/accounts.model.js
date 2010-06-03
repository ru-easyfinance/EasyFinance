/**
 * @desc Operation Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.accounts = function(){
    // constants
    var ACCOUNTS_LIST_URL = '/accounts/accountslist/?responseMode=json';
    var ADD_ACCOUNT_URL = '/my/account.json';
    var EDIT_ACCOUNT_URL = '/accounts/edit/?responseMode=json';
    var DELETE_ACCOUNT_URL = '/accounts/delete/?responseMode=json&confirmed=1';

    var OPERATIONS_JOURNAL_URL = '/operation/listOperations/?responseMode=json';
    var OPERATIONS_JOURNAL_CSV_URL = '/operation/listOperations/?responseMode=csv';

    var ACCEPT_OPERATIONS_URL = '/calendar/accept_all/?responseMode=json&confirmed=1';
    var DELETE_OPERATIONS_URL = '/operation/del_all/?responseMode=json&confirmed=1';

    var ADD_OPERATION_URL = '/operation/add/?responseMode=json';
    var EDIT_OPERATION_URL = '/operation/edit/?responseMode=json';
    var EDIT_OPERATION_DATE_URL = '/calendar/edit_date/?responseMode=json';

    var ADD_CHAIN_URL = '/calendar/add/?responseMode=json';
    var EDIT_CHAIN_URL = '/calendar/edit/?responseMode=json';
    var DELETE_CHAIN_URL = '/calendar/del_chain/?responseMode=json';

    // private variables
    var _this = null;

    var _accounts;
    var _accountsOrdered;
    var _journal;

    // private functions
    function _compareAccountsOrderByName(a, b) {
        if (!a || !b || !a.name || !b.name) {
            alert('sss');
            return 0;
        }

        var strA = a.name.toString().toLowerCase();
        var strB = b.name.toString().toLowerCase();

        if (!strA.localeCompare || !strB) {
            alert('bbb');
        }

        return strA.localeCompare(strB);
    }

    function _orderAccounts() {
        _accountsOrdered = new Array();

        for (var key in _accounts) {
            _accountsOrdered.push(_accounts[key]);
        }

        if (isChrome) {
            // сортируем по алфавиту специально для Хрома
            _accountsOrdered.sort(_compareAccountsOrderByName);
        }
    }

    function _loadAccounts(callback) {
        $.post(ACCOUNTS_LIST_URL, '', function(data) {
            if (data.result) {
                _accounts = $.extend(true, {}, data.result.data);
                _orderAccounts();

                $(document).trigger('accountsLoaded');
            }

            if (typeof callback == 'function')
                callback(_accounts);
        }, "json");
    }

    // public variables

    // public functions

    /**
     * @desc initialize
     * @usage load(callback)
     */
    function load(modelCurrency, param1, param2){
        _this = this;

        _modelCurrency = modelCurrency;

        if (typeof param1 == 'object'){
            _accounts = param1;
            _orderAccounts();

            $(document).trigger('accountsLoaded');

            $(document).trigger('accountsLoaded');

            if (typeof param2 == 'function')
                param2(_this);
        } else {
            if (typeof param1 == 'function')
                _loadAccounts(function(){param1(_this);});
            else
                _loadAccounts();
        }
    }

    function getAccounts(){
        return $.extend(true, {}, _accounts);
    }

    function getAccountsOrdered(){
        return $.extend(true, {}, _accountsOrdered);
    }

    function getAccountById(id){
        if (!_accounts || !_accounts[id])
            return null;

        return $.extend(true, {}, _accounts[id]);
    }

    function getAccountNameById(id){
        if (!_accounts)
            return null;

        if (_accounts[id])
            return _accounts[id]["name"];
        else
            return '';
    }

    function getAccountType(id){
        if (_accounts && _accounts[id])
            return _accounts[id]["type"];
        else
            return null;
    }

    function getAccountCurrencyId(id){
        if (_accounts && _accounts[id])
            return _accounts[id].currency;
        else
            return null;
    }

    function getAccountCurrency(id){
        if (_accounts && _accounts[id])
            return easyFinance.models.currency.getCurrencyById(_accounts[id].currency);
        else
            return null;
    }

    function getAccountCurrencyCost(id){
        if (_accounts && _accounts[id])
            return easyFinance.models.currency.getCurrencyById(_accounts[id].currency).cost;
        else
            return null;
    }

    function getAccountCurrencyText(id){
        if (_accounts && _accounts[id])
            return easyFinance.models.currency.getCurrencyById(_accounts[id].currency).text;
        else
            return null;
    }

    function getAccountIdByName(name){
        if (!_accounts)
            return null;

        for (var key in _accounts) {
            if (_accounts[key]["name"] == name) {
                return key;
            }
        }

        return null;
    }

    function getAccountBalanceTotal(id){
        if (_accounts && _accounts[id])
            return _accounts[id]["totalBalance"];
        else
            return null;
    }

    function getAccountBalanceAvailable(id){
        if (_accounts && _accounts[id])
            return _accounts[id]["totalBalance"] - _accounts[id]["reserve"];
        else
            return null;
    }

    function getAccountTypeString(id){
        var types = {1: "Наличные", 2: "Дебетовая карта", 9: "Кредит", 5: "Депозит", 6: "Займ выданный", 7: "Займ полученый", 8: "Кредитная карта", 15: "Электронный кошелек", 16: "Банковский счёт"};

        if (_accounts && _accounts[id])
            return types[_accounts[id].type];
        else
            return '';
    }

    function addAccount(params, callback){
        if (typeof params != "object")
            return false;

        // тип счёта, имя, валюта -
        // обязательные параметры для всех счетов
        if (!params.type || !params.name || !params.currency)
            return false;

        $.post(ADD_ACCOUNT_URL, {
                currency_id: params.currency,
                type_id:     params.type,
                name:        params.name,
                comment:     params.description,
                initPayment: params.initPayment
            }, function(data){
            var _id = (data.result && data.result.id) ? data.result.id : null;

            _loadAccounts(function() {
                var event = $.Event("accountAdded");
                event.id = _id;
                $(document).trigger(event);
            });

            if (callback)
                callback(data);
        }, 'json');
    }

    function editAccountById(id, params, callback){
        if (typeof params != "object")
            return false;

        // ID, тип счёта, имя, валюта -
        // обязательные параметры для всех счетов
        params.id = id;
        $.post(EDIT_ACCOUNT_URL, params, function(data){
                _loadAccounts(function() {
                    $(document).trigger('accountEdited');
                });

/*
                if (data.result) {
                    var balanceDiff = parseFloat(params.initPayment) - parseFloat(_accounts[id].initPayment);

                    _accounts[id].name = params.name;
                    _accounts[id].comment = params.comment;
                    _accounts[id].currency = params.currency;
                    _accounts[id].totalBalance = parseFloat(_accounts[id].totalBalance) + balanceDiff;
                    _accounts[id].initPayment = params.initPayment;
                }

                @todo: calc defCur
*/
                if (callback)
                    callback(data);
            }, 'json'
        );
    }

    function deleteAccountById(id, callback) {
        $.post(DELETE_ACCOUNT_URL, {id:id, confirmed:1}, function(data){
            if (data.result) {
                delete _accounts[id];
                _orderAccounts();
                $(document).trigger('accountDeleted');
            }

            if (callback)
                callback(data);
        }, 'json');
    }

    /**
     * @desc read initial data from json/server
     * @usage ---loadJournal(json)
     * @usage ---loadJournal(json, callback)
     * @usage loadJournal(account, category, dateFrom, dateTo, sumFrom, sumTo, type, callback)
     */
    function loadJournal(param1, param2, param3, param4, param5, param6, param7, param8, param9, csv){
        //if (typeof param1 == 'string') {
        //    _journal = param1;
        //    if (typeof param2 == 'function')
        //        param2(_journal);
        //} else {
            // load from server
            if (csv == true) window.location = OPERATIONS_JOURNAL_CSV_URL + "&account="+param1+"&category="+param2+
                "&dateFrom="+param3+"&dateTo="+param4+"&sumFrom="+param5+
                "&sumTo="+param6+"&type="+param7+"&search_field="+param8;
    else

        $.get(OPERATIONS_JOURNAL_URL, {
                    account: param1,
                    category: param2,
                    dateFrom: param3,
                    dateTo: param4,
                    sumFrom: param5,
                    sumTo: param6,
                    type: param7,
            search_field : param8
                }, function(data) {
                    _journal = data.operations;
                    if (typeof param9 == 'function')
                        param9(data);
            }, 'json');
        //}
    }

    // получаем списки просроченных и будущих событий
    function loadEvents(callback){

    }

    // получаем список просроченных событий
    function loadOverdueEvents(param1, param2){

    }

    function getOverdueOperationById(id){
        if (!res.calendar || !res.calendar.overdue)
            return null;

        for (var row in res.calendar.overdue) {
            if (res.calendar.overdue[row].id == id)
                return $.extend({}, res.calendar.overdue[row]);
        }
    return null;
    }

    function getFutureOperationById(id){
        if (!res.calendar || !res.calendar.future)
            return null;

        for (var row in res.calendar.future) {
            if (res.calendar.future[row].id == id)
                return $.extend({}, res.calendar.future[row]);
        }
    return null;
    }

    function acceptOperationsByIds(ids, callback) {
        var _ids = ids;

        $.post(ACCEPT_OPERATIONS_URL, {ids : ids.toString() }, function(data) {
            // update accounts
            _loadAccounts();

            if (data.result) {
                for (var key in _ids) {
                    // удаляем из списка просроченных операций
                    for (var row in res.calendar.overdue) {
                        if (res.calendar.overdue[row].id == _ids[key])
                            delete res.calendar.overdue[row];
                    }

                    // удаляем из списка напоминаний
                    for (var row in res.calendar.future) {
                        if (res.calendar.future[row].id == _ids[key])
                            delete res.calendar.future[row];
                    }
                }

                var event = $.Event("operationsAccepted");
                event.ids = _ids;
                $(document).trigger(event);
            }

            if (typeof callback == "function")
                callback(data);
        }, 'json');
    }

    /**
     * @desc delete operations by id
     * @param ids: array of operation ids
     * @param isVirts: ids of virtual operations
     * @param callback: callback function
     */
    function deleteOperationsByIds(ids, isVirts, callback) {
        var _ids = null;
        if (typeof ids == "string")
            _ids = [ids];
        else
            _ids = ids;

        $.post(DELETE_OPERATIONS_URL, {id : ids.toString(), virt: isVirts.toString()}, function(data) {
                // update accounts
                _loadAccounts();

                var i, tr_id, row;

                if (_journal) {
                    for (i=0; i<_ids.length; i++) {
                        // delete paired transfer if exists
                        if (_journal[_ids[i]]) {
                            tr_id = _journal[_ids[i]].tr_id
                            if (tr_id !== null && tr_id != "0") {
                                delete _journal[tr_id];
                            }
                        }

                        // delete operation
                        delete _journal[_ids[i]];
                    }
                }

                for (i=0; i<_ids.length; i++) {
                    // удаляем из списка просроченных операций
                    for (row in res.calendar.overdue) {
                        if (res.calendar.overdue[row].id == _ids[i])
                            delete res.calendar.overdue[row];
                    }

                    // удаляем из списка будущих операций
                    for (row in res.calendar.future) {
                        if (res.calendar.future[row].id == _ids[i])
                            delete res.calendar.future[row];
                    }
                }

                var event = $.Event("operationsDeleted");
                event.ids = $.extend({}, _ids);
                $(document).trigger(event);

                if (typeof callback == "function")
                    callback(data);
        }, 'json');
    }

    function deleteOperationsChain(chainId, callback){
        var _chainId = chainId;
        $.post(DELETE_CHAIN_URL, {chain : chainId}, function(data) {
                // удаляем из списка просроченных операций
                for (row in res.calendar.overdue) {
                    if (res.calendar.overdue[row].chain == _chainId)
                        delete res.calendar.overdue[row];
                }

                // удаляем из списка будущих операций
                for (row in res.calendar.future) {
                    if (res.calendar.future[row].chain == _chainId)
                        delete res.calendar.future[row];
                }

                var event = $.Event("operationsChainDeleted");
                event.id = _chainId;
                $(document).trigger(event);

                if (callback)
                    callback(data);
        }, 'json');
    }

    function addOperation(
        type, account, category, date, comment,
        amount, toAccount, currency, convert,
        target, close, tags,
        callback) {
            editOperationById(
                '', "1", type, account, category, date,
                comment, amount, toAccount, currency,
                convert, target, close, tags
            );
    }

    // создание цепочки операций
    // используется при планировании
    // ("добавление в календарь")
    function addOperationsChain(
        type, account, category, date, comment,
        amount, toAccount, currency, convert,
        target, close, tags, time, last, every, repeat, week,
        callback) {
            editOperationById(
                '', "0", type, account, category, date,
                comment, amount, toAccount, currency,
                convert, target, close, tags,
                null, time, last, every, repeat, week
            );
    }

    function editOperationDateById(operationId, newDate, callback) {
        $.post(EDIT_OPERATION_DATE_URL, {id: operationId, date: newDate}, function(data){
            if (data.result) {
                var event = $.Event("operationDateEdited");
                event.id = operationId;
                event.date = newDate;
                $(document).trigger(event);
            }

            if (typeof callback == "function")
                callback(data);
        }, "json");
    }

    function editOperationById(
        id, accepted, type, account, category, date, comment,
        amount, toAccount, currency, convert,
        target, close, tags,
        chain, time, last, every, repeat, week, // параметры для цепочек операций
        callback){
            // параметры для обычной транзакции
            var params = {
                id        : id,
                accepted  : accepted,
                type      : type,
                account   : account,
                date      : date,
                comment   : comment,
                category  : category,
                amount    : amount,
                toAccount : toAccount,
                currency  : currency,
                convert   : convert,
                target    : target,
                close     : close,
                tags      : tags
            };

            var url = '';
            if (typeof chain == "string") {
                // регулярная транзакция
                url = (id == '') ? ADD_CHAIN_URL : EDIT_CHAIN_URL;

                // расширяем список параметров
                params.chain = chain;
                params.time = time;
                params.last = last;
                params.every = every;
                params.repeat = repeat;
                params.week = week;
            } else {
                url = (id == '') ? ADD_OPERATION_URL : EDIT_OPERATION_URL;
            }

            $.post(url, params, function(data){
                // @todo: update totalBalance!

                var event, k;
                var props = ["calendar", "overdue", "future"];
                if (url == ADD_OPERATION_URL) {
                    event = $.Event('operationAdded');
                } else if (url == EDIT_OPERATION_URL) {
                    event = $.Event('operationEdited');

                    if (data.operation) {
                        // обновляем информацию о событии в наших списках
                        for (k in props) {
                            for (var j in res.calendar[props[k]]) {
                                if (res.calendar[props[k]][j].id == data.operation.id) {
                                    res.calendar[props[k]][j] = $.extend({}, data.operation);
                                }
                            }
                        }
                    }
                } else if (url == ADD_CHAIN_URL) {
                    event = $.Event('operationsChainAdded');
                } else if (url == EDIT_CHAIN_URL) {
                    event = $.Event('operationsChainEdited');
                }
                event["operation"] = data.operation || null;

                for (k in props) {
                    if (data[props[k]]) {
                        res.calendar[props[k]] = $.extend({}, data[props[k]]);
                        event[props[k]] = res[props[k]];
                        delete data[props[k]];
                    }
                }

                $(document).trigger(event);

                _loadAccounts();

                callback(data);
            }, "json");
    }

    function editOperationsChain(){

    }

    // reveal some private things by assigning public pointers
    return {
        load: load,
        getAccounts: getAccounts,
        getAccountsOrdered: getAccountsOrdered,
        getAccountById: getAccountById,

        getAccountNameById: getAccountNameById,
        getAccountIdByName: getAccountIdByName,
        getAccountType: getAccountType,
        getAccountTypeString: getAccountTypeString,

        getAccountCurrencyId: getAccountCurrencyId,
        getAccountCurrency: getAccountCurrency,
        getAccountCurrencyCost: getAccountCurrencyCost,
        getAccountCurrencyText: getAccountCurrencyText,

        getAccountBalanceTotal: getAccountBalanceTotal,
        getAccountBalanceAvailable: getAccountBalanceAvailable,
        acceptOperationsByIds : acceptOperationsByIds,
        addAccount: addAccount,
        editAccountById: editAccountById,
        deleteAccountById: deleteAccountById,

        addOperation: addOperation,
        editOperationDateById: editOperationDateById,
        editOperationById: editOperationById,
        deleteOperationsByIds: deleteOperationsByIds,

        addOperationsChain: addOperationsChain,
        editOperationsChain: editOperationsChain,
        deleteOperationsChain: deleteOperationsChain,

        loadJournal: loadJournal,
        loadEvents: loadEvents,
        //loadOverdueEvents: loadOverdueEvents,
        //loadFutureEvents: loadFutureEvents

        getOverdueOperationById: getOverdueOperationById,
        getFutureOperationById: getFutureOperationById
    };
}(); // execute anonymous function to immediatly return object
