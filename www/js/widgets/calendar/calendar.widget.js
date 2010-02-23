easyFinance.widgets.calendar = function(){
    var _data, _editor;
    var _d = new Date();
    function init(){
//        var ddt2month=['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        _editor = calendarEditor;
        $.fullCalendar.monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        $.fullCalendar.monthAbbrevs = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
        $.fullCalendar.dayNames = ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
        $.fullCalendar.dayAbbrevs = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];
        $('#per_tabl_header th.chk input, #ev_tabl th input[type="checkbox"]').change(function(){
            if ($(this).attr('checked')){
                $(this).closest('div').find('input[type=checkbox]').attr('checked', 'checked');
            }else{
                $(this).closest('div').find('input[type=checkbox]').removeAttr('checked');
            }
        });
        /* $('#event_with_select_events span#remove_all_cal').click(function() {
            var ch = $(' #ev_tabl tr td input:checked, #per_tabl tr td input:checked');
            if ($(ch).length > 0 && confirm('Удалить выбранные события?')) {
                var obj = new Array ();
                $(ch).closest('tr').each(function(){
                    obj.push($(this).attr('id').replace('ev_', ''));
                });
                $.jGrowl('События удаляются!',{theme : 'green'});
                $.post('/calendar/reminderDel?responseMode=json',{ids : obj.toString()},function(data){
                    $.jGrowl('События удалены!',{theme : 'green'});
                    calendarLeft.init();
                    $('#calendar').fullCalendar('refresh');
                },'json');
            }
        }); */

        $('#datepicker').datepicker({numberOfMonths: 3}).datepicker();
var s;
$('#calendar').fullCalendar({
        weekStart: 1,
        draggable: false,
        year: _d.getFullYear(),
        month: _d.getMonth(),
        fixedWeeks: false,
        abbrevDayHeadings: true,
        title: false,
        titleFormat: 'F Y',
        buttons: false,
        showTime: 'guess',
        timeFormat: "G:i",
        monthDisplay: function(year, month, monthTitle) {
            var _$cal = $('#views');
            $('#datepicker').datepicker('setDate' , new Date(year, month-1));
            _$cal.find('li.y_prev a').text(year-1);
            _$cal.find('li.y_next a').text(year+1);
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
        },
        dayClick: function(dayDate) {
            var i = 1;
            var weekDay = dayDate.getDay();
            if (weekDay === 0){
                weekDay = 7;
            }
            _editor.load();
            $('#cal_date').val($.datepicker.formatDate('dd.mm.yy',dayDate ));
            $('#week.week input').each(function(){
                if (i == weekDay){
                    $(this).attr('checked', 'checked');
                }
                i++;
            });
        },
        eventDragOpacity: 0.5,
        eventRevertDuration: 900,
        events: function(start, end, calback) {
            s = new Date(start.getFullYear(), start.getMonth()-2, start.getDate()+10);//+10??там разбег всего на пять, но лучше перестраховаться @todo 0_о Разобраться с автором...
            var e = new Date(s.getFullYear(), s.getMonth()+4, s.getDate());
            easyFinance.models.calendarOld.getOperationsByInterval(s.getTime(), e.getTime(), function(result){
                    _data = result;
                    easyFinance.widgets.calendarPreview.load(result);
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
                                (   '<div><b>' +($(this).attr('date')||'') +
                                    '</b></div><table class="calendar_tip">' +
                                    $(this).attr('used') + '</table>') :
                                '<div style="color:#B4B4B4">На выбранный день ничего не запланировано</div>';
                            $('#datepicker.hasDatepicker').qtip('api').updateContent(content);
                    });
                    var ddt = new Date();
//                    var now = new Date();
                    var eventsList='', periodicList='';
                    var calendarArray = [];
                    var monthForList = s.getMonth() + 2;
                    if (monthForList >11){
                        monthForList += -12;
                    }

//                    easyFinance.widgets.calendarRight(result);
                    for(var v in result){
                        var accept  = result[v].accept == '1' ? 'accept':'reject';
                        ddt.setTime(result[v].date*1000);

                        if (result[v].type == 'p'){
                            if (ddt.getMonth() == monthForList){
                                periodicList += '<tr id="ev_' + v + '"><td class="chk"><input type="checkbox" value="" /></td>' +
                                        '<td>' + $.datepicker.formatDate('dd.mm.yy',ddt) + '</td>' +
                                        '<td>' + result[v].title + '</td>' +
                                        '<td>' + result[v].amount + '</td>' +
                                        '<td><div class="cont" style="top: -17px"><ul style="right:0">' +
                                            '<li class="edit"><a title="Редактировать">Редактировать</a></li>' +
                                            '<li class="del"><a title="Удалить">Удалить</a></li>' +
                                            '</ul></div></td><td class="'+accept+'" style="width:16px">&nbsp;&nbsp;&nbsp;</td>' +
                                        '</tr>';
                            }
                            if (result[v].op_type == '1'){
                                calendarArray.push({
                                    key: v,
                                    id: result[v].id,
                                    title: result[v].title,
                                    date: result[v].date,
                                    end : result[v].date,
                                    showTime: 0,
                                    className: 'green',
                                    draggable : true
                                });
                            }else{
                                calendarArray.push({
                                    key: v,
                                    id: result[v].id,
                                    title: result[v].title,
                                    date: result[v].date,
                                    end : result[v].date,
                                    showTime: 0,
                                    className: 'red',
                                    draggable : true
                                });
                            }
                        }else if (result[v].type == 'e'){
                            if (ddt.getMonth() == monthForList){
                                eventsList += '<tr id="ev_' + v + '"><td class="chk"><input type="checkbox" value="" /></td>' +
                                            '<td>' + $.datepicker.formatDate('dd.mm.yy', ddt) + ' ' + ddt.toLocaleTimeString().substr(0, 5) + '</td>' +
                                            '<td>'+result[v].title+'</td>' +
                                            '<td><div class="cont" style="top: -17px"><ul style="right:-0px;">' +
                                            '<li class="edit"><a title="Редактировать">Редактировать</a></li>' +
                                            '<li class="del"><a title="Удалить">Удалить</a></li>' +
                                            '</ul></div></td><td class="'+accept+'" style="width:16px">&nbsp;&nbsp;&nbsp;</td>' +
                                            '</tr>';
                            }

                            calendarArray.push({
                                key: v,
                                id: result[v].id,
                                title: result[v].title,
                                date: result[v].date,
                                end : result[v].date,
                                className: 'yellow',
                                draggable : true
                            });
                        }
                    }
                    $('#ev_tabl tbody').html(eventsList);
                    $('#per_tabl tbody').html(periodicList);
                    $('#per_tabl tr, #ev_tabl tr').dblclick(function(){
                        $(this).find('li.edit a').click();
                    });
                    $('#per_tabl tr td.reject, #ev_tabl tr td.reject').click(function(){
                        $.post('/calendar/reminderAccept?responseMode=json',
                            {ids : $(this).closest('tr').attr('id').replace('ev_', '')},
                            function(){$('#calendar').fullCalendar('refresh');},
                            'json');
                    });
//                    $.post('calendar/reminderAccept',{ids : obj.toString()},function(){},'json')
                    $('tr .cont ul li.edit a').click(function(){
                        var element = _data[$(this).closest('tr').attr('id').replace('ev_', '')];
                        var type = element.type == 'e'?'event':'periodic';
                        _editor.load({el:element, type:type});
                    });
                    $('tr .cont ul li.del a').click(function(){
                        _editor.del({id:$(this).closest('tr').attr('id').replace('ev_', '')});
                    });
                    calback(calendarArray);
            });
                
            
        },
        eventClick: function(calEvent, jsEvent) {
            var element = _data[calEvent.key];
            var type = element.type == 'e'?'event':'periodic';
            _editor.load({el:element, type:type});
        },
        eventDragStart: function(calEvent, jsEvent, ui){
            $('#calend .full-calendar-month table tr td').css('cursor','crosshair');
        },
        eventDragStop: function(calEvent, jsEvent, ui){
            $('.qtip:visible').remove();
            $('#calend .full-calendar-month table tr td').css('cursor','pointer')
            _data[calEvent.key].date = Math.floor(calEvent.start / 1000);
            var ret = {};
                ret = $.extend(ret, _data[calEvent.key]);
            var workDate = new Date(ret.date*1000);
            ret.date = $.datepicker.formatDate('dd.mm.yy', workDate);
            var startDate = new Date(ret.start*1000);
            ret.start = $.datepicker.formatDate('dd.mm.yy', startDate);
            ret.use_mode = 'single';
            $.post('/calendar/edit?responseMode=json',ret,function(data){
                $('#calendar').fullCalendar('refresh');
            },'json');
        },
        eventRender: function(calEvent, element){
            var event = _data[calEvent.key];
            var template = 'Повторяется ';
            // повторять пять раз
            if (event.repeat > 365){
                var dt = new Date((event.repeat)*1000);
                template += 'до ' + $.datepicker.formatDate('dd.mm.yy', dt);
//            }else if (event.repeat == '0'){
//                template += 'бесконечно';
            }else{
                var lastChar = event.repeat.toString().substr(event.repeat.toString().length-1, 1);
                if((event.repeat > 20 || event.repeat < 10) && (lastChar == '4' || lastChar == '3' || lastChar == '2')){
                    template += event.repeat + ' раза';
                }else{
                    template += event.repeat + ' раз';
                }
            }
            //каждый день , еженедельно
            switch (event.every){
                case '0':
                    template = 'Без повторения';
                    break;
                case '1':
                    template += ' каждый день ';
                    break;
                case '7':
                    template += ' каждую неделю по';
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
                    break;
                case '30':
                    template += ' каждый месяц ';
                    break;
                case '365':
                    template += ' каждый год ';
                    break;
            }
            var ddt = new Date(event.date*1000);

            var modelAccounts = easyFinance.models.accounts;
            var modelCurrency = easyFinance.models.currency;

            var account = modelAccounts.getAccountById(event.account);
            var currencyId = account ? account.currency : '1'; // should use default instead of "1"?
            var strCurrency = modelCurrency.getCurrencyTextById(currencyId);

            var tipContent = '<div style="text-align:left"><div><b>'+ (event.title || 'Без названия') +'</b></div>' +
                '<div>'+$.datepicker.formatDate('dd.mm.yy',ddt)+' ' +
                (event.type == 'e' ?
                    ddt.toLocaleTimeString().substr(0, 5) :
                    ('</div><div style="font-weight:bolder;color:'+
                        (event.op_type > 0 ?
                            'green">' :
                            'red"> -') + (event.amount ? formatCurrency(Math.abs(event.amount)) : '0.00') +
                        ' ' + strCurrency)) + '</div>' + //@todo FIX
                '<div>'+(event.accept == '1' ? 'Подтверждено' : (_d > ddt ? 'Просрочено' : 'Не подтверждено')) + '</div>' +
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
    //_$cal.find('.full-calendar-buttons .today, .prev-month, .prev-year, .next-month, .next-year').addClass('ui-fullcalendar-button ui-button ui-state-default ui-corner-all');
    _$cal.find('li.m_prev').click(function(){$('#calendar').fullCalendar('prevMonth');});
    _$cal.find('li.m_next').click(function(){$('#calendar').fullCalendar('nextMonth');});
    _$cal.find('li.y_prev').click(function(){$('#calendar').fullCalendar('prevYear');});
    _$cal.find('li.y_next').click(function(){$('#calendar').fullCalendar('nextYear');});
    _$cal.find('li.cur').click(function(){$('#calendar').fullCalendar('today');});
    }
    return {init: init};
};
