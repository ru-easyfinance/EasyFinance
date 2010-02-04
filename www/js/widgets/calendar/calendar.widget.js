easyFinance.widgets.calendar = function(){
    var _data, _editor;
    var _d = new Date();

    var eventToPriorityAndColor = {//if acept
        e : {
            accept : {// podtverzdenniy
                '1' : {priority :'12', color :'#575757'},
                '7' : {priority :'14', color :'#575757'},
                '30' : {priority :'16', color :'#575757'},
                '365' : {priority :'18', color :'#575757'},
                '0' : {priority :'20', color :'#575757'}
            },
            unsworn : {// ne podtwerzdenniy 
                '1' : {priority :'12', color :'#6fee55'},
                '7' : {priority :'14', color :'#2ab601'},
                '30' : {priority :'16', color :'#217e03'},
                '365' : {priority :'18', color :'#0a15ea'},
                '0' : {priority :'20', color :'#0a15ea'}
            },
            expired : {// prosrocheniy
                '1' : {priority :'4', color :'#1a1a1a'},
                '7' : {priority :'5', color :'#1a1a1a'},
                '30' : {priority :'6', color :'#1a1a1a'},
                '365' : {priority :'8', color :'#1a1a1a'},
                '0' : {priority :'10', color :'#1a1a1a'}
            }
        },
        p : {
            accept : {
                '1' : {priority :'11', color :'#575757'},
                '7' : {priority :'13', color :'#575757'},
                '30' : {priority :'15', color :'#575757'},
                '365' : {priority :'17', color :'#575757'},
                '0' : {priority :'19', color :'#575757'}
            },
            unsworn : {
                '1' : {priority :'11', color :'#6fee55'},
                '7' : {priority :'13', color :'#2ab601'},
                '30' : {priority :'15', color :'#217e03'},
                '365' : {priority :'17', color :'#0a15ea'},
                '0' : {priority :'19', color :'#0a15ea'}
            },
            expired : {
                '1' : {priority :'1', color :'#fe1a09'},
                '7' : {priority :'2', color :'#fe1a09'},
                '30' : {priority :'3', color :'#fe1a09'},
                '365' : {priority :'7', color :'#fe1a09'},
                '0' : {priority :'9', color :'#fe1a09'}
            }
        }
    }

    function init(){
        var ddt2month=['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        _editor = calendarEditor;
        $.fullCalendar.monthNames = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        $.fullCalendar.monthAbbrevs = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
        $.fullCalendar.dayNames = ['Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
        $.fullCalendar.dayAbbrevs = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];

        $('#datepicker').datepicker({numberOfMonths: 3}).datepicker();
        $('.hasDatepicker td').live('click',function(){
            var month = $(this).closest('.calendar, .ui-datepicker-group').find('span.ui-datepicker-month').text();
            var year = $(this).closest('.calendar, .ui-datepicker-group').find('span.ui-datepicker-year').text();
            var day = $(this).find('a').text();
            for(var tmpKey in ddt2month){
                if (ddt2month[tmpKey] == month){
                    month = tmpKey;
                    break;
                }
            }
            _editor.load();
            $('#cal_date').val(day + '.' + (Number(month) + 1) + '.' + year);

        });
        $('#datepicker.hasDatepicker').qtip({
                content: (''),
                position: {
                    corner: {
                        target: 'topMiddle',
                        tooltip: 'bottomMiddle'
                    }
                },
                style: 'modern'
            });

        $('.hasDatepicker td a').live('mouseover',function(){
                var content =  $(this).attr('used') ?
                    (   '<div><b>' +($(this).attr('date')||'') +
                        '</b></div><table class="calendar_tip">' +
                        $(this).attr('used') + '</table>') :
                    '<div style="color:#B4B4B4">На выбранный день ничего не запланировано</div>';
                $('#datepicker.hasDatepicker').qtip('api').updateContent(content);
        });
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
            _editor.load();
            $('#cal_date').val($.datepicker.formatDate('dd.mm.yy',dayDate ));
        },
        eventDragOpacity: 0.5,
        eventRevertDuration: 900,
        events: function(start, end, calback) {
            s = new Date(start.getFullYear(), start.getMonth()-2, start.getDate()+10);//+10??там разбег всего на пять, но лучше перестраховаться @todo 0_о Разобраться с автором...
            var e = new Date(s.getFullYear(), s.getMonth()+4, s.getDate());
            $.getJSON('/calendar/events/', {
                    start: s.getTime(),
                    end:   e.getTime()
                },   function(result) {
                    _data = result;
                    $('.hasDatepicker td').removeAttr('onclick').find('a').removeAttr('href');
                    var ddt = new Date();
                    var now = new Date();
                    var ddt_day , ddt_month, eventsList='', periodicList='';
                    var calendarArray = [];
                    var monthForList = s.getMonth() + 2;
                    if (monthForList >11){
                        monthForList += -12;
                    }
                    if (typeof result != 'object'){
                        return null;
                    }
                    for(var v in result){
                        var accept  = result[v].accept == '1' ? 'accept':'reject';
                        ddt.setTime(result[v].date*1000);

                        if (result[v].type == 'p'){
                            if (ddt.getMonth() == monthForList){
                                periodicList += '<tr id="ev_' + v + '"><td class="chk"><input type="checkbox" value="" /></td>' +
                                        '<td>' + $.datepicker.formatDate('dd.mm.yy',ddt) + '</td>' +
                                        '<td>' + result[v].title + '</td>' +
                                        '<td>' + result[v].amount + '</td>' +
                                        '<td><div class="cont" style="top: -17px"><ul style="right:-20px">' +
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
                        var fFilter, sFilter, tFilter , priority;
                        //get calendar month
                        ddt_month = ddt2month[ddt.getMonth()];
                        $('.hasDatepicker .ui-datepicker-title').each(function(){
                            var month = $(this).find('span.ui-datepicker-month').text();
                            var year = $(this).find('span.ui-datepicker-year').text();
                            if (month == ddt_month && year == ddt.getFullYear()){
                                ddt_day = ddt.getDate();
                                $(this).closest('.ui-datepicker-group,.calendar').find('td a').each(function(){
                                    if ($(this).text() == ddt_day){
                                        fFilter = result[v].type;
                                        sFilter = ((now < ddt) ?
                                            'unsworn' :
                                            (
                                                result[v].accept != '0' ?
                                                'accept' :
                                                'expired'
                                            )
                                        );
                                        tFilter = result[v].every;

                                        priority = eventToPriorityAndColor[fFilter][sFilter][tFilter].priority;

                                        if (!$(this).attr('priority') || $(this).attr('priority') < priority ){
                                            $(this).
                                                attr('date',$.datepicker.formatDate('dd.mm.yy', ddt)).
                                                attr('priority', priority).
                                                css('color', eventToPriorityAndColor[fFilter][sFilter][tFilter].color);
                                        }
                                        $(this).
                                            attr('used',($(this).attr('used')||'') +
                                                '<tr><td>' +
                                                result[v].title + '<td></td>' +
                                                (fFilter == 'e' ?
                                                    ddt.toLocaleTimeString().substr(0, 5) :
                                                    result[v].amount) +
                                                '</td></tr>').
                                            closest('td');//.
                                            //addClass('hasEvents');
                                    }
                                });
                            }
                        });
                    }
                    $('#ev_tabl tbody').html(eventsList);
                    $('#per_tabl tbody').html(periodicList);
                    $('#per_tabl tr, #ev_tabl tr').dblclick(function(){
                        $(this).find('li.edit a').click();
                    });
                    $('#per_tabl tr td.reject, #ev_tabl tr td.reject').click(function(){
                        $.post('/calendar/reminderAccept',
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
                },'json'
            );
        },
        eventClick: function(calEvent, jsEvent) {
            var element = _data[calEvent.key];
            var type = element.type == 'e'?'event':'periodic';
            _editor.load({el:element, type:type});
        },
        eventDragStop: function(calEvent, jsEvent, ui){
            $('.qtip:visible').remove();
            _data[calEvent.key].date = Math.floor(calEvent.start / 1000);
            var ret = {};
                ret = $.extend(ret, _data[calEvent.key]);
            var workDate = new Date(ret.date*1000);
            ret.date = $.datepicker.formatDate('dd.mm.yy', workDate);
            var startDate = new Date(ret.start*1000);
            ret.start = $.datepicker.formatDate('dd.mm.yy', startDate);
            ret.use_mode = 'single';
            $.post('/calendar/edit',ret,function(data){
                var ddt = new Date();
                var now = new Date();
                var ddt_day , ddt_month, eventsList='', periodicList='';
                var calendarArray = [];
                var monthForList = s.getMonth() + 2;
                for(var v in _data){
                        var accept  = _data[v].accept == '1' ? 'accept':'reject';
                        ddt.setTime(_data[v].date*1000);

                        if (_data[v].type == 'p'){
                            if (ddt.getMonth() == monthForList){
                                periodicList += '<tr id="ev_' + v + '"><td class="chk"><input type="checkbox" value="" /></td>' +
                                        '<td>' + $.datepicker.formatDate('dd.mm.yy',ddt) + '</td>' +
                                        '<td>' + _data[v].title + '</td>' +
                                        '<td>' + _data[v].amount + '</td>' +
                                        '<td><div class="cont" style="top: -17px"><ul style="right:-20px">' +
                                            '<li class="edit"><a title="Редактировать">Редактировать</a></li>' +
                                            '<li class="del"><a title="Удалить">Удалить</a></li>' +
                                            '</ul></div></td><td class="'+accept+'" style="width:16px">&nbsp;&nbsp;&nbsp;</td>' +
                                        '</tr>';
                            }
                            if (_data[v].op_type == '1'){
                                calendarArray.push({
                                    key: v,
                                    id: _data[v].id,
                                    title: _data[v].title,
                                    date: _data[v].date,
                                    end : _data[v].date,
                                    showTime: 0,
                                    className: 'green',
                                    draggable : true
                                });
                            }else{
                                calendarArray.push({
                                    key: v,
                                    id: _data[v].id,
                                    title: _data[v].title,
                                    date: _data[v].date,
                                    end : _data[v].date,
                                    showTime: 0,
                                    className: 'red',
                                    draggable : true
                                });
                            }
                        }else if (_data[v].type == 'e'){
                            if (ddt.getMonth() == monthForList){
                                eventsList += '<tr id="ev_' + v + '"><td class="chk"><input type="checkbox" value="" /></td>' +
                                            '<td>' + $.datepicker.formatDate('dd.mm.yy', ddt) + ' ' + ddt.toLocaleTimeString().substr(0, 5) + '</td>' +
                                            '<td>'+_data[v].title+'</td>' +
                                            '<td><div class="cont" style="top: -17px"><ul style="right:-0px;">' +
                                            '<li class="edit"><a title="Редактировать">Редактировать</a></li>' +
                                            '<li class="del"><a title="Удалить">Удалить</a></li>' +
                                            '</ul></div></td><td class="'+accept+'" style="width:16px">&nbsp;&nbsp;&nbsp;</td>' +
                                            '</tr>';
                            }

                            calendarArray.push({
                                key: v,
                                id: _data[v].id,
                                title: _data[v].title,
                                date: _data[v].date,
                                end : _data[v].date,
                                className: 'yellow',
                                draggable : true
                            });
                        }
                        var fFilter, sFilter, tFilter , priority;
                        //get calendar month
                        ddt_month = ddt2month[ddt.getMonth()];
                        $('.hasDatepicker .ui-datepicker-title').each(function(){
                            var month = $(this).find('span.ui-datepicker-month').text();
                            var year = $(this).find('span.ui-datepicker-year').text();
                            if (month == ddt_month && year == ddt.getFullYear()){
                                ddt_day = ddt.getDate();
                                $(this).closest('.ui-datepicker-group,.calendar').find('td a').each(function(){
                                    if ($(this).text() == ddt_day){
                                        fFilter = _data[v].type;
                                        sFilter = ((now < ddt) ?
                                            'unsworn' :
                                            (
                                                _data[v].accept != '0' ?
                                                'accept' :
                                                'expired'
                                            )
                                        );
                                        tFilter = _data[v].every;

                                        priority = eventToPriorityAndColor[fFilter][sFilter][tFilter].priority;

                                        if (!$(this).attr('priority') || $(this).attr('priority') < priority ){
                                            $(this).
                                                attr('date',$.datepicker.formatDate('dd.mm.yy', ddt)).
                                                attr('priority', priority).
                                                css('color', eventToPriorityAndColor[fFilter][sFilter][tFilter].color);
                                        }
                                        $(this).
                                            attr('used',($(this).attr('used')||'') +
                                                '<tr><td>' +
                                                _data[v].title + '<td></td>' +
                                                (fFilter == 'e' ?
                                                    ddt.toLocaleTimeString().substr(0, 5) :
                                                    _data[v].amount) +
                                                '</td></tr>').
                                            closest('td');//.
                                            //addClass('hasEvents');
                                    }
                                });
                            }
                        });
                    }
                    $('#ev_tabl tbody').html(eventsList);
                    $('#per_tabl tbody').html(periodicList);
                    $('#per_tabl tr, #ev_tabl tr').dblclick(function(){
                        $(this).find('li.edit a').click();
                    });
                    $('#per_tabl tr td.reject, #ev_tabl tr td.reject').click(function(){
                        $.post('/calendar/reminderAccept',
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
            },'json');
        },
        eventRender: function(calEvent, element){
            var event = _data[calEvent.key];
            var template = 'Повторяется ';
            // повторять пять раз
            if (event.repeat > 365){
                var dt = new Date((event.repeat)*1000);
                template += 'до ' + $.datepicker.formatDate('dd.mm.yy', dt);
            }else if (event.repeat == '0'){
                template += 'бесконечно';
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
            var tipContent = '<div style="text-align:left"><div><b>'+ (event.title || 'Без названия') +'</b></div>' +
                '<div>'+$.datepicker.formatDate('dd.mm.yy',ddt)+' ' +
                (event.type == 'e' ?
                    ddt.toLocaleTimeString().substr(0, 5) :
                    ('</div><div style="font-weight:bolder;color:'+
                        (event.op_type > 0 ?
                            'green">' :
                            'red"> -') + (event.amount ? formatCurrency(Math.abs(event.amount)) : '0.00') +
                        ' ' + res.currency[res.accounts[event.account].currency].text)) + '</div>' + //@todo FIX
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