/**
 * @desc Operation Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.accounts = function(){
    // constants
    var ACCOUNTS_LIST_URL = '/accounts/accountslist/';
    var DELETE_ACCOUNT_URL = '/accounts/del/';

    var OPERATIONS_JOURNAL_URL = '/operation/listOperations/';
    var DELETE_OPERATIONS_URL = '/operation/del_all/';
    var ADD_OPERATION_URL = '/operation/add/';
    var EDIT_OPERATION_URL = '/operation/edit/';

    // private variables
    var _this = null;

    var _accounts;
    var _journal;


    // private functions
    function _loadAccounts(callback) {
        $.post(ACCOUNTS_LIST_URL, '', function(data) {
            _accounts = data;

            $(document).trigger('accountsLoaded');

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

        if (typeof param1 == 'string'){
            _accounts = eval(param1);

            if (typeof param2 == 'function')
                param2(_this);
        } else {
            if (typeof param1 == 'function')
                _loadAccounts(function(){param1(_this)});
            else
                _loadAccounts();
        }
    }

    function getAccounts(){
        return _accounts;
    }

    function getAccountBalanceTotal(id){
        return _accounts[id]["total_balance"];
    }

    function getAccountBalanceAvailable(id){
        return _accounts[id]["total_balance"] - _accounts[id]["reserve"];
    }

    //@ TODO: addAccount(...)
    //@ TODO: editAccountById(id)
    
    function deleteAccountById(id, callback) {
        $.post(DELETE_ACCOUNT_URL, {id:id}, function(data){
                delete _accounts[id];

                $(document).trigger('accountDeleted');

                if (callback)
                    callback(data);
            }
            , 'json'
        );
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
                    _journal = data;
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
            
            $.post(url, {
                id        : id,
                type      : type,
                account   : account,
                category  : category,
                date      : date,
                comment   : comment,
                amount    : amount,
                toAccount : toAccount,
                currency  : currency,
                convert   : convert,
                target    : target,
                close     : close,
                tags      : tags
            }, function(data){
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
        getAccountBalanceTotal: getAccountBalanceTotal,
        getAccountBalanceAvailable: getAccountBalanceAvailable,
        deleteAccountById: deleteAccountById,
        loadJournal: loadJournal,
        addOperation: addOperation,
        editOperationById: editOperationById,
        deleteOperationsByIds: deleteOperationsByIds
    };
}(); // execute anonymous function to immediatly return object