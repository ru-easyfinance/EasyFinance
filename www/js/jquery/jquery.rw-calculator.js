/**
 * @author rewle
 */
(function($){
    function rwCalculator($node, events){
        try {
            var _defaultEvents = typeof(events) == 'object' ? events : $.rwCalculator.defaultEvents
            var _node = $node || $('body')
            var _initialised = $.rwCalculator.initialized || false;
            var _inst = null;
            var _node = $node;
            if (!_initialised) {
                _inst = $.rwCalculator._init();//insert rw-calculator in body
                delete $.rwCalculator._init;
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
                            if (typeof(_defaultEvents[key][fk]) == 'function') {
                                _defaultEvents[key][fk](e);
                            }
                            else 
                                if (typeof(_defaultEvents[key][fk]) == 'string') {
                                    $.rwCalculator['functions'][_defaultEvents[key][fk]].call(this, _node)
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
            return sign.toFixed(2).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
        } 
        catch (e) {
            return funcStr;
        }
    };
    
    
    $.rwCalculator.functions = {
        'show': function(){//TODO full search
            var _bodyRect = $('body')[0].getBoundingClientRect()
            var _elementRect = $(this)[0].getBoundingClientRect()
            var _left = _elementRect.left
            var _top = _elementRect.top + _elementRect.height;
            $(this).parent().append($.rwCalculator.inst);
            $.rwCalculator.inst.slideDown().css({
                left: _left,
                top: _top
            });
            if ($.rwCalculator.node.val() == '0') {
                $.rwCalculator.node.val('');
            }
            
        },
        'hide': function(){
            $.rwCalculator.inst.slideUp();
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
        '</div>';
        
        
        $('body').append(_html);
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
                
                if (document.selection) { // ie
                    $.rwCalculator.node.focus();
                    var range = document.selection.createRange();
                    range.moveStart('character', -$.rwCalculator.node.val().length);
                    range.moveStart('character', $.rwCalculator.node.val().length);
                    range.moveEnd('character', 0);
                    range.select();
                }
            }
        });
        $.rwCalculator.inst.find('td.special').click(function(){
            var event = $(this).attr('event');
            $.rwCalculator.functions[event]();
        });
    }
    
    $.fn.rwCalculator = function(options){
        var _node = this;
        rwCalculator(_node);
    };
    
})(jQuery);
