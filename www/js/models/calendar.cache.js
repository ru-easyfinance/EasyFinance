/**
 * @desc Calendar Model - временная
 * @author Alexandr [rewle] Ilichov
 */
easyFinance.models.calendarCache = function(){
    var _data = {};
    var flag = 0;
    var _callback;

    function clean(){
        // -- for IE7 --
        delete _data;
        // /-- for IE7 --
        _data = {};
    }

    function load(startMonth, startYear, monthCount, callback){
        var realYear = startYear;
        var realMonth;
        var returnData = {};
        var ajaxLoading = false;
        var ajaxLoadDates = [];
        for (var i = 0; i < monthCount; i++) {
            realMonth = startMonth - (-i);
            if (realMonth > 11) {
                realMonth = realMonth - 12
                realYear = startYear + 1;
            }

            // #1592
            // Андрей Jet Жариков
            // здесь раньше была проверка на то, содержатся ли данные в кэше
            // если данные за месяц были в кэше, они отдаются сразу,
            // иначе - идет запрос на сервер и наполнение кэша.
            //
            // По непонятным мне причинам, в ряде случаев данные
            // за тот или иной месяц подргужались в кэш частично,
            // и поэтому не все операции были доступны для просмотра в календаре
            //
            // Например: если у меня есть операция на 1 Сентября, то при просмотре
            // Августа я её уже вижу (т.к. показывается несколько чисел из след. месяца),
            // а при прокрутке на Сентябрь данные берутся из кеша,
            // и других записей по Сентябрю не видно, хотя они там должны быть!
            //
            // Короче говоря, кэш в текущем виде - глючит!
            // Поэтому данной правкой я фактически заставляю календарь
            // каждый раз при смене месяца запрашивать данные с сервера.
            //
            // Обратите внимание, что ещё в calendar.widget.js используется функция
            // loadSetupData, которая тоже пользуется данными из кэша. Поэтому наполнение
            // кэша я пока не стал удалять. Если (когда) будете это дело рефакторить - вычищайте аккуратно ;)

            //if (typeof(_data[realYear + '-' + realMonth]) == 'object') {
            //    returnData = $.extend(returnData, _data[realYear + '-' + realMonth]);
            //} else {
                ajaxLoading = true;
                ajaxLoadDates.push({
                    month: realMonth,
                    year: realYear
                });
            //}
        }

        if (ajaxLoading) {
            var startDate = new Date();
            var endDate = new Date();
            var start, end;
            flag = ajaxLoadDates.length;
            //            _callback = callback;
            for (var k = 0; k < flag; k++) {
                startDate.setFullYear(ajaxLoadDates[k].year, ajaxLoadDates[k].month, 1);
                endDate.setFullYear(ajaxLoadDates[k].year, ajaxLoadDates[k].month + 1, 1);
                start = Math.floor(startDate.getTime() / 1000);
                _data[startDate.getFullYear() + '-' + startDate.getMonth()] = {}
                end = Math.floor(endDate.getTime() / 1000);
                $.getJSON('/calendar/events/?responseMode=json', {
                    start: start,
                    end: end
                }, function(result){
                    var tmpDate = new Date();
                    for (var key in result.calendar) {
                        tmpDate.setTime(result.calendar[key].timestamp * 1000);
                        if (typeof(_data[tmpDate.getFullYear() + '-' + tmpDate.getMonth()]) != 'object') {
                            _data[tmpDate.getFullYear() + '-' + tmpDate.getMonth()] = {}
                        }
                        _data[tmpDate.getFullYear() + '-' + tmpDate.getMonth()][key] = $.extend({}, result.calendar[key]);
                    }
                    returnData = $.extend(returnData, result.calendar);
                    flag--;
                    if (flag == 0) {
                        callback(returnData);
                    }
                });
            }
        }
        else {
            if (typeof(callback) == 'function') {
                callback(returnData);
            }
        }
    }


    var _rightDate,_contentDate;
    function reloadWidgets(widget){
        if (typeof(easyFinance.widgets.calendar) != 'object'){
            widget = 'calendarPreview';
        }

        if (widget == 'calendar') {
            _rightDate = easyFinance.widgets.calendar.getCurrentDate();
            load(_rightDate.getMonth(), _rightDate.getFullYear(), 1, function(data){
                $('#calendar').fullCalendar('refetchEvents');
                easyFinance.widgets.calendarList.load(data);
            });
        } else if (widget == 'calendarPreview') {
            _rightDate = easyFinance.widgets.calendarPreview.getCurrentDate();
            load(_rightDate.getMonth(), _rightDate.getFullYear(), 1, function(data){
                easyFinance.widgets.calendarPreview.load(data);
            });
        } else {
            _rightDate = easyFinance.widgets.calendarPreview.getCurrentDate();
            _contentDate = easyFinance.widgets.calendar.getCurrentDate();
            if (_rightDate.getFullYear() == _contentDate.getFullYear() && _rightDate.getMonth() == _contentDate.getMonth()) {
                load(_rightDate.getMonth(), _rightDate.getFullYear(), 1, function(data){
                    $('#calendar').fullCalendar('refetchEvents');
                    easyFinance.widgets.calendarList.load(data);
                    easyFinance.widgets.calendarPreview.load(data);
                });
            } else {
                load(_rightDate.getMonth(), _rightDate.getFullYear(), 1, function(data){
                    easyFinance.widgets.calendarPreview.load(data);
                    load(_contentDate.getMonth(), _contentDate.getFullYear(), 1, function(data){
                        // #1433. обновляем граф. календарь
                        $('#calendar').fullCalendar('refetchEvents');
                        easyFinance.widgets.calendarList.load(data);
                    });
                });
            }
        }
    }

    function init(result){
        var tmpDate = new Date();
        for (var key in result) {
            tmpDate.setTime(result[key].timestamp * 1000);
            if (typeof(_data[tmpDate.getFullYear() + '-' + tmpDate.getMonth()]) != 'object') {
                _data[tmpDate.getFullYear() + '-' + tmpDate.getMonth()] = {}
            }
            _data[tmpDate.getFullYear() + '-' + tmpDate.getMonth()][key] = $.extend({}, result[key]);
        }
    }
    //special for fullcalendar
    function loadSetupData(month, year){
        var tmpDate = new Date();
        if (!month || month == null) {
            month = tmpDate.getMonth();
            year = tmpDate.getFullYear();
        }
        var generalKey = year + '-' + month;
        tmpDate.setMonth(month - 1);

        var leftKey = tmpDate.getFullYear() + '-' + tmpDate.getMonth();
        tmpDate.setFullYear(year, month + 1);
        var rightKey = tmpDate.getFullYear() + '-' + tmpDate.getMonth();
        var retObj = $.extend({}, (_data[leftKey] || {}), (_data[generalKey] || {}), (_data[rightKey] || {}));
        return retObj;
    }

    function removeChain(chainId){
        for (var mainKey in _data) {
            for (var key in _data[mainKey]) {
                if (_data[mainKey][key].chain == chainId && _data[mainKey][key].accepted == 0) {
                    delete _data[mainKey][key];
                }
            }
        }
    }

    function removeElements(elementsArray){
        if (typeof(elementsArray) == "number") {
            elementsArray = [elementsArray];
        }
        for (var mainKey in elementsArray) {
            for (var generalKey in _data) {
                for (var key in _data[generalKey]) {
                    if (_data[generalKey][key].id == elementsArray[mainKey]) {
                        delete _data[generalKey][key];
                    }
                }
            }
        }
    }

    function acceptElements(elementsArray){
        if (typeof(elementsArray) == "number") {
            elementsArray = [elementsArray];
        }
        for (var mainKey in elementsArray) {
            for (var generalKey in _data) {
                for (var key in _data[generalKey]) {
                    if (_data[generalKey][key].id == elementsArray[mainKey]) {
                        _data[generalKey][key].accepted = 1;
                    }
                }
            }
        }
    }

    return {
        init: init,
        load: load,
        clean: clean,
        reloadWidgets: reloadWidgets,
        loadSetupData: loadSetupData,
        removeChain: removeChain,
        removeElements: removeElements,
        acceptElements: acceptElements
    }

}();
