easyFinance.widgets.calendarPreview = function(){
    var ddt = new Date();
    var now = new Date();
    var _currentDate = new Date();
    _currentDate.setDate(1)
    var ddt_day, ddt_month;
    var ddt2month = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    function init(){
        $('.calendar_block .calendar').datepicker({
            onChangeMonthYear: function(year, month, inst){
                _currentDate.setFullYear(year, month - 1);
                setTimeout(function(){easyFinance.models.calendarCache.reloadWidgets('calendarPreview');},1000)

            }
        });
        if($('#calendar').length > 0){
            $('.calendar_block .calendar .ui-datepicker-next').click();
        }
        $(document).bind('operationEdited operationsChainAdded operationsChainEdited operationDateEdited', function(data){
            easyFinance.models.calendarCache.clean();
            easyFinance.models.calendarCache.init(data.calendar || {});
            easyFinance.models.calendarCache.reloadWidgets();
        });
        $(document).bind('operationsAccepted', function(data){
            easyFinance.models.calendarCache.acceptElements(data.ids || []);
            easyFinance.models.calendarCache.reloadWidgets();
        });
        $(document).bind('operationsDeleted', function(data){
            easyFinance.models.calendarCache.removeElements(data.ids || []);
            easyFinance.models.calendarCache.reloadWidgets();
        });
        $(document).bind('operationsChainDeleted', function(data){
            easyFinance.models.calendarCache.removeChain(data.id || 0);
            easyFinance.models.calendarCache.reloadWidgets();
        });
    }

    function load(result){
        for (v in result) {
            ddt.setTime(result[v].timestamp * 1000);
            ddt_month = ddt2month[ddt.getMonth()];
            $('.calendar_block .ui-datepicker-title').each(function(){
                var month = $(this).find('span.ui-datepicker-month').text();
                var year = $(this).find('span.ui-datepicker-year').text();
                if (month == ddt_month && year == ddt.getFullYear()) {
                    $(this).closest('.calendar_block').find('td').removeClass('hasEvents').find('a').removeAttr('used').removeAttr('style');
                }
            });
        }
        for (v in result) {
            ddt.setTime(result[v].timestamp * 1000);
            ddt_month = ddt2month[ddt.getMonth()];
            $('.calendar_block .ui-datepicker-title').each(function(){
                var month = $(this).find('span.ui-datepicker-month').text();
                var year = $(this).find('span.ui-datepicker-year').text();
                if (month == ddt_month && year == ddt.getFullYear()) {
                    ddt_day = ddt.getDate();
                    $(this).closest('.calendar').find('td a').each(function(){
                        if ($(this).text() == ddt_day) {
                            if ($(this).css('priority') != 'red') {
                                if (result[v].accepted == '0') {
                                    // подкрашиваем неподтверждённые события
                                    $(this).css('color', 'red');
                                } else {
                                    // подкрашиваем подтверждённые события
                                    $(this).css('color', 'green');
                                }
                            }
                            $(this).attr('date', $.datepicker.formatDate('dd.mm.yy', ddt)).attr('used', ($(this).attr('used') || '') +
                            '<tr><td style="text-align:left;width:100%"><nobr><b>' +
                            shorter((easyFinance.models.category.getUserCategoryNameById(result[v].cat_id) || 'Без категории'), 13) +
                            '</b></nobr></td><td style="text-align:right"><nobr>' +
                            (result[v].money >= 0 ? ('<span class="sumGreen">') : ('<span class="sumRed">')) +
                            formatCurrency(result[v].money) +
                            '</span> ' +
                            (easyFinance.models.accounts.getAccountCurrencyText(result[v].account) || easyFinance.models.currency.getDefaultCurrencyText()) +
                            '</nobr></td></tr>').closest('td').addClass('hasEvents');
                        }
                    });
                }
            });
        }
        //Right cal
        $('.calendar_block .hasDatepicker').qtip({
            content: (''),
            position: {
                corner: {
                    target: 'bottomMiddle',
                    tooltip: 'topMiddle'
                }
            },
            style: 'modern'
        });
        $('.calendar_block .hasDatepicker td, #calend .hasDatepicker td').removeAttr('onclick').find('a').removeAttr('href');
        $('.calendar_block .hasDatepicker td a').live('mouseover', function(){
            var content = $(this).attr('used') ? ('<div></div><table class="calendar_tip">' +
            $(this).attr('used') +
            '</table>') : '<div style="color:#B4B4B4">На выбранный день ничего не запланировано</div>';
            $('.calendar_block .hasDatepicker').qtip('api').updateContent(content);
        });
        $('.calendar_block .hasDatepicker td a').live('click', function(){
            var dat = getCurrentDate();
            dat.setDate($(this).text())

            easyFinance.widgets.operationEdit.fillFormCalendar({date: $.datepicker.formatDate('dd.mm.yy', dat)}, false, true);

            return false;
        });
    }

    function getCurrentDate(){
        return _currentDate;
    }

    return {
        getCurrentDate: getCurrentDate,
        load: load,
        init: init
    }
}();
$(document).ready(function(){
    easyFinance.widgets.calendarPreview.init();
});
