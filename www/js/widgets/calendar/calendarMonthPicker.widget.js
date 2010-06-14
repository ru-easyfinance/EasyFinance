/* Виджет для быстрого выбора месяца и года.
 * Используется в календаре. При изменении
 * периода выдает событие monthPickerChanged,
 * в Event записаны startDate и endDate в формате Date
 *
 * Автор: Андрей [Jet] Жариков
 */

easyFinance.widgets.calendarMonthPicker = function(){
    var _$node = null;
    var _startDate = null;
    var _endDate = null;

    function _change() {
        var month = _startDate.getMonth();
        var year = _startDate.getFullYear();
        var monthTitle = getMonthName(month) + ' ' + _startDate.getFullYear();

        _$node.find('li.y_prev a').text(year - 1);
        _$node.find('li.y_next a').text(year + 1);

        if (month == 11) {
            _$node.find('li.m_next a').text(getMonthName(0));
        } else {
            _$node.find('li.m_next a').text(getMonthName(month + 1));
        }

        if (month == 0) {
            _$node.find('li.m_prev a').text(getMonthName(11));
        } else {
            _$node.find('li.m_prev a').text(getMonthName(month - 1));
        }

        _$node.find('li.cur').text(monthTitle);

        // fire event
        var event = $.Event("monthPickerChanged");
        event.startDate = _startDate;
        event.endDate = _endDate;
        $(document).trigger(event);
    }

    // public functions
    function init(selector){
        _$node = $(selector);

        // устанавливаем диапазон по умолчанию
        reset();
        _change();

        _$node.find('li.m_prev').click(function(){
            _startDate.setMonth(_startDate.getMonth()-1);
            _endDate.setMonth(_endDate.getMonth()-1);
            _change();
        });

        _$node.find('li.m_next').click(function(){
            _startDate.setMonth(_startDate.getMonth()+1);
            _endDate.setMonth(_endDate.getMonth()+1);
            _change();
        });

        _$node.find('li.y_prev').click(function(){
            _startDate.setYear(_startDate.getFullYear()-1);
            _endDate.setYear(_endDate.getFullYear()-1);
            _change();
        });

        _$node.find('li.y_next').click(function(){
            _startDate.setYear(_startDate.getFullYear()+1);
            _endDate.setYear(_endDate.getFullYear()+1);
            _change();
        });

        _$node.find('li.cur').click(function(){
            reset();
            _change();
        });
    }

    function reset() {
        var today = new Date();
        var thisYear = today.getFullYear();
        var thisMonth = today.getMonth();

        // по умолчанию первый день текущего месяца
        _startDate = new Date(thisYear, thisMonth, 1);
        // по умолчанию последний день текущего месяца
        _endDate = new Date(thisYear, thisMonth + 1, 0);
    }

    return {
        init: init,
        reset: reset
    };
}();
