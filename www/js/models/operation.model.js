easyFinance.models.operation = function(){
    // constants
    var OPERATIONS_JOURNAL_URL = '/operation/listOperations/';
    var DELETE_OPERATIONS_URL = '/operation/del_all/';
    var ADD_OPERATION_URL = '/operation/add/';
    var EDIT_OPERATION_URL = '/operation/edit/';

    // private variables
    var _journal;

    // private functions

    // public variables

    // public functions

    /**
     * @desc initialize
     * @usage load(callback)
     */
    function load(param1){
        if (typeof param1 == 'function')
            param1(this);
    }

    /**
     * @desc read initial data from json/server
     * @usage ---loadJournal(json)
     * @usage ---loadJournal(json, callback)
     * @usage loadJournal(account, category, dateFrom, dateTo, callback)
     */
    function loadJournal(param1, param2, param3, param4, param5){
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
                    dateTo: param4
                }, function(data) {
                    _journal = data;
                    if (typeof param5 == 'function')
                        param5(_journal);
            }, 'json');
        //}
    }

    /**
     * @desc delete operations by id
     * @param ids: array of operation ids
     * @param callback: callback function
     */
    function deleteOperationsByIds(ids, callback) {
        var _ids = ids;

        $.post(DELETE_OPERATIONS_URL, {id : ids.toString()}, function(data) {
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
            }, callback, "json");
    }

    // reveal some private things by assigning public pointers
    return {
        load: load,
        loadJournal: loadJournal,
        addOperation: addOperation,
        editOperationById: editOperationById,
        deleteOperationsByIds: deleteOperationsByIds
    };
}(); // execute anonymous function to immediatly return object