easyFinance.widgets.calendarPreview = function(){
    var ddt = new Date();
    var now = new Date();
    var ddt_day , ddt_month;
    var ddt2month=['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
	function init(){
		if ($('#calend').length > 0) {
		}
		else {
			//            $(document).bind(
			//                'operationsAccepted operationEdited operationsDeleted operationsChainAdded operationsChainEdited operationsChainDeleted operationDateEdited',
			//                function(data){
			//                    easyFinance.models.calendarCache.clean();
			//                    easyFinance.models.calendarCache.init(data.calendar || {});
			//                    var date = new Date();
			//                    date.setFullYear(date.getFullYear(), date.getMonth()+1, 1);
			//                    easyFinance.models.calendarCache.reloadWidgets(date.getMonth(),date.getFullYear());
			//                }
			//
			//            );
			$(document).bind('operationEdited operationsChainAdded operationsChainEdited operationDateEdited', function(data){
				easyFinance.models.calendarCache.clean();
				easyFinance.models.calendarCache.init(data.calendar || {});
				var date = new Date();
				date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
				easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
			});
			
			
			$(document).bind('operationsAccepted', function(data){
				easyFinance.models.calendarCache.acceptElements(data.ids || []);
				var date = new Date();
				date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
				easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
			});
			$(document).bind('operationsDeleted', function(data){
				easyFinance.models.calendarCache.removeElements(data.ids || []);
				var date = new Date();
				date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
				easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
			});
			$(document).bind('operationsChainDeleted', function(data){
				easyFinance.models.calendarCache.removeChain(data.id || 0);
				var date = new Date();
				date.setFullYear(date.getFullYear(), date.getMonth() - 1, 1);
				easyFinance.models.calendarCache.reloadWidgets(date.getMonth() - 1, date.getFullYear());
			});
		}
	}


    function load(result){
        for(var v in result){
            ddt.setTime(result[v].timestamp*1000);
            ddt_month = ddt2month[ddt.getMonth()];
            $('.calendar_block .ui-datepicker-title, #calend .ui-datepicker-title').each(function(){
                var month = $(this).find('span.ui-datepicker-month').text();
                var year = $(this).find('span.ui-datepicker-year').text();
                if (month == ddt_month && year == ddt.getFullYear()){
                    $(this).closest('.ui-datepicker-group, .calendar').find('td').removeClass('hasEvents').find('a').removeAttr('used').removeAttr('style');
                }
            });
        }
        for(v in result){
            ddt.setTime(result[v].timestamp*1000);
            ddt_month = ddt2month[ddt.getMonth()];
            $('.calendar_block .ui-datepicker-title, #calend .ui-datepicker-title').each(function(){
                var month = $(this).find('span.ui-datepicker-month').text();
                var year = $(this).find('span.ui-datepicker-year').text();
                if (month == ddt_month && year == ddt.getFullYear()){
//                    $(this).closest('.ui-datepicker-group, .calendar').find('td').removeClass('hasEvents').find('a').removeAttr('used').removeAttr('style');
                    ddt_day = ddt.getDate();
                    $(this).closest('.ui-datepicker-group,.calendar').find('td a').each(function(){
                        if ($(this).text() == ddt_day){
                            if ($(this).css('priority') != 'red' ){
                                if (result[v].accepted == '0' && now >= ddt){
                                    $(this).css('color', 'red');
                                }else{
                                    $(this).css('color', '#000000');
                                }
                            }
                            $(this).
                                attr('date',$.datepicker.formatDate('dd.mm.yy', ddt)).
                                attr('used',($(this).attr('used')||'') +
                                    '<tr><td style="text-align:left;width:100%"><nobr><b>' +
                                    shorter((easyFinance.models.category.getUserCategoryNameById(result[v].cat_id)||'Без категории'),13) +
                                    '</b></nobr></td><td style="text-align:right"><nobr>' +
                                    ( result[v].money >= 0 ?('<span class="sumGreen">') : ('<span class="sumRed">')) +
                                    formatCurrency(result[v].money) + '</span> ' +
                                    (easyFinance.models.accounts.getAccountCurrencyText(result[v].account) || easyFinance.models.currency.getDefaultCurrencyText()) +
                                    '</nobr></td></tr>').
                                closest('td').
                                addClass('hasEvents');
                        }
                    });
                }
            });
        }
        //calend
        if ($('#calend').length > 0 ){
            $('#calend .hasDatepicker').qtip({
                content: (''),
                position: {
                    corner: {
                        target: 'topMiddle',
                        tooltip: 'bottomMiddle'
                    }
                },
                style: 'modern'
            });

            $('#calend .hasDatepicker td a').live('mouseover',function(){
                    var content =  $(this).attr('used') ?
                        (   '<div></div><table class="calendar_tip">' +
                            $(this).attr('used') + '</table>') :
                        '<div style="color:#B4B4B4">На выбранный день ничего не запланировано</div>';
                    $('#datepicker.hasDatepicker').qtip('api').updateContent(content);
                
            });
            $('.hasDatepicker td a').live('click',function(){
//                    debugger;
                    var ddt2month=['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
                    var month = $(this).closest('.calendar, .ui-datepicker-group').find('span.ui-datepicker-month').text();
                    var year = $(this).closest('.calendar, .ui-datepicker-group').find('span.ui-datepicker-year').text();
                    var day = $(this).text();
                    for(var tmpKey in ddt2month){
                        if (ddt2month[tmpKey] == month){
                            month = tmpKey;
                            break;
                        }
                    }
                    var dayDate = new Date(year, month, day, 1, 1, 1, 1);
                    var data = {
                        date: $.datepicker.formatDate('dd.mm.yy',dayDate ),
                        time: ''//dt.toLocaleTimeString().substr(0, 5)
                    };
                    easyFinance.widgets.operationEdit.fillFormCalendar(data,false,true);
                });
        }else{
//            $(document).bind(
//                'operationsAccepted operationEdited operationsDeleted operationsChainAdded operationsChainEdited operationsChainDeleted operationDateEdited',
//                function(data){
//                    easyFinance.models.calendarCache.clean();
//                    easyFinance.models.calendarCache.init(data.calendar || {});
//                    var date = new Date();
//                    date.setFullYear(date.getFullYear(), date.getMonth()+1, 1);
//                    easyFinance.models.calendarCache.reloadWidgets(date.getMonth(),date.getFullYear());
//                }
//
//            );
//            $(document).bind(
//                'operationEdited operationsChainAdded operationsChainEdited operationDateEdited',
//                function(data){
//                    easyFinance.models.calendarCache.clean();
//                    easyFinance.models.calendarCache.init(data.calendar || {});
//                    var date =  new Date();
//                    date.setFullYear(date.getFullYear(), date.getMonth()-1, 1);
//                    easyFinance.models.calendarCache.reloadWidgets(date.getMonth()-1,date.getFullYear());
//                }
//            );
//
//
//            $(document).bind(
//                'operationsAccepted',
//                function(data){
//                    easyFinance.models.calendarCache.acceptElements(data.ids || []);
//                    var date =  new Date();
//                    date.setFullYear(date.getFullYear(), date.getMonth()-1, 1);
//                    easyFinance.models.calendarCache.reloadWidgets(date.getMonth()-1,date.getFullYear());
//                }
//            );
//                $(document).bind(
//                'operationsDeleted',
//                function(data){
//                    easyFinance.models.calendarCache.removeElements(data.ids || []);
//                    var date =  new Date();
//                    date.setFullYear(date.getFullYear(), date.getMonth()-1, 1);
//                    easyFinance.models.calendarCache.reloadWidgets(date.getMonth()-1,date.getFullYear());
//                }
//            );
//            $(document).bind(
//                'operationsChainDeleted',
//                function(data){
//                    easyFinance.models.calendarCache.removeChain(data.id || 0);
//                    var date =  new Date();
//                    date.setFullYear(date.getFullYear(), date.getMonth()-1, 1);
//                    easyFinance.models.calendarCache.reloadWidgets(date.getMonth()-1,date.getFullYear());
//                }
//            );

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
        $('.calendar_block .hasDatepicker td a').live('mouseover',function(){
            var content =  $(this).attr('used') ?
                (   '<div></div><table class="calendar_tip">' +
                    $(this).attr('used') + '</table>') :
                '<div style="color:#B4B4B4">На выбранный день ничего не запланировано</div>';
            $('.calendar_block .hasDatepicker').qtip('api').updateContent(content);
        });
        $('.calendar_block .hasDatepicker td a').live('click',function(){
            var data = {
                date: $(this).attr('date'),
                time: ''//dt.toLocaleTimeString().substr(0, 5)
            };
            easyFinance.widgets.operationEdit.fillFormCalendar(data,false,true);
        });
        
    }
    
    
    
    return {
        load : load,
		init: init
    }
}();
$(document).ready(function(){
	easyFinance.widgets.calendarPreview.init();
        if (res.calendar && typeof(easyFinance.widgets.calendar)!='object' && typeof(easyFinance.widgets.calendar)!='function'){
            easyFinance.models.calendarCache.init(res.calendar.calendar);
            if ($('#calend').length == 0){
                easyFinance.models.calendarCache.reloadWidgets();
            }
        }
});