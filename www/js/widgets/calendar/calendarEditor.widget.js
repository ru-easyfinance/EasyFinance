/**
 *
 */
easyFinance.widgets.calendarEditor = function(){

    var _useFilter;
    var _categories;
    var func;

    /**
     * Переключает видимость категорий
     * @param type 1 - доход, -1 - расход
     */
    function _toggleCategory(type) {
        $('#cal_category option').hide();
        $('#cal_category option[iswaste="0"]').show();
        $('#cal_category option[iswaste="'+type+'"]').show();
        $('#cal_category option:visible').filter(':first').attr('selected','selected');
    }

    /**
     * Фильтрует какие поля формы показывать
     */
    function _filter(){
        if (_useFilter){
            $('#op_dialog_event div.line').hide();
            $('#op_dialog_event div.line.'+$('#cal_mainselect li.act').attr('id')).show();
            $('#cal_repeat').change()
        }
    }

    /**
     * Устанавливает поля формы
     * @param el obj объект с полями)
     * @param type тип события
     */
    function _setupValues(el,type){
        _clear()
        var dt = new Date();
        dt.setTime(el.date*1000);

        $('#cal_mainselect li').removeClass('act');
        $('#cal_mainselect #'+type).addClass('act')

        $('#cal_key').val(el.id);
        $('#cal_chain').val(el.chain);
        $('#cal_title').val(el.title);
        $('#cal_date').val($.datepicker.formatDate('dd.mm.yy', dt));
        
        if (el.type == 'p'){
            $('#cal_amount').val(el.amount.toString());
            $('#cal_category').val(el.cat)
            $('#cal_type').val(el.op_type)
            $('#cal_account').val(el.account.toString());
        }else{
            $('#cal_title').val(el.title.toString());
        }
        $('#cal_comment').val(el.comment);
        $('#cal_time').val(dt.toLocaleTimeString().substr(0, 5));
        //Повторения
        $('#cal_repeat').val(el.every);
        if (el.repeat == 0){//infinity
            $('.rep_type[rep="2"]').attr('checked','checked');
        }else if(el.repeat <= 365){//разы
            $('.rep_type[rep="1"]').attr('checked','checked');
            $('#cal_count').val(el.repeat);
        }else if(el.repeat > 365){//дата
            dt.setTime(el.repeat*1000);
            $('#cal_date_end').val($.datepicker.formatDate('dd.mm.yy', dt));
        }
        if (el.every > 0){
            $('.repeat').closest('.line').show();
            $('#cal_repeat').change();
            if ($('#cal_repeat').val()=="7"){
                var i = 0
                $('#week.week input').each(function(){
                    if (el.week.toString().substr(i, 1) == '1'){
                        $(this).attr('checked', 'checked')
                    }
                    i++
                });
                $('#week.week').closest('.line').show();
            }
        }

    }

    /**
     * очищает форму
     */
    function _clear(){
        $('input[type="text"],select,textarea','#op_dialog_event').val('');
        $('#op_dialog_event #cal_repeat').val(0);
        $('#op_dialog_event .special #cal_use_mode_1').attr('checked','checked');
        $('#week.week input').removeAttr('checked');
    }
    /**
     *
     */
    function _printCategories(){
        var text = '';
        // пробегаем по родительским категориям
        for (var keyParent in _categories) {
            // выводим название категории
            text = text + '<option value="' + keyParent + '" iswaste="' + _categories[keyParent].type + '" >' + _categories[keyParent].name + '</option>';

            // выводим дочерние категории
            for (var keyChild in _categories[keyParent].children) {
                text = text + '<option value="' + keyChild + '" iswaste="' + _categories[keyParent].children[keyChild].type + '">&mdash; ' + _categories[keyParent].children[keyChild].name + '</option>';
            }
        }
        $('#cal_category').html(text);
    }

    /**
     * Инициация модели
     * @param model ссылка на модель
     */
    function init(){
        
        $('input#cal_date, input#cal_date_end').datepicker();
        $('#cal_time').timePicker().mask('99:99');

        $('#cal_mainselect li').click(function(){
            $('#cal_mainselect li').removeClass('act');
            $(this).addClass('act')
            _filter()
        });

        $('#cal_type option').click(function(){
            
            _toggleCategory( $('#cal_type').val())
        });

        $('#cal_repeat').change(function(){
            if ($('#cal_repeat').val()=="7"){ // Неделя
                $('#week.week').closest('.line').show();
                $('.repeat').closest('.line').show()
            }else if($('#cal_repeat').val()=="0"){ // Не повторять
                $('#week.week').closest('.line').hide();
                $('.repeat').closest('.line').hide()
            }else{ // Иначе
                $('#week.week').closest('.line').hide();
                $('.repeat').closest('.line').show()
            }
        });

        $('.repeat .rep_type').click(function(){
            $('#cal_count,#cal_infinity,#cal_date_end').attr('disabled','disabled');
            $('.repeat .rep_type:checked').closest('div').find('input,select').removeAttr('disabled');
            $('#cal_date_end').datepicker();
        })

        $('#cal_amount').keyup(function(e){
            FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
        });
    }
    
    /**
     * Загружает форму
     * @param data obj
     */
    function load(data){
        _categories = easyFinance.models.category.getUserCategoriesTree()
        _printCategories();

        var accounts = easyFinance.models.accounts.getAccounts();
        var accStr = ''
        for (var key in accounts){
            accStr+= '<option value="'+accounts[key].id+'">'+accounts[key].name+'</option>'
        }
        $('#cal_account').html(accStr);
        if(typeof data == 'object'){
            func = 'edit/'
            _setupValues(data.el, data.type)
            _useFilter = 1;
            _filter()
            _useFilter = 0;
            $('#op_dialog_event div.line.special').show()
            $('#cal_mainselect').closest('.line').hide()
            $('#op_dialog_event').dialog({
                bgiframe: true,
                autoOpen: false,
                width: 470,
                height: 'auto',
                buttons: {
                    
                    'Сохранить': function() {
                        save();
                        $(this).dialog('close');
                    },
                    'Отмена': function() {
                        $(this).dialog('close');
                    },
                    'Удалить': function() {
                        del({id: $('#op_dialog_event #cal_key').attr('value'),chain: $('#cal_chain').val()});
                        $(this).dialog('close');
                    }
                },
                close : function(){
                    _clear();
                    $('#op_dialog_event').dialog('destroy');
                }
            });
        }else{
            func = 'add/'
            $('#cal_mainselect').closest('.line').show()
            _useFilter = 1;
            _filter();
            $('#op_dialog_event').dialog({
                bgiframe: true,
                autoOpen: false,
                width: 470,
                buttons: {
                    'Сохранить': function() {
                        save();
                        $(this).dialog('close');
                    },
                    'Отмена': function() {
                        $(this).dialog('close');
                    }
                },
                close : function(){
                    _clear();
                    $('#op_dialog_event').dialog('destroy');
                }
            });
        }
        $('#cal_repeat').change();
        $('#op_dialog_event').dialog('open');
        $('#cal_date_end').datepicker();
    }
    
    /**
     * сохранят данные
     */
    function save(){
        var type = '';
        var every = $('#op_dialog_event #cal_repeat option:selected').attr('value')
        var repeat = '',week = '';

        if ($('#cal_mainselect .act').attr('id')=='periodic') {
            type = 'p';
        } else {
            type = 'e';
        }
        
        switch($('.rep_type:checked').attr('rep')){
            case '2':
                repeat = 0;
                break;
            case '1':
                repeat = $('#op_dialog_event #cal_count').attr('value');
                break;
            default:
                repeat = $('#op_dialog_event #cal_date_end').attr('value');
                break;
        }


        
        if(every == '7'){
            week = $('.week #cal_mon:checked').length.toString() +
                $('.week #cal_tue:checked').length.toString() +
                $('.week #cal_wed:checked').length.toString() +
                $('.week #cal_thu:checked').length.toString() +
                $('.week #cal_fri:checked').length.toString() +
                $('.week #cal_sat:checked').length.toString() +
                $('.week #cal_sun:checked').length.toString();
        }
        var ret = {
            
            id:         $('#op_dialog_event #cal_key').attr('value')||0,
            chain:      $('#cal_chain').val(),
            type:       type,
            title:      $('#op_dialog_event #cal_title').attr('value'),
            date:       $('#op_dialog_event #cal_date').attr('value'),
            every:      every,
            week:       week,
            repeat:     repeat,
            //special for event
            time:       $('#op_dialog_event #cal_time').attr('value'),

            //for periodic
            comment:    $('#op_dialog_event #cal_comment').attr('value'),
            amount:     $('#op_dialog_event #cal_amount').val().replace(/[^0-9\.\-]/,''),
            cat:   $('#op_dialog_event #cal_category').val(),
            op_type:       $('#op_dialog_event #cal_type').val(),
            account:    $('#op_dialog_event #cal_account').val(),

            use_mode: $('#op_dialog_event .special input:checked').attr('value')
            
        };
        $.post('/calendar/'+func,ret,function(data){
            calendarLeft.init(easyFinance.models.calendar())
            if(window.location.pathname.indexOf('calendar') != -1){
                $('#calendar').fullCalendar('refresh');
            }else{
                var s = new Date();
                s.setDate(1);
                var e = new Date(s.getFullYear(), s.getMonth()+1, 1);
                $.getJSON('/calendar/events/', {
                        start: s.getTime(),
                        end:   e.getTime()
                    },
                    function(result) {
                        easyFinance.widgets.calendarRight(result);
                    },
                    'json');
            }
        },'json');
    }

    /**
     * удаляет событие
     */
    function del(ret){
        ret.use_mode= $('#op_dialog_event .special input:checked').attr('value');
        $.post('/calendar/del/',ret,
        function(data){
            $.get('/calendar/reminder/',{},function(data){calendarLeft.init(data)},'json')
            if(window.location.pathname.indexOf('calendar') != -1){
                $('#calendar').fullCalendar('refresh');
            }
        },'json');
    }

    return {init: init, load: load, save: save, del: del}
}
