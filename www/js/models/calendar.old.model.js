/**
 * @desc Calendar Model - временная
 * @author Alexandr [rewle] Ilichov
 */
easyFinance.models.calendarOld = function(){
    var _data;
    var _startDate, _endDate;//Временная система кэша

    function init(){
        _data = {};
        _startDate = 0;
        _endDate = 0;
    }

    function _selectFromData(startDate, endDate){
        var returnObject = {};
        for (var key in _data){
            if (_data[key].data <= endDate && _data[key].data >= startDate){
                returnObject[key] = _data[key];
            }
        }
        return returnObject;
    }

    function getOperationsByInterval(startDate,endDate, calback){
        var fromAjax, fromData, ajaxStart = startDate,ajaxEnd = endDate;
        var returnObject = {};
//        if ((startDate >= _endDate)||(endDate <= _startDate)||((endDate >= _endDate)&&(startDate <= _startDate))){
            //all from ajax
            fromAjax = true;
            fromData = false;
//        }else if(startDate >= _startDate && endDate <= _endDate){
//            //all from data
//            fromAjax = false;
//            fromData = true;
//        }else{
//            //ajax + data
//            fromAjax = true;
//            fromData = true;
//            if (startDate <= _endDate){
//                ajaxStart = _endDate;
//            }else{
//                ajaxEnd = _startDate;
//            }
//        }
        if (fromAjax){
            $.getJSON(
                '/calendar/events/?responseMode=json',
                {
                    start: ajaxStart,
                    end:   ajaxEnd
                },
                function(result){
                    
                    if(fromData){
                        //выборка из даты
                        returnObject = _selectFromData(startDate, endDate);
                        //Дополнение кэша
                        _data = $.extend(_data,result.calendar);
                        returnObject = $.extend(returnObject,result.calendar);
                        _startDate = (_startDate < startDate) ? _startDate : startDate;
                        _startDate = (_endDate > endDate) ? _endDate : endDate;
                    }else{
                        //замена кэша
                        _data = $.extend({},result.calendar);
                        returnObject = result.calendar;
                        _startDate = startDate;
                        _endDate = endDate;
                    }
                    calback(returnObject);
                }
            );
        }else if(fromData){
            //выборка из даты
            returnObject = _selectFromData(startDate, endDate);
            calback(returnObject);
        }
    }

    function removeChain(chainId){
        $.post(
            '/calendar/del/?responseMode=json',
            {
                chain: chainId,
                use_mode: 'all'
            },
        function(data){
            if (typeof(calback) == 'function'){
                calback(data.calendar);
            }
        },'json');
    }

    function removeOperation(operationId, calback){
        $.post(
            '/calendar/del/?responseMode=json',
            {
                id: operationId,
                use_mode: 'single'
            },
            function(data){
                if (typeof(calback) == 'function'){
                    calback(data.calendar);
                }
            },
            'json'
        );
    }

    function removeOperations(operationIdes, calback){
        $.post(
            '/calendar/reminderDel?responseMode=json',
            {
                ids : operationIdes
            },
            function(data){
                if (typeof(calback) == 'function'){
                    calback(data.calendar);
                }
            },
            'json'
        );
    }

    function editChain(chain, calback){
        $.post(
            '/calendar/edit?responseMode=json',
            chain,
            function(data){
                if (typeof(calback) == 'function'){
                    calback(data.calendar);
                }
            },
            'json'
        );
    }

    function editOperation(operation, calback){
        $.post(
            '/calendar/edit?responseMode=json',
            operation,
            function(data){
                if (typeof(calback) == 'function'){
                    calback(data.calendar);
                }
            },
            'json'
        );
    }

    function acceptOperation(operationId, calback){
        $.post(
            '/calendar/reminderAccept?responseMode=json',
            {
                ids : operationId
            },
            function(data){
                if (typeof(calback) == 'function'){
                    calback(data.calendar);
                }
            },
            'json'
        );
    }

    function acceptOperations(operationIdes, calback){
        $.post(
            '/calendar/reminderAccept?responseMode=json',
            {
                ids : operationIdes
            },
            function(data){
                if (typeof(calback) == 'function'){
                    calback(data.calendar);
                }
            },
            'json'
        );
    }

    return {
        init : init,
        getOperationsByInterval : getOperationsByInterval,
        removeChain : removeChain,
        removeOperation : removeOperation,
        removeOperations : removeOperations,
        editChain : editChain,
        editOperation : editOperation,
        acceptOperation : acceptOperation,
        acceptOperations : acceptOperations
    }
}();

easyFinance.models.calendarOld.init();