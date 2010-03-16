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
 * @param name str название
 * @param value str значение
 * @param expires str срок жизни
 * @param path str
 * @param domain str
 * @param secure bool использование защищённых кук
 * @return bool
 */
function setCookie (name, value, expires, path, domain, secure) {
	if (!name || name ==''){
		return false;
	}
	document.cookie = name + "=" + escape(value) +
		((expires) ? "; expires=" + expires : "") +
                ((path) ? "; path=" + path : "") +
                ((domain) ? "; domain=" + domain : "") +
                ((secure) ? "; secure" : "");
    return true;
}

/**
 * Функция которая возвращает значение куки
 * @param name str
 * @return str - значение куки
 */

function getCookie(name) {
	var cookie = " " + document.cookie + ';';
	var search = " " + name + "=";
	var offset = cookie.indexOf(search);
	if (offset != -1) {
		offset += search.length;
		var end = cookie.indexOf(";", offset);
		return unescape(cookie.substring(offset, end));
	}
	return null;
}

///////////////////////////////////////Работа со строками////////////////////////////////////
/**
 * Функция, которая проверяет длину строки и при необходимости её укорачивает
 * @param str {String}
 * @param maxLength {Int}
 * @return String
 */
function shorter(str, maxLength){
    if (str == undefined){
        return null;
    }
    if(str.length > maxLength){
        str = str.substring(0, maxLength-3) + '...';
    }
    return str;
}

///////////////////////////////////////Работа с числами////////////////////////////////////

/**
 * Преобразует число в наш формат
 * @param num float число
 * @return string
 */
function formatCurrency(num) {
    if(isNaN(num)){return "0.00";}
    var sign = new Number(num);
    return sign.toFixed(2).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
}

/**
 * оставляет 4 значащих цифры
 * @param Cost string | Number
 * @return String 
 */
function roundToSignificantFigures(Cost){
	var workCost = '';
	if (typeof(Cost) == 'number'){
		if (Cost >= 1000){
			return Cost.toFixed(0);
		}else if(Cost >= 100){
			return Cost.toFixed(1);
		}else if(Cost >= 10){
			return Cost.toFixed(2);
		}else if (Cost >= 1){
			return Cost.toFixed(3);
		}else{
			workCost = Cost.toString();
		}
	}else if(typeof(Cost) == 'string'){
		if (Cost >= 1000){
			return Number(Cost).toFixed(0);
		}else if(Cost >= 100){
			return Number(Cost).toFixed(1);
		}else if(Cost >= 10){
			return Number(Cost).toFixed(2);
		}else if (Cost >= 1){
			return Number(Cost).toFixed(3);
		}else{
			workCost = Cost;
		}
	}
	var lenght = workCost.length - 2;
	var end = -1;
	for (var i = 2; i < lenght ;i++){
		if (end == -1 && workCost.substr(i, 1) != '0'){
			end = 1;
			workCost = Number(workCost).toFixed(i+2).toString();
			break;
		}
	}
	return workCost || null;
}
/**
 * Преобразует любую строку в число методом удаления всех левых символов))
 * @param str {String}
 * @return String
 */
function toFloat(str){
    if (str !== null && str !== undefined){
        str = new Number(str.toString().replace(/[^0-9\.\-]/gi, ''));
        return str;
    }else{
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
	var key
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
;
