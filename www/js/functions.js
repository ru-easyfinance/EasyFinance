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
    if(str.length > maxLength){
        str = str.substring(0, maxLength-4) + '...';
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
 * Преобразует любую строку в число методом удаления всех левых символов))
 * @param str {String}
 * @return String
 */
function toFloat(str){
if (!str) {
        str = new Number(str.toString().replace(/[^0-9\.\-]/gi, ''));
        return str;
    } else {
        return 0;
    }
}
/**
 * @deprecated надо перехлдить на zend стандарты написания имён
 * @see func toFloat
 */
function tofloat(str){
    if (!str) {
        str = new Number(str.toString().replace(/[^0-9\.\-]/gi, ''));
        return str;
    } else {
        return '';
    }
}

;