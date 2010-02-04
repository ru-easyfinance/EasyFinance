/**
 * @desc Operation Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.accounts = function(){
    // constants
    var ACCOUNTS_LIST_URL = '/accounts/accountslist/?responseMode=json';
    var ADD_ACCOUNT_URL = '/accounts/add/?responseMode=json';
    var EDIT_ACCOUNT_URL = '/accounts/edit/?responseMode=json';
    var DELETE_ACCOUNT_URL = '/accounts/delete/?responseMode=json';

    var OPERATIONS_JOURNAL_URL = '/operation/listOperations/?responseMode=json';
    var DELETE_OPERATIONS_URL = '/operation/del_all/?responseMode=json';
    var ADD_OPERATION_URL = '/operation/add/?responseMode=json';
    var EDIT_OPERATION_URL = '/operation/edit/?responseMode=json';

    // private variables
    var _this = null;

    var _accounts;
    var _journal;

    // private functions
    function _loadAccounts(callback) {
        $.post(ACCOUNTS_LIST_URL, '', function(data) {
            if (data.result) {
                _accounts = $.extend(true, {}, data.result.data);

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
    function load(param1, param2){
        _this = this;
        
        if (typeof param1 == 'object'){
            _accounts = param1;

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
        return _accounts;
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

    function getAccountCurrency(id){
        if (_accounts && _accounts[id])
            return res.currency[_accounts[id]["currency"]];
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

    function addAccount(params, callback){
        if (typeof params != "object")
            return false;

        // тип счёта, имя, валюта -
        // обязательные параметры для всех счетов
        if (!params.type || !params.name || !params.currency)
            return false;

        $.post(ADD_ACCOUNT_URL, params, function(data){
            _loadAccounts();
//                debugger;
//                if (data.result)
//                    _accounts[data.result.id] = $.extend(true, {}, params);

//                $(document).trigger('accountAdded');

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
                _loadAccounts();

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

                $(document).trigger('accountEdited');
*/
                if (callback)
                    callback(data);
            }, 'json'
        );
    }

    function deleteAccountById(id, callback) {
        $.post(DELETE_ACCOUNT_URL, {id:id}, function(data){
            if (data.result) {
                delete _accounts[id];
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
    function loadJournal(param1, param2, param3, param4, param5, param6, param7, param8){
        //if (typeof param1 == 'string') {
        //    _journal = param1;
        //    if (typeof param2 == 'function')
        //        param2(_journal);
        //} else {
            // load from server
            $.get(OPERATIONS_JOURNAL_URL, {
                    account: param1,
                    category: param2,
                    dateFrom: param3,
                    dateTo: param4,
                    sumFrom: param5,
                    sumTo: param6,
                    type: param7
                }, function(data) {
                    _journal = data.operations;
                    if (typeof param8 == 'function')
                        param8(_journal);
            }, 'json');
        //}
    }

    /**
     * @desc delete operations by id
     * @param ids: array of operation ids
     * @param callback: callback function
     */
    function deleteOperationsByIds(ids, isVirts, callback) {
        var _ids = ids;

        $.post(DELETE_OPERATIONS_URL, {id : ids.toString(), virt: isVirts.toString()}, function(data) {
                // update accounts
                _loadAccounts();
                
                for (var i=0; i<_ids.length; i++) {
                    // delete paired transfer if exists
                    if (_journal[_ids[i]] && _journal[_ids[i]].tr_id != null && _journal[_ids[i]].tr_id != "0")
                        delete _journal[_journal[_ids[i]].tr_id];

                    // delete operation
                    delete _journal[_ids[i]];
                }

                callback(_journal);
        }, 'json');
    }

    function addOperation(
        type, account, category, date, comment,
        amount, toAccount, currency, convert,
        target, close, tags,
        callback) {
            editOperationById(
                '', type, account, category, date,
                comment, amount, toAccount, currency,
                convert, target, close, tags
            );
    }

    function editOperationById(
        id, type, account, category, date, comment,
        amount, toAccount, currency, convert,
        target, close, tags,
        callback){
            var url = (id == '') ? ADD_OPERATION_URL : EDIT_OPERATION_URL;

            var params = {
                id        : id,
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

            $.post(url, params, function(data){
                if (url == ADD_OPERATION_URL) {
                    $(document).trigger('operationAdded');
                } else if (url == EDIT_OPERATION_URL) {
                    $(document).trigger('operationEdited');
                }

                _loadAccounts();
                callback(data);
            }, "json");
    }

    // reveal some private things by assigning public pointers
    return {
        load: load,
        getAccounts: getAccounts,
        getAccountNameById: getAccountNameById,
        getAccountIdByName: getAccountIdByName,
        getAccountType: getAccountType,
        getAccountCurrency: getAccountCurrency,
        getAccountBalanceTotal: getAccountBalanceTotal,
        getAccountBalanceAvailable: getAccountBalanceAvailable,
        addAccount: addAccount,
        editAccountById: editAccountById,
        deleteAccountById: deleteAccountById,
        loadJournal: loadJournal,
        addOperation: addOperation,
        editOperationById: editOperationById,
        deleteOperationsByIds: deleteOperationsByIds
    };
}(); // execute anonymous function to immediatly return object
