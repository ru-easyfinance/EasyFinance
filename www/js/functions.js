;
/*
 * Здесь хранить основные используемые функции
 *
 * При написании любых общих функций - перемещать сюда
 * желательно писать пораздельно.
 *
 * По возможности переходить на функции описанные здесь.
 */

///////////////////////////////////////Работа с Cookie////////////////////////////////////


/**
 * Функция, которая устанавливает куку
 * @param {String} name
 * @param {String} value
 * @param {String} expires
 * @param {String} path
 * @param {String} domain
 * @param {String} secure
 * @return {Boolean}
 */
function setCookie (name, value, expires, path, domain, secure) {
    try {
        if (!name || name == '') {
            return false;
        }
        document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
        return true;
    }catch (e){
        return false;
    }
}

/**
 * Функция возвращает значение куки по её имени
 * @param {String} name
 * @return {String || Null}
 */
function getCookie(name) {
    try {
        var cookie = " " + document.cookie + ';';
        var search = " " + name + "=";
        var offset = cookie.indexOf(search);
        if (offset != -1) {
            offset += search.length;
            var end = cookie.indexOf(";", offset);
            return unescape(cookie.substring(offset, end));
        }
        return null;
    }catch (e){
        return null;
    }
}

///////////////////////////////////////Работа со строками////////////////////////////////////
/**
 * Функция, которая проверяет длину строки и при необходимости её укорачивает
 * @param {String} str
 * @param {Number} maxLength
 * @return {String}
 */
function shorter(str, maxLength){
    try {
        if (str == undefined) {
            return null;
        }
        if (str.length > maxLength) {
            str = str.substring(0, maxLength - 3) + '...';
        }
        return str;
    }catch (e){
        return null;
    }
}
/**
 * Устанавливает позицую каретки в инпуте
 * TODO test
 * @param {jQuery} elem
 * @param {Number} caretPos
 * @return {true || false}
 */
function setCursorPositionFromInput (elem, caretPos) {
    try {
        if (document.selection) { // ie
            $(elem).focus();
            var range = document.selection.createRange();
            range.moveStart('character', -$(elem).val().length);
            range.moveStart('character', caretPos);
            range.moveEnd('character', 0);
            range.select();
        }
        else
            if (elem.selectionStart || elem.selectionStart == '0') { // Mozilla
                elem.selectionStart = caretPos;
                elem.selectionEnd = caretPos;
                elem.focus();
            }
            return true;
    } catch(e){
        return false;
    }
}
/**
 * Возвращает позицию курсора в инпуте
 * @param {jQuery} elem
 * @return {Number || null}
 */
function getCursorPositionFromInput (elem) {
    try {
        var caretPos = 0;
        if (document.selection) { // ie
            $(elem).focus();
            var range = document.selection.createRange();
            elem.moveStart('character', -$(elem).val().length);
            caretPos = range.text.length;
        }
        else
            if (elem.selectionStart || elem.selectionStart == '0') { // Mozilla
                caretPos = elem.selectionStart;
            }
        return caretPos;
    }catch(e){
        return null;
    }
}

///////////////////////////////////////Работа с числами////////////////////////////////////

/**
 * Преобразует число в наш формат
 * по умолчанию выводит число в виде n nnn nnn.nn
 * @param {Number || String} num
 * [@param {Boolean} hideCents] - скрывать ненулевые копейки или нет
 * [@param {Boolean} centsSpacers] - выводить вместо скрытых копеек пробелы или нет
 * @return {String}
 */
function formatCurrency(num, hideCents, centsSpacers) {
    if (typeof hideCents === undefined) {
        hideCents = false;
    }

    if (typeof spaceCents === undefined) {
        centsSpacers = false;
    }

    if (isNaN(num)) {
        num = 0;
    }

    var amount = new Number(num);

    // разбиваем число на компоненты
    var abs = Math.abs(amount);
    var whole = Math.floor(abs);
    var cents = Math.floor((abs - whole).toFixed(2) * 100);
    if (cents == 100) {
        // когда округляем числа вида 13.999
        cents = 0;
        whole = whole + 1;
    }
    var zeroCents = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    var strSign = amount < 0 ? '-' : '';

    var strCents = "";
    if (!hideCents) {
        if (cents == 0) {
            if (centsSpacers) {
                strCents = zeroCents;
            }
        } else {
            strCents = ((cents < 10) ? ".0" : ".") + cents.toString();
        }
    }

    return strSign + whole.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + strCents;
}

/* Выводим сумму в ноду с заданным селектором,
 * выставляет соответствующий класс суммы
 * и добавляет в конце текст валюты по молчанию
 *
 * Например: setSumInDefaultCurrency('#test', 10)
 * выведет в ноду #test '10 руб.' и присвоит ей класс sumGreen
 **/
function setSumInDefaultCurrency(selector, sum) {
    var $node = $(selector);
    $node.text (formatCurrency(sum) + " " + easyFinance.models.currency.getDefaultCurrencyText());

    if (sum >= 0) {
        $node.removeClass('sumRed').addClass('sumGreen');
    } else {
        $node.removeClass('sumGreen').addClass('sumRed');
    }
}



// округляем число до двух знаков после запятой
function roundToCents(number) {
    return Math.round(number*Math.pow(10,2))/Math.pow(10,2);
}

// округляем число до sig значимых знаков
function roundToSignificantFigures(n, sig) {
    var mult = Math.pow(10, sig - Math.floor(Math.log(n) / Math.LN10) - 1);

    return Math.round(n * mult) / mult;
}


/**
 * оставляет 4 значащих цифры
 * @param {String || Number} Cost
 * @return {String}
 */
/*
function roundToSignificantFigures(Cost){
    try {
        var workCost = '';
        if (typeof(Cost) == 'number') {
            if (Cost >= 1000) {
                return Cost.toFixed(0);
            }
            else
                if (Cost >= 100) {
                    return Cost.toFixed(1);
                }
                else
                    if (Cost >= 10) {
                        return Cost.toFixed(2);
                    }
                    else
                        if (Cost >= 1) {
                            return Cost.toFixed(3);
                        }
                        else {
                            workCost = Cost.toString();
                        }
        }
        else
            if (typeof(Cost) == 'string') {
                if (Cost >= 1000) {
                    return Number(Cost).toFixed(0);
                }
                else
                    if (Cost >= 100) {
                        return Number(Cost).toFixed(1);
                    }
                    else
                        if (Cost >= 10) {
                            return Number(Cost).toFixed(2);
                        }
                        else
                            if (Cost >= 1) {
                                return Number(Cost).toFixed(3);
                            }
                            else {
                                workCost = Cost;
                            }
            }
        var lenght = workCost.length - 2;
        var end = -1;
        for (var i = 2; i < lenght; i++) {
            if (end == -1 && workCost.substr(i, 1) != '0') {
                end = 1;
                workCost = Number(workCost).toFixed(i + 2).toString();
                break;
            }
        }
        return workCost || null;
    }catch (e){
        return null;
    }
}
*/

/**
 * Преобразует любую строку в число методом удаления всех левых символов))
 * @param {String} str
 * @return String
 */
function toFloat(str){
    try {
        if (str !== null && str !== undefined) {
            str = new Number(str.toString().replace(/[^0-9\.\-]/gi, ''));
            return str;
        }
        else {
            return 0;
        }
    }catch (e){
        return 0;
    }
}
/**
 * @deprecated надо перехлдить на zend стандарты написания имён
 * @see func toFloat
 */
function tofloat(str){
    if (str !== null) {
        str = new Number(str.toString().replace(/[^0-9\.\-]/gi, ''));
        return str;
    } else {
        return '';
    }
}

/**
 * @deprecated
 * @param {Object} obj
 * @param {String} columnName
 */
var searchMinimum = function(obj, columnName){
    var minKey = null;
    var minVal = null;
    for (var key in obj){
        if (minKey == null || obj[key][columnName] <= minVal ){
            minKey = key;
            minVal = obj[key][columnName];
        }
    }
    return minKey;
}

/**
 * @deprecated
 * @param {Object} obj
 * @param {String} columnName
 */
function getElementsFromObjectWithOrderByColumnByASC(obj, columnName){
    var returnArray = []
    var workingObj = $.extend({}, obj)
    var key
    while(workingObj.length > 0 ){
        key = searchMinimum(obj, columnName);
        if (key == null){
            break;
        }
        returnArray.push($.extend({},workingObj[key]));
        delete workingObj[key];
    }
    return returnArray;

}

/**
 * @deprecated
 * @param {Object} obj
 * @param {String} columnName
 * @param {Function} callback
 */
function getElementsFromObjectWithOrderByColumnWithTemplate(obj, columnName, callback){
    var returnArray = [];
    if (typeof(callback) == 'string'){
        switch(callback){
            case 'searchMinimum':
            default :
            callback = searchMinimum;
                break;
        }
    }
    var workingObj = $.extend({}, obj)
    var key;
    while(workingObj.length > 0 ){

            key = callback(obj, columnName);

        if (key == null){
            break;
        }
        returnArray.push($.extend({},workingObj[key]));
        delete workingObj[key];
    }
    return returnArray;
}

// @TODO
// избавиться от currentCalendarEvent!
// сейчас, к сожалению, если работать без этого,
// то при редактировании неск. операций подставляются старые данные
// есть какой-то глюк или нюанс при работе с замыканиями..
var currentCalendarEvent = null;

function calendarEditSingleOrChain(event) {
    currentCalendarEvent = event;

    if (currentCalendarEvent.every == "0") {
        // единичная операция, редактируем
        easyFinance.widgets.operationEdit.fillFormCalendar(currentCalendarEvent, true, false);
    } else {
        promptSingleOrChain("edit", function(isChain){
            easyFinance.widgets.operationEdit.fillFormCalendar(currentCalendarEvent, true, isChain);
        });
    }
}

function calendarDeleteSingleOrChain(event) {
    currentCalendarEvent = event;

    var deleteCallback = function(data){
        // функция отображает результат удаления
        if (data.result) {
            if (data.result.text)
                $.jGrowl(data.result.text, {theme: 'green'});
        } else if (data.error) {
            if (data.error.text)
                $.jGrowl(data.error.text, {theme: 'red', stick: true});
        }
    };

    if (event.every == "0") {
        // если операция одиночная, удаляем после подтверждения
        if (confirm('Удалить операцию?')) {
            easyFinance.models.accounts.deleteOperationsByIds(currentCalendarEvent.id, [], deleteCallback);
        }
    } else {
        // спрашиваем, удалить данную операцию или всю серию
        promptSingleOrChain("delete", function(isChain){
            if (isChain) {
                easyFinance.models.accounts.deleteOperationsChain(currentCalendarEvent.chain, deleteCallback);
            } else {
                easyFinance.models.accounts.deleteOperationsByIds(currentCalendarEvent.id, [], deleteCallback);
            }
        });
    }
}

/**
 * TODO rewrite
 * Рисует диалог выбора для цепочки
 * @param {String} mode
 * @param {Function} callback
 */
function promptSingleOrChain(mode, callback){
    if (mode == "edit") {
        $("#dialogSingleOrChainEdit").html('<div style="margin: 0 14px">Это операция является частью серии операций.<br> Вы хотите изменить только выбранную операцию или все неподтверждённые операции в этой серии? </div>').dialog({
            autoOpen: false,
            width: 540,
            dialogClass: 'calendar',
            title: 'Редактирование календаря',
            buttons: {
                "Изменить все неподтверждённые": function(){
                    $(this).dialog('close');
                    callback(true);
                },
                "Изменить выбранную": function(){
                    $(this).dialog('close');
                    callback(false);
                }
            }
        }).dialog('open');
    } else {
        if (mode == "delete") {
            $("#dialogSingleOrChainDelete").html('<div style="margin: 0 14px">Это операция является частью серии операций.<br> Вы хотите удалить только выбранную операцию или все неподтверждённые операции в этой серии? </div>').dialog({
                autoOpen: false,
                width: 540,
                dialogClass: 'calendar',
                title: 'Удаление из календаря',
                buttons: {
                    "Удалить все неподтверждённые": function(){
                        $(this).dialog('close');
                        callback(true);
                    },
                    "Удалить выбранную": function(){
                        $(this).dialog('close');
                        callback(false);
                    }
                }
            }).dialog('open');
        }
    }
}
/**
 * TODO mode
 * make system dialog
 * @param {Object} options
 * @param {Function} callback
 * @return {boolean}
 */
var efConfirm = function(options, callback){
    try {
        var NODE_FOR_DIALOG = $('#efConfirmDialog');
        var DEFAULT_BUTTONS = {
            'Да': true,
            'НЕТ': false
        }
        if (typeof(options) != 'object') {
            options = {};
        }
        var _title = options.title || '';
        var _content = options.content || '';
        var _dialogClass = options.dialogClass || '';
        var _buttons = options.buttons || DEFAULT_BUTTONS
        var _dialogButtons = {};
        for (var key in _buttons) {
            _dialogButtons[key] = function(){
                $(NODE_FOR_DIALOG).dialog('close');
                if (typeof(callback) == 'function') {
                    callback(_buttons[key]);
                }
            }
        }
        $(NODE_FOR_DIALOG).html(_content).dialog({
            autoOpen: true,
            width: 540,
            modal: true,
            dialogClass: _dialogClass,
            title: _title,
            buttons: _dialogButtons,
            close: function(){
                $(NODE_FOR_DIALOG).dialog('destroy');//special for memory save
            }
        });
        return true;
    }catch(e){
        return false;
    }
};

var monthNames = new Array(12);
monthNames[0]="Январь";
monthNames[1]="Февраль";
monthNames[2]="Март";
monthNames[3]="Апрель";
monthNames[4]="Май";
monthNames[5]="Июнь";
monthNames[6]="Июль";
monthNames[7]="Август";
monthNames[8]="Сентябрь";
monthNames[9]="Октябрь";
monthNames[10]="Ноябрь";
monthNames[11]="Декабрь";

function getMonthName(month) {
    return monthNames[month];
}

function trim(str) {
	return str.replace(/(^\s+)|(\s+$)/g, "");
}

function ltrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

function rtrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}