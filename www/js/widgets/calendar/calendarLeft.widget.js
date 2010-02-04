    

easyFinance.widgets.calendarLeft = function(data){
    var _model;
    var _data = data || res.calendar.reminder;//@todo
    function init(model){
        $('#popupcalendar').dialog({
                bgiframe: true,
                autoOpen: false,
                width: 470,
                height: 'auto',
                buttons: {
                    'Подтвердить': function() {
                        var ch = $('#events_periodic tbody .chk input:checked, #events_calendar tbody .chk input:checked');
                        if ($(ch).length > 0 && confirm('Подтвердить операции с отмеченными элементами?')) {
                            var obj = new Array ();
                            $(ch).each(function(){
                                obj.push($(this).closest('tr').attr('value'));
                            });
                            $.post('/calendar/reminderAccept',{ids : obj.toString()},function(data){_data = data;showEvents();},'json');
                        }
                    },
                    'Удалить': function() {
                        var ch = $('#events_periodic tbody .chk input:checked, #events_calendar tbody .chk input:checked');
                        if ($(ch).length > 0 && confirm('Удалить выбранные события')) {
                            var obj = new Array ();
                            $(ch).each(function(){
                                obj.push($(this).closest('tr').attr('value'));
                            });
                            $.post('/calendar/reminderDel',{ids : obj.toString()},function(data){_data = data;showEvents();},'json');
                        }
                        //$(this).dialog('close');
                    }
                    
                },
                close : function(){
                    //$('#popupcalendar').hide();
                    $.jGrowl('В текущей сессии окно с событиями не будет показываться', {theme: ''});
                    $.cookie('events_hide', 1, {path: '/'});

                }
        });

        
        if (_data)
            showEvents();
            
        $('#popupcalendar th.chk input').change(function(){
            var check = this.checked;
            $(this).closest('div').find('.chk input').each(function(){
                this.checked = check;
            });
        });

        $('#btnAccept').click(function(){
            var ch = $('#events_periodic tbody .chk input:checked, #events_calendar tbody .chk input:checked');
            if ($(ch).length > 0 && confirm('Подтвердить операции с отмеченными элементами?')) {
                var obj = new Array ();
                $(ch).each(function(){
                    obj.push($(this).closest('tr').attr('value'));
                });
                $.post('calendar/reminderAccept',{ids : obj.toString()},function(){},'json')

            }
        });

        $('#AshowEvents').live('click',function(){
            $.cookie('events_hide', 0, {path: '/'});
            window.location.hash = null;
            showEvents();
            return false;
        });
    }
    /**
     * Выводит окошко пользователя для управления событиями
     */
    function showEvents(){
        //var now = new Date();
        var eventList = '', periodicList = '';
        var eventLeft = '', periodicLeft = '';
        //var category = easyFinance.models.category;
            //category.load(res.category)
        var accounts = easyFinance.models.accounts;
            accounts.load(res.accounts);
        for (var key in _data) {
            var event = _data[key];
            var date = new Date(event.date*1000);
            var diff = Math.floor(((new Date()).getTime() - event.date*1000)/(24*60*60*1000));
            date = $.datepicker.formatDate('dd.mm.yy',date);
            //diff = $.datepicker.formatDate('dd.mm.yy',diff);
                if (event.type == 'p') {
                       // var cat_name = category.getUserCategoryNameById(event.cat)||''
                        var account_name = accounts.getAccountNameById(event.account)||' ';
                        
                        periodicList += '<tr value="'+event.id+'"><td class="chk col c1"><input type="checkbox" /></td>'+
                                    '<td class="col">'+date+'</td>'+
                                    '<td class="money col">'+formatCurrency(event.amount)+'</td>'+
                                    '<td class="col">'+account_name+'</td>'+
                                    '<td class="col">'+event.comment+'</td>'+
                                    //+'<td>'+diff+'</td>'
                                    //+'<td>'+cat_name+'</td>'
                                    '</tr>';

                        periodicLeft += '<li id="'+event.id+'">'+
                                    '<a href="/periodic/">'+event.comment+'</a>'+
                                    '<b>'+ event.amount+'</b>'+
                                    '<span class="date">'+date+'</span></li>';
                }else {
                        
                        eventList += '<tr value="'+event['id']+'"><td class="chk col c1"><input type="checkbox" /></td>'+
                                    '<td class="col">'+event['title']+'</td>'+
                                    '<td class="col">'+diff+' дней</td>'+
                                    '<td class="col">'+date+'</td>'+
                                    '<td class="col">'+event['comment']+'</td>'+
                                     '</tr>';

                        eventLeft += '<li id="'+event['id']+'">'+
                                    '<a href="/calendar/">'+event['title']+'</a>'+
                                    '<span class="date">'+date+'</span></li>';
                    
                }
            }
            
            if (eventList == ''){
                $('#popupcalendar .eventcontent').hide();
            }else{
                $('#popupcalendar .eventcontent').show();
                $('#events_calendar tbody').html(eventList);
            }

            if (periodicList == ''){
                $('#popupcalendar .periodiccontent').hide();
            }else{
                $('#popupcalendar .periodiccontent').show();
                $('#events_periodic tbody').html(periodicList);
            }

            if (eventList == '' && periodicList == ''){
                $.cookie('events_hide', 1, {path: '/'});
                $('#popupcalendar').dialog('close');
            }
            
            if (eventLeft != '') {
                eventLeft = '<h2>События календаря</h2><ul>' + eventLeft + '</ul>';
            }
            if (periodicLeft != '') {
                periodicLeft = '<h2>Регулярные операции</h2><ul>' + periodicLeft + '</ul>';
            }
            var left = eventLeft + periodicLeft;
            if (left != '') {
                $('.transaction').html(left+'&nbsp;<a href="#p_index" id="AshowEvents">Показать события</a>');
            }else{
                $('.transaction').html('');
            }

            


            
            if ($.cookie('events_hide') != 1) {
                $('#popupcalendar').dialog('open');
                $('#popupcalendar').show();
            }
        
    }

    return {init: init};

};