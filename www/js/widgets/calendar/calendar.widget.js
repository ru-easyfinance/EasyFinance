easyFinance.widgets.calendar = function(){
    var _data, _editor;
    var _d = new Date();
    function _positioningToolbar(left, top){
//        alert(left + top);
        $('#calendar #popupMenuWithEventsForCalendar').css({
            left: left,
            top: top
        }).addClass('act');
    }



    function init(){
        
        $.fullCalendar.monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        $.fullCalendar.monthNamesShort = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
        $.fullCalendar.dayNames = ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
        $.fullCalendar.dayNamesShort = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];
        $('#datepicker').datepicker({numberOfMonths: 3}).datepicker();
//        $('#calend').mouseout(function(){
//            $('#calendar #popupMenuWithEventsForCalendar').removeClass('act');
//        });
        $('#calendar').fullCalendar({
                year: _d.getFullYear(),
                month: _d.getMonth(),
                titleFormat: {
                    month: 'MMMM yyyy',                             // September 2009
                    week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}", // Sep 7 - 13 2009
                    day: 'dddd, MMM d, yyyy'                  // Tuesday, Sep 8, 2009
                },
                header: false,
                showTime: 'guess',
                timeFormat: {
                    // for agendaWeek and agendaDay
                    agenda: 'h:mm{ - h:mm}', // 5:00 - 6:30
                    // for all other views
                    '': 'h:mm'            // 7p
                },
                editable: true,
                disableResizing: true,
                firstDay: 1,
                DragOpacity: {
                    // for agendaWeek and agendaDay
                    agenda: 0.5,

                    // for all other views
                    '': 0.5
                },
                events: function(start, end, calback) {
                    s = new Date(start.getFullYear(), start.getMonth()-2, start.getDate()+14);//+10??там разбег всего на пять, но лучше перестраховаться @todo 0_о Разобраться с автором...
                    var e = new Date(s.getFullYear(), s.getMonth()+4, s.getDate());


                    // ----------------- Date Bar -------------------------------------------
                    var date = $('#calendar').fullCalendar('getDate');
                    var month = date.getMonth();
                    var year = date.getFullYear();
                    var monthTitle = $.fullCalendar.monthNames[month] + ' ' + year.toString();
                    var _$cal = $('#views');

                    _$cal.find('li.y_prev a').text(year-1);
                    _$cal.find('li.y_next a').text(year+1);
                    // -- некрасиво .. нелогично .. не нравится
                    if (month == 11) {
                        _$cal.find('li.m_next a').text($.fullCalendar.monthNames[0]);
                    } else {
                        _$cal.find('li.m_next a').text($.fullCalendar.monthNames[month+1]);
                    }
                    if (month === 0) {
                        _$cal.find('li.m_prev a').text($.fullCalendar.monthNames[11]);
                    } else {
                        _$cal.find('li.m_prev a').text($.fullCalendar.monthNames[month-1]);
                    }

                    _$cal.find('li.cur').text(monthTitle);

                    // ---------------- Date Bar --------------------------------------------

                    var typesToClasses = {
                        '0' : 'red',
                        '1' : 'green',
                        '2' : 'yellow',
                        '4' : 'blue'
                    }
                    _data = easyFinance.models.calendarCache.loadSetupData(month, year);
                    var nowDate = new Date();
                    var calendarArray = [];
                    for(var v in _data){
                        var title = formatCurrency(_data[v].money);
                        if (_data[v].account &&
                            _data[v].account != 0 &&
                            easyFinance.models.accounts.getAccountCurrencyText(_data[v].account) != easyFinance.models.currency.getDefaultCurrencyText()){
                            title += easyFinance.models.accounts.getAccountCurrencyText(_data[v].account)
                        }


                        var overdue = (_data[v].accepted == 0 && _data[v].timestamp*1000 < nowDate.getTime());
                        calendarArray.push({
                            key: v,
                            id: _data[v].id,
                            title: title,
                            date: _data[v].timestamp,
                            end : _data[v].timestamp,
                            showTime: 0,
                            className: typesToClasses[_data[v].type] + ' '+ (_data[v].accepted == 1 ? 'accepted' : (overdue ? 'overdue' : '')),//@todo
                            draggable : true
                        });
                    }
                    
                    calback(calendarArray);
                    //cont
                    $('#calendar .fc-content #popupMenuWithEventsForCalendar').remove();
                    $('#calendar .fc-content').append('<div class="cont" id="popupMenuWithEventsForCalendar"><ul style="display:block">'+
                        '<li title="Подтвердить" class="accept"><a></a></li>'+
                        '<li title="Редактировать" class="edit"><a></a></li>'+
                        '<li title="Удалить" class="del"><a></a></li></ul></div>');
                    $('#calendar .fc-content #popupMenuWithEventsForCalendar li.edit').click(function(){
//                        alert('edit' + $('#calendar .fc-content #popupMenuWithEventsForCalendar').attr('key'));
                        var element = _data[$('#calendar .fc-content #popupMenuWithEventsForCalendar').attr('key')];
                        promptSingleOrChain("edit", function(isChain){
                            easyFinance.widgets.operationEdit.fillFormCalendar(element, true, isChain);
                        });
                    });
                    $('#calendar .fc-content #popupMenuWithEventsForCalendar li.del').click(function(){
//                        alert('del' + $('#calendar .fc-content #popupMenuWithEventsForCalendar').attr('key'));
                        var element = _data[$('#calendar .fc-content #popupMenuWithEventsForCalendar').attr('key')];
                        var chainId = element.chain; // заполнить!
                        var operationId = element.id; // заполнить!

                        promptSingleOrChain("delete", function(isChain) {
                            if (isChain)
                                easyFinance.models.accounts.deleteOperationsChain(chainId, function() {
//                                    alert('удалили цепочку!');
                                });
                            else
                                easyFinance.models.accounts.deleteOperationsByIds([operationId], [], function() {
                                });
                        });
                    });
                    $('#calendar .fc-content #popupMenuWithEventsForCalendar:not(.accepted) li.accept').click(function(){
//                        alert('acc' + $('#calendar .fc-content #popupMenuWithEventsForCalendar').attr('key'));
                        var operationId = _data[$('#calendar .fc-content #popupMenuWithEventsForCalendar').attr('key')].id;
                        easyFinance.models.accounts.acceptOperationsByIds([operationId]);
                    });
                    //cont
                },
                eventClick: function(event, element, view){
                    var elem = _data[event.key];
                        promptSingleOrChain("edit", function(isChain){
                            easyFinance.widgets.operationEdit.fillFormCalendar(elem, true, isChain);
                        });
                },
                dayClick: function(date, allDay, jsEvent, view){
                    var data = {
                        date: $.datepicker.formatDate('dd.mm.yy',date),
                        time: ''//dt.toLocaleTimeString().substr(0, 5)
                    };
                    easyFinance.widgets.operationEdit.fillFormCalendar(data,false,true);
                },
                eventDragStart: function(calEvent, jsEvent, ui){

                },
                eventMouseover: function( event, jsEvent, view ) {
//                    debugger;
                    _positioningToolbar(jsEvent.currentTarget.style.left, jsEvent.currentTarget.style.top);
                    $('#calendar #popupMenuWithEventsForCalendar').attr('key', event.key);
                    if (_data[event.key].accepted == '1'){
                        $('#calendar #popupMenuWithEventsForCalendar').addClass('accepted');
                        $('#calendar #popupMenuWithEventsForCalendar li.accept').hide();
                    }else{
                        $('#calendar #popupMenuWithEventsForCalendar').removeClass('accepted');
                        $('#calendar #popupMenuWithEventsForCalendar li.accept').show();
                    }
                },
                eventDrop: function(calEvent, jsEvent, ui){
                    easyFinance.models.accounts.editOperationDateById(calEvent.id, $.datepicker.formatDate('dd.mm.yy',calEvent.start));
                    $('.qtip').remove();
                },
                eventRender: function(calEvent, element){
                    var event = _data[calEvent.key];
                    var template = '';

                    var typeToStr = {
                        '0' : 'Расход',
                        '1' : 'Доход',
                        '2' : 'Перевод со счёта',
                        '4' : 'Перевод на финцель'
                    };
                    var typeToClass = {
                        '0' : 'plus',
                        '1' : 'minus',
                        '2' : 'transfer',
                        '4' : 'target'
                    };

                    if (event.repeat != null){
                        template = 'Повторяется';
//                        var lastChar = event.repeat.toString().substr(event.repeat.toString().length-1, 1);
                        switch (event.every){
                            case '0':
                                template = 'Без повторения';
                                break;
                            case '1':
                                template += ' ежедневно';
                                if (event.repeat < 500){
                                    template += ' (' + event.repeat + ' дн.)';
                                }else{
                                    template += ' до' +  $.datepicker.formatDate('dd.mm.yy',Date(event.repeat));
                                }

                                break;
                            case '7':
                                template += ' еженедельно по';
                                if (event.week.toString().substr(0, 1) == 1){
                                    template += ' понедельникам,';
                                }
                                if (event.week.toString().substr(1, 1) == 1){
                                    template += ' вторникам,';
                                }
                                if (event.week.toString().substr(2, 1) == 1){
                                    template += ' средам,';
                                }
                                if (event.week.toString().substr(3, 1) == 1){
                                    template += ' четвергам,';
                                }
                                if (event.week.toString().substr(4, 1) == 1){
                                    template += ' пятницам,';
                                }
                                if (event.week.toString().substr(5, 1) == 1){
                                    template += ' субботам,';
                                }
                                if (event.week.toString().substr(6, 1) == 1){
                                    template += ' воскресеньям,';
                                }
                                template = template.replace(/\,$/,'');
                                if (event.repeat < 500){
                                    template += ' (' + event.repeat + ' дн.)';
                                }else{
                                    template += ' до' +  $.datepicker.formatDate('dd.mm.yy',Date(event.repeat));
                                }

                                break;
                            case '30':
                                template += ' ежемесячно';
                                if (event.repeat < 500){
                                    template += ' (' + event.repeat + ' мес.)';
                                }else{
                                    template += ' до' +  $.datepicker.formatDate('dd.mm.yy',Date(event.repeat));
                                }

                                break;
                            case '365':
                                template += ' ежегодно';
                                if (event.repeat < 500){
                                    template += ' (' + event.repeat + ' г.)';
                                }else{
                                    template += ' до' +  $.datepicker.formatDate('dd.mm.yy',Date(event.repeat));
                                }
                                break;
                        }
                        
                        
                    }
//                    var ddt = new Date(event.date*1000);

//                    var modelAccounts = easyFinance.models.accounts;
//                    var modelCurrency = easyFinance.models.currency;

//                    var account = modelAccounts.getAccountById(event.account);

                    var tipContent = '<div style="text-align:left"><div><b>'+ 
                        (easyFinance.models.category.getUserCategoryNameById(event.cat_id)||'Без категории') +
                        '</b></div>' +
                        '<div class="calendar_tooltip '+typeToClass[event.type]+'"><div>&nbsp;&nbsp;&nbsp;&nbsp;</div>'+typeToStr[event.type] + '</div>'+
                        '<div>'+(event.accepted == '1' ? 'Подтверждено' : 'Не подтверждено') + '</div>' +
                        '<div style="border-bottom: 1px dotted #e4e4e4; border-top: 1px dotted #e4e4e4;"><i>'+template+'</i></div>' +
                        '<div>' + (event.comment || '') + '</div></div>';
                    $(element).qtip({
                        content: tipContent,
                        position: {
                            corner: {
                                target: 'leftMiddle',
                                tooltip: 'rightMiddle'
                            }
                        },
                        style: 'modern'
                    });

                }
            });


            $('#views').addClass('ui-widget-content');
            var _$cal = $('#views');
            _$cal.find('li.m_prev').click(function(){
                var date = $('#calendar').fullCalendar('getDate');
                
                date.setFullYear(date.getFullYear(), date.getMonth()-1, 1);
                easyFinance.models.calendarCache.reloadWidgets(date.getMonth(),date.getFullYear());
            });
            _$cal.find('li.m_next').click(function(){
                var date = $('#calendar').fullCalendar('getDate');
                
                date.setFullYear(date.getFullYear(), date.getMonth()+1, 1);
                easyFinance.models.calendarCache.reloadWidgets(date.getMonth(),date.getFullYear());

            });
            _$cal.find('li.y_prev').click(function(){
                var date = $('#calendar').fullCalendar('getDate');
                date.setFullYear(date.getFullYear(), date.getMonth()-1, 1);
                easyFinance.models.calendarCache.reloadWidgets(date.getMonth(),date.getFullYear());

            });
            _$cal.find('li.y_next').click(function(){
                var date = $('#calendar').fullCalendar('getDate');
                date.setFullYear(date.getFullYear(), date.getMonth()+1, 1);
                easyFinance.models.calendarCache.reloadWidgets(date.getMonth(),date.getFullYear());

            });

            _$cal.find('li.cur').click(function(){
                easyFinance.models.calendarCache.reloadWidgets();
                $('#calendar').fullCalendar('today');
            });
    }
    return {init: init};
}();

