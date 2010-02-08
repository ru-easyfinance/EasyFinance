/**
 *
 */
easyFinance.widgets.calendarEditor = function(){

    var _useFilter;
    var _categories, _sexy = false;
    var func;

    /**
     * Переключает видимость категорий
     * @param type 1 - доход, -1 - расход
     */
    function _toggleCategory(type) {
//        $('#cal_category option').hide();
//        $('#cal_category option[iswaste="0"]').show();
//        $('#cal_category option[iswaste="'+type+'"]').show();
//        $('#cal_category option:visible').filter(':first').attr('selected','selected');
        if (_sexy){
            var txt = '';
            for (var key in catArr){
                if (catArr[key].iswaste == '0' || catArr[key].iswaste == type){
                    txt += '<option value="'+catArr[key].value+'">'+catArr[key].text+'</option>';
                }
            }
            $('select#cal_category').html(txt);
            $.sexyCombo.changeOptions("select#cal_category");
        }
    }

    /**
     * Фильтрует какие поля формы показывать
     */
    function _filter(){
        if (_useFilter){
            $('#op_dialog_event div.line').hide();
            $('#op_dialog_event div.line.'+$('#cal_mainselect li.act').attr('id')).show();
            $('#cal_repeat').change();
        }
    }

    /**
     * Устанавливает поля формы
     * @param el obj объект с полями)
     * @param type тип события
     */
    function _setupValues(el,type){
        _clear();
        var dt = new Date();
        dt.setTime(el.date*1000);

        $('#cal_mainselect li').removeClass('act');
        $('#cal_mainselect #'+type).addClass('act');

        $('#cal_key').val(el.id);
        $('#cal_chain').val(el.chain);
        $('#cal_title').val(el.title);
        $('#cal_date').val($.datepicker.formatDate('dd.mm.yy', dt));
        
        if (el.type == 'p'){
            $('#cal_amount').val(el.amount.toString());
            if (_sexy){
                _category.setComboValue((res.category.user[el.cat] ? res.category.user[el.cat].name : ''), false, false);
                _type.setComboValue((el.op_type == 1 ? 'Доход':'Расход'), false, false);
                _account.setComboValue((res.accounts[el.account.toString()] ? res.accounts[el.account.toString()].name : ''), false, false);
            }
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
                var i = 0;
                $('#week.week input').each(function(){
                    if (el.week.toString().substr(i, 1) == '1'){
                        $(this).attr('checked', 'checked');
                    }
                    i++;
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
        $('#op_dialog_event .special #cal_use_mode_3').attr('checked','checked');
        $('input#cal_count').val('1');
        $('#week.week input').removeAttr('checked');
    }
    /**
     *
     */
    function _printCategories(){
        var text = [];
        // пробегаем по родительским категориям
        for (var keyParent in _categories) {
            // выводим название категории
            text.push({value : keyParent, text : _categories[keyParent].name, iswaste : _categories[keyParent].type});

            // выводим дочерние категории
            for (var keyChild in _categories[keyParent].children) {
                text.push({value : keyChild, text : '&mdash; ' + _categories[keyParent].children[keyChild].name, iswaste : _categories[keyParent].children[keyChild].type});
            }
        }
        return text;
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
            $(this).addClass('act');
            _filter();
        });

        $('#cal_type option').click(function(){
            
            _toggleCategory( $('#cal_type').val());
        });

        $('#cal_repeat').change(function(){
            if ($('#cal_repeat').val()=="7"){ // Неделя
                $('#week.week').closest('.line').show();
                $('.repeat').closest('.line').show();
            }else if($('#cal_repeat').val()=="0"){ // Не повторять
                $('#week.week').closest('.line').hide();
                $('.repeat').closest('.line').hide();
            }else{ // Иначе
                $('#week.week').closest('.line').hide();
                $('.repeat').closest('.line').show();
            }
        });

        $('.repeat .rep_type').click(function(){
            $('#cal_count,#cal_infinity,#cal_date_end').attr('disabled','disabled');
            $('.repeat .rep_type:checked').closest('div').find('input,select').removeAttr('disabled');
            $('#cal_date_end').datepicker();
        });

//        $('#cal_amount').keyup(function(e){
//            FloatFormat(this,String.fromCharCode(e.which) + $(this).val());
//        });
    }
    
    /**
     * Загружает форму
     * @param data obj
     */
    var catArr, _type, _account, _category;
    var accArr;
    function load(data){
        _categories = easyFinance.models.category.getUserCategoriesTree();
        catArr = _printCategories();

        var accounts = easyFinance.models.accounts.getAccounts();
        accArr = [];
        for (var key in accounts){
            accArr.push({value : accounts[key].id, text : accounts[key].name});
        }
        if(typeof data == 'object'){
            func = 'edit/';
            _setupValues(data.el, data.type);
            _useFilter = 1;
            _filter();
            _useFilter = 0;
            $('#op_dialog_event div.line.special').show();
            $('#cal_mainselect').closest('.line').hide();
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
            
            $('span#ui-dialog-title-op_dialog_event').html('<h3>Редактирование события</h3>');
        }else{
            func = 'add/';
            $('#cal_mainselect').closest('.line').show();
            _useFilter = 1;
            _filter();
            $('#op_dialog_event').dialog({
                bgiframe: true,
                autoOpen: false,
                width: 470,
                buttons: {
                    'Сохранить': function() {
                        if (save()){
                            $(this).dialog('close');
                        }
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
            $('select#cal_repeat').removeAttr('disabled');
            $('span#ui-dialog-title-op_dialog_event').html('<h3>Добавление события</h3>');
        }
        $('#cal_repeat').change();
        $('#op_dialog_event').dialog('open');
        $('#cal_title').focus();
        if(!_sexy){
            $('#op_dialog_event div.line').show();
            _sexy = true;
            _type = $.sexyCombo.create({
                id : "cal_type",
                name: "type",
                container: "#cal_type",
                dropUp: false,
    //            filterFn: _sexyFilter,
                data: [{value: "-1", text: "Расход"},{value: "1", text: "Доход"}],
                changeCallback: function() {
                    _toggleCategory(this.getHiddenValue());
                }
            });
            _account = $.sexyCombo.create({
                id : "cal_account",
                name: "account",
                container: "#cal_account",
                dropUp: false,
    //            filterFn: _sexyFilter,
                data: accArr,
                changeCallback: function() {}
            });
            _category = $.sexyCombo.create({
                id : "cal_category",
                name: "category",
                container: "#cal_category",
                dropUp: false,
    //            filterFn: _sexyFilter,
                data: catArr,
                changeCallback: function() {

                }
            });
           
            _filter();
            
        }
        if(typeof(data) == 'object'){
            _setupValues(data.el, data.type);
            _useFilter = 1;
            _filter();
            _useFilter = 0;
            $('#op_dialog_event div.line.special').show();
            
            $('#cal_mainselect').closest('.line').hide();
            $('#cal_repeat').change();
            $('select#cal_repeat').attr('disabled', 'disabled');
        }
        $('#cal_date_end').datepicker();
    }
    
    /**
     * сохранят данные
     */
    function save(){
        var type = '';
        var every = $('#op_dialog_event #cal_repeat option:selected').attr('value');
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
        if (typeof($('#op_dialog_event #cal_title').attr('value')) != 'string' || $('#op_dialog_event #cal_title').attr('value').toString() == ''){
            $.jGrowl('Не заполнено название операции!',{theme : 'red'});
            return false;
        }
        if (!$('#op_dialog_event #cal_date').attr('value') || !/[0-3][0-9]\.[0-1][0-9]\.[0-9]{4}/.test($('#op_dialog_event #cal_date').attr('value'))){
            $.jGrowl('Не заполнена дата!',{theme : 'red'});
            return false;
        }
        var ret = {
            
            id:         $('#op_dialog_event #cal_key').attr('value')||0,
            chain:      $('#cal_chain').val()||0,
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
            cat:   $('#op_dialog_event select#cal_category').val(),
            op_type:       $('#op_dialog_event select#cal_type').val(),
            account:    $('#op_dialog_event select#cal_account').val(),

            use_mode: $('#op_dialog_event .special input:checked').attr('value')
            
        };
        $.jGrowl('Событие сохраняется!',{theme : 'green'});
        $.post('/calendar/'+func,ret,function(data){
            $.jGrowl('Событие успешно сохранено!',{theme : 'green'});
            calendarLeft.init();
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
        return true;
    }

    /**
     * удаляет событие
     */
    function del(ret){
        ret.use_mode= $('#op_dialog_event .special input:checked').attr('value');
        $.post('/calendar/del/',ret,
        function(data){
            $.get('/calendar/reminder/',{},function(data){calendarLeft.init(data);},'json');
            if(window.location.pathname.indexOf('calendar') != -1){
                $('#calendar').fullCalendar('refresh');
            }
        },'json');
    }

    return {init: init, load: load, save: save, del: del};
};
