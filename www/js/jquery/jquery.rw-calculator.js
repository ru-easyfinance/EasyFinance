/**
 * TODO test multy input init))
 * @author rewle
 * @version 2.0 a
 * @classDescription plugin for jQuery.Used jquery, jqueryUI ,jquery.rw-calculator.settings
 * For use : $(node).rwCalculator(?events,?options) || $.rwCalculator($(node), ?events, ?options)
 * event : @see $.rwCalculator.defaultEvents
 *
 * Description for node
 *   node is selector to DOM element(tested on <input> and <textarea>)
 *
 * Description for event:
 *    type of event == 'object'
 *    event[eventName] == [function || string]
 *    		function(e){} e is original jQuery event
 *          string - one of custom event
 *
 * Custom plagin options
 * 		$.rwCalculator.version - plugin version
 * 		$.rwCalculator.initialized - initialized plugin;
 * 		$.rwCalculator.buttonPanel - description button panel
 * 		$.rwCalculator.calculate - function(str) - calculate str
 * 		$.rwCalculator.node - last used initialized $(node)
 * 		$.rwCalculator.inst - plugin))
 *  	$.rwCalculator.functions - custom functions !this == node!
 *  		show - show button panel after $(this)
 *  		calculate - calculate $(this) value
 *  		clear - clear $(this) value
 *  		hide - hide button panel
 *
 * Button panel
 * 		-lineNumber
 * 				--ButtonNumber
 * 						---class - has 'printed' || 'special'(for use castom event)
 * 						---? rowspan - rowspan
 * 						---text - button text
 * 						---title - tooltip for button
 *
 *
 *
 */
(function($){
    function rwCalculator($node, opyions){
        try {
            var _defaultEvents = $.rwCalculator.defaultEvents;
            var _node = $node || $('body')
            var _inst = null;
            var _node = $node;
			if (typeof(options)!='object'){
				options = {};
			}

            if ($.rwCalculator._init){
                _inst = $.rwCalculator._init();//insert rw-calculator in body
                delete $.rwCalculator._init; //for IE7 mem hook
				$.rwCalculator._init = false;
            }

            else {
                _inst = $.rwCalculator.inst;
            }
            $.rwCalculator.node = $node;
            for (var key in _defaultEvents) {
                if (typeof(_defaultEvents[key]) == 'object') {
                    $($node)[key](function(e){
                        var key = e.type;
                        var _tmp;
                        for (var fk in _defaultEvents[key]) {
                            if (typeof(_defaultEvents[key][fk]) == 'function'){
                                _defaultEvents[key][fk](e);
                            }
                            else
                                if (typeof(_defaultEvents[key][fk]) == 'string'){
                                    $.rwCalculator['functions'][_defaultEvents[key][fk]].call(this, e)
                                }
                        }
                    });
                }
            }
            return {
                node: _node
            }
        }
        catch (e) {
//            alert('Error');
        }
    }

    $.rwCalculator.calculate = function(funcStr){
        var sign;
        funcStr = funcStr.replace(/[,]/gi, '.').replace(/[\.\.]/gi, '.').replace(/[\+\+]/gi, '+').replace(/[\*\*]/gi, '*').replace(/[\/\/]/gi, '/').replace(/[\-\-]/gi, '-').replace(/^\-/, '0-');
        funcStr = funcStr.toString().replace(/[^0-9\-\*\+\/\.]/gi, '');
        try {
            if ((funcStr.indexOf('*') == -1) &&
            (funcStr.indexOf('+') == -1) &&
            (funcStr.indexOf('/') == -1) &&
            (funcStr.indexOf('-') == -1)) {
                sign = new Number(funcStr);
            }
            else {
                sign = new Number(eval(funcStr));
            }
            return sign.toFixed(2).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ').replace('.00','');
        }
        catch (e) {
            return funcStr;
        }
    };


    $.rwCalculator.functions = {
        'show': function(e, option){//TODO full search

        	$.rwCalculator.inst.show();
        	$(this).parent().append($.rwCalculator.inst);

			var top =0;
			//if ($('body').height() < ($.rwCalculator.inst.height() + $.rwCalculator.inst.offset().top)) {
			//	top = '-' + ($.rwCalculator.inst.height() + 15) + 'px';
			//}
			//else {
				top = '22px';
			//}
			$.rwCalculator.inst.css('top',top);
			$('.calculatorWrapper').show();
			$.rwCalculator.node = $(this);
            if ($.rwCalculator.node.val() == '0') {
                $.rwCalculator.node.val('');
            }

        },
        'hide': function(){
			$('.calculatorWrapper').hide();
				$.rwCalculator.inst.hide();
				//$.click();
        },
        'calculate': function(){
            $($.rwCalculator.node).val($.rwCalculator.calculate($($.rwCalculator.node).val()));
        },
        'clear': function(){
            $($.rwCalculator.node).val('');
        }
    }

	$.rwCalculator._generateHtmlButtonPanel = function(){
		try {
			var _html = '<table>';

			for(var line in $.rwCalculator.buttonPanel){
				_html += '<tr>';
					for(var btn in $.rwCalculator.buttonPanel[line]){
						_html += '<td rowspan="' + ($.rwCalculator.buttonPanel[line][btn]['rowspan'] || '1') + '" class="' + $.rwCalculator.buttonPanel[line][btn]['class'] +
						'" ' + ($.rwCalculator.buttonPanel[line][btn]['event']?('event="' + $.rwCalculator.buttonPanel[line][btn]['event'] + '"'):'') +
						'><div title="' + ($.rwCalculator.buttonPanel[line][btn]['title'] ||'') +
						'" >' + $.rwCalculator.buttonPanel[line][btn]['text'] +
						'</div></td>';
					}
				_html += '</tr>'
			}

			return (_html + '</table>');
		}catch (e){
			return '';
		}
	}

    $.rwCalculator._init = function(){
        var _tmpDt = new Date();
        var id = 'rwCalculator' + (_tmpDt.getTime()).toString();


        var _html = '<div id="' + id + '" class="rw-widget rw-container rw-calculator rw-popup-widget">' +
        '<div class="panel">' +
        $.rwCalculator._generateHtmlButtonPanel() +
        '</div>' +
        '<div class="calculatorWrapper">   </div></div>';
        $('body').append(_html);
		$('.calculatorWrapper').click(function(){
			$('.calculatorWrapper').hide();
			$.rwCalculator.inst.hide();
			})
        $.rwCalculator.inst = $('#' + id);
        //binds
        var clearVal = false;
        function _print(simbol){
            //            var val = _calculator.find('input').val();
            if ('123456789000.,+-*/'.indexOf(simbol) != -1) {
                return true;
            }
            else
                if ('='.indexOf(simbol) != -1) {
                    var val = _calculator.find('input').val();
                    _calculator.find('input').val(_calculate(val));
                }
                else {
                    return false;
                }
        }

		$.rwCalculator.inst.find('td.printed div').click(function(){
            var val = $.rwCalculator.node.val();
            var txt = $(this).text();
            if ('123456789'.indexOf(txt) != -1 && (val == '0' || clearVal)) {
                val = '';
            }
            clearVal = false;
            if (_print(txt)) {
                $.rwCalculator.node.val((val + txt));
                $.rwCalculator.node.focus();
                if (document.selection) { // ie

                    var range = document.selection.createRange();
                    range.moveStart('character', -$.rwCalculator.node.val().length);
                    range.moveStart('character', $.rwCalculator.node.val().length);
                    range.moveEnd('character', 0);
                    range.select();
                }
            }
        });

		$.rwCalculator.inst.find('td.special').click(function(e){
            var event = $(this).attr('event');
            $.rwCalculator.functions[event]();
        });

    }

    $.fn.rwCalculator = function( options){
        var _node = this;
        if (typeof(options)!='object'){
			options = {};
		}
		rwCalculator(_node,  options);

    };

})(jQuery);
