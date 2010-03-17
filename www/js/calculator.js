/*
 * В данной версии реализовано как плагин jQuery
 */
function calculate(funcStr){
	if (!funcStr) {
		return '0.00';
	}
	funcStr = funcStr.toString().replace(/[^0-9\.\-\+\/\*]/gi, '') || '0';
	funcStr = funcStr.replace(/[\,]/gi, '.').replace(/[\.\.]/gi, '.').replace(/[\+\+]/gi, '+').replace(/[\*\*]/gi, '*').replace(/[\/\/]/gi, '/').replace(/[\-\-]/gi, '-').replace(/^\-/, '0-');
	if (funcStr.match(/([0-9]+(\.{1}[0-9]+)?[\+\-\*\/]{1}){0,}[0-9]+(\.{1}[0-9]+)?/)[0] != funcStr) {
		return funcStr;
	}
	var sign = 0;
	if (funcStr.match(/[0-9]+(\.{1}[0-9]+)?/)[0] == funcStr) {
		sign = new Number(funcStr);
	} else {
		sign = new Number(eval(funcStr));
	}
	return sign.toFixed(2).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
}

var RW_calculator = function(){
	var _calculator;
	var clearVal = false;
	/**
	 * Функция расчёта
	 */
	function _calculate(funcStr){
		var sign;
		
		funcStr = funcStr.replace(/[,]/gi, '.').replace(/[\.\.]/gi, '.').replace(/[\+\+]/gi, '+').replace(/[\*\*]/gi, '*').replace(/[\/\/]/gi, '/').replace(/[\-\-]/gi, '-').replace(/^\-/, '0-');
		funcStr = funcStr.toString().replace(/[^0-9\-\*\+\/\.]/gi, '');
		try {
			if ((funcStr.indexOf('*') == -1) &&
			(funcStr.indexOf('+') == -1) &&
			(funcStr.indexOf('/') == -1) &&
			(funcStr.indexOf('-') == -1)) {
				sign = new Number(funcStr);
			} else {
				sign = new Number(eval(funcStr));
			}
			clearVal = true;
			return sign.toFixed(2).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
		} catch (e) {
			return funcStr;
			//            return false;
		}
	}
	/**
	 * Печать символа
	 */
	function _print(simbol){
		//            var val = _calculator.find('input').val();
		if ('123456789000.,+-*/'.indexOf(simbol) != -1) {
			return true;
		} else 
			if ('='.indexOf(simbol) != -1) {
				var val = _calculator.find('input').val();
				_calculator.find('input').val(_calculate(val));
			} else {
				return false;
			}
	}
	function init(calculator){
		_calculator = calculator;
		calculator.find('td.printed div').click(function(){
			var val = calculator.find('input').val();
			var txt = $(this).text();
			if ('123456789'.indexOf(txt) != -1 && (val == '0' || clearVal)) {
				val = '';
			}
			clearVal = false;
			if (_print(txt)) {
				_calculator.find('input').val((val + txt));
				
			    if (document.selection) { // ie
			        $(_calculator.find('input')).focus ();
			        var range = document.selection.createRange ();
			        range.moveStart ('character', - $(_calculator.find('input')).val().length);
			        range.moveStart ('character', $(_calculator.find('input')).val().length);
			        range.moveEnd ('character', 0);
			        range.select ();
			    }
			}
		});
		calculator.find('td.special').click(function(){
			var event = $(this).attr('event');
			var val = 0;
			switch (event) {
				case 'clear':
					calculator.find('input').val('0');
					break;
				case 'back':
					val = calculator.find('input').val();
					calculator.find('input').val(val.substr(0, val.length - 1));
					break;
				case 'calc':
					val = calculator.find('input').val();
					calculator.find('input').val(_calculate(val));
					break;
			}
		});
		calculator.find('input').focus(function(){
			if (calculator.find('input').val() == '0') {
				calculator.find('input').val('');
			}
		});
		calculator.find('input').keypress(function(e){
			if (!e.altKey && !e.shiftKey && !e.ctrlKey) {
				var chars = String.fromCharCode(e.which);
				if (chars && chars != '' && _print(chars)) {
					return true;
				} else {
					if (e.keyCode == 13) {
						var val = calculator.find('input').val();
						calculator.find('input').val(_calculate(val));
					} else {
						var keyCode = e.keyCode;
						if (keyCode != 13 && keyCode != 46 && keyCode != 8 && keyCode != 37 && keyCode != 39 && e.which != 32) 
							return false;
					}
					return true;
				}
				return false;
			}
		});
		calculator.click(function(){
			calculator.find('input').focus();
		});
		calculator.keypress(function(e){
			if (e.which == 13) {
				var val = calculator.find('input').val();
				calculator.find('input').val(_calculate(val));
			}
		});
	}
	return {
		init: init
	}
}();
$(document).ready(function(){
	RW_calculator.init($('.calculatorRW'));
});
