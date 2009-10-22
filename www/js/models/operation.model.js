/**
 * @desc Operation Model
 * @author Andrey [Jet] Zharikov
 */

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
     * @param ids: arary of operation ids
     */
    function deleteOperationsById(ids, callback) {
        var _ids = ids;

        $.post(DELETE_OPERATIONS_URL, {id : ids.toString()}, function(data) {
                for (var i=0; i<_ids.length; i++) {
                    delete _journal[_ids[i]];
                }

                callback(_journal);
        }, 'json');
    }

    function addOperation() {

    }

    function editOperationById(
        id, type, account, category, date, comment,
        amount, toAccount, currency, target, close, tags,
        callback){
            /*
            $.post(($('form').attr('action')), {
                id        : $('#id').val(),
                type      : $('#type').val(),
                account   : $('#op_account').val(),
                category  : $('#op_category').val(),
                date      : $('#op_date').val(),
                comment   : $('#op_comment').val(),
                amount    : tofloat($('#op_amount').val()),
                toAccount : $('#op_AccountForTransfer').val(),
                currency  : $('#op_currency').val(),
                target    : $('#op_target').val(),
                close     : $('#op_close:checked').length,
                tags      : $('#op_tags').val()
            };
            */
    }

    // reveal some private things by assigning public pointers
    return {
        load: load,
        loadJournal: loadJournal,
        deleteOperationsById: deleteOperationsById
    };
}(); // execute anonymous function to immediatly return object