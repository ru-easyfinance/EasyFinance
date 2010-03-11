/**
 * @desc Add/Edit Operation Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.operationEdit = function(){
    // private constants

    // private variables
    var _modelAccounts = null;
    var _modelCategory = null;

    var _$node = null;
    var _$dialog = null;

    var _isEditing = false;
    var _isCalendar = false;
    var _isChain = false;

    var _accOptionsData = null;

    var _oldSum = 0; // нужно для редактирования

    var _selectedAccount = '';
    var _selectedType = '0';
    var _selectedCategory = '-1';
    var _selectedTransfer = '';
    var _selectedTarget = '';

    var _sexyAccount = null;
    var _sexyType = null;
    var _sexyCategory = null;
    var _sexyTransfer = null;
    var _sexyTarget = null;

    // для мультивалютных переводов
    var _defaultCurrency = null;
    var _accountCurrency = null;
    var _transferCurrency = null;
    var _realConversionRate = 0;

    var _$noFocus = null;

    var _$blockCalendar = null;
    var _$blockWeekdays = null;
    var _$blockRepeating = null;

    // private functions

    function _initTags() {
        $('a#infoicon1').click(function(){
            $('#op_infobut1').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('#op_infobut1').show();
        });

        $('a#infoicon2').click(function(){
            $('#op_infobut2').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('#op_infobut2').show();
        });
        
        $('#op_infobut1').hide();
        $('#op_infobut2').hide();

        $('a#op_tags').click(function(){
            $('.op_tags_could').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('.op_tags_could li').show();
        });

        $('.op_tags_could li').live('click',function(){
            var txt=$('.op_tags input').val()+$(this).text()+', ';
            $('.op_tags input').val(txt);
            $('.op_tags_could').dialog("close");
        });

        // Загружаем теги
        var k,n;
        var data = res.cloud;
        var str = '<ul>';
        var m = -1;
        for (var key in data) {
            if (m == -1) m = data[key]['cnt'];
            k = data[key]['cnt']/m;
            n = Math.floor(k*5);
            str = str + '<li class="tag'+n+'"><a>'+data[key]['name']+'</a></li>';
        }

        $('.op_tags_could').html(str+'</ul>');
        $('.op_tags_could li').hide();
    }

    function _sexyFilter (input, text){
        if (this.wrapper.data("sc:lastEvent") == "click")
            return true;

        if (text.toLowerCase().indexOf(input.toLowerCase()) != -1)
            return true;
        else
            return false;
    }

    function _initSexyCombos() {
        // составляем список счетов
        var accounts = _modelAccounts.getAccounts();
        var accountsCount = 0;
        var key;

        // считаем количество всех счетов
        for (key in accounts) {
			accountsCount++;
        }
		
        var recentCount = 0;
        var recent = res.accountsRecent;
        // считаем количество часто используемых счетов
        for (key in recent) {
            recentCount++;
        }

        _accOptionsData = [];
        if (recentCount >= accountsCount || recentCount == 0) {
            // если счетов мало (не больше частых счетов),
            // выводим все счета по алфавиту
            for (key in accounts) {
                _accOptionsData.push({value: accounts[key].id, text: accounts[key].name + ' (' + _modelAccounts.getAccountCurrencyText(key) + ')'});
            }
        } else {
            // если счетов много, сначала выводим часто используемые счета
            for (key in recent) {
                _accOptionsData.push({value: accounts[key].id, text: accounts[key].name + ' (' + _modelAccounts.getAccountCurrencyText(key) + ')'});
                delete accounts[key];
            }

            _accOptionsData.push({value: "", text: "&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;"});

            // затем выводим все остальные счета в алфавитном порядке
            for (key in accounts) {
                _accOptionsData.push({value: accounts[key].id, text: accounts[key].name + ' (' + _modelAccounts.getAccountCurrencyText(key) + ')'});
            }
        }
        
        // #870. Запомним заранее выбранный аккаунт, если он был задан
        // (после инициализации это значение сбрасывается)
        var _preAccount = _selectedAccount;
        _sexyAccount = $.sexyCombo.create({
            id : "op_account",
            name: "op_account",
            container: "#div_op_account",
            dropUp: false,
            filterFn: _sexyFilter,
            data: _accOptionsData,
            changeCallback: function() {
                _selectedAccount = this.getHiddenValue();

                _changeAccountForTransfer();

                // reload operation journal
                if (easyFinance.widgets.operationsJournal)
                    easyFinance.widgets.operationsJournal.setAccount(_selectedAccount);
                $('#btn_ReloadData').click();
            }
        });
        if (_preAccount)
            setAccount (_preAccount);

        _sexyCategory = $.sexyCombo.create({
            id : "op_category",
            name: "op_category",
            container: "#div_op_category",
            dropUp: false,
            filterFn: _sexyFilter,
            data: [{value: "0", text: "-"}],
            changeCallback: function() {
                _selectedCategory = this.getHiddenValue();
            }
        });

        // заполняем категории в соответствии с типом операции
        _changeOperationType();
        setCategory("-1");

        var typeOptionsData = [
            {value: "0", text: "Расход", selected: true},
            {value: "1", text: "Доход"},
            {value: "2", text: "Перевод со счёта"},
            {value: "4", text: "Перевод на фин. цель"}
        ];

        // если есть фин. цели
        //if (res.user_targets)
        //    typeOptionsData.push ( {value: "4", text: "Перевод на фин. цель"} );

        _sexyType = $.sexyCombo.create({
            id : "op_type",
            name: "op_type",
            container: "#div_op_type",
            dropUp: false,
            filterFn: _sexyFilter,
            data: typeOptionsData,
            changeCallback: function() {
                var old = _selectedType;
                _selectedType = this.getHiddenValue();

                if (old != _selectedType)
                    _changeOperationType();
            }
        });

        // Tab & Shift+Tab для секси комбо
        var next = {op_type : "#op_category", op_account : "#op_type", op_category : '#op_date'};
        var prev = {op_type : "#op_account", op_account : "#op_category", op_category : '#op_type'};
    }

    function _initForm(){
        _$noFocus = $("#opCatchFocus");

        _$dialog = $('form.op_addoperation').dialog({
            dialogClass: 'dlgOperationEdit',
            autoOpen: false,
            title: 'Новая операция',
            width: 400,
            beforeclose: function() {
                // очищаем форму перед закрытием
                _clearForm();
            },
            buttons: {
                "Отмена": function() {
                    // закрываем диалог
                    $(this).dialog("close");
                },
                "Сохранить": function() {
                    _saveOperation();
                }
            }
        });

        // @todo: реализовать переход между комбо по tab'у
        $('div.combo.sexy input').keypress(function(e){
            if (e.keyCode == 9){
                var id = $(this).closest('div.combo.sexy').find('select').attr('id');
                (this).blur().closest('div.combo.sexy').find('ul:visible').closest('div.combo.sexy').find('div.icon').click();
                if (e.shiftKey){
                    // move backward
                    if (id != 'account'){
                        $(prev[id]).next('input:visible').focus().closest('div.combo.sexy').find('div.icon').click();
                    }else{
                        $(prev[id]).focus();
                    }
                }else{
                    // move forward
                    if (id != 'op_category'){
                        $(next[id]).next('input:visible').focus().closest('div.combo.sexy').find('div.icon').click();
                    }else{
                        $(next[id]).focus();
                    }
                }
            }
        });
        // ====

        // настраиваем переключение между 
        // обычным режимом и планированием
        $("#op_addoperation_but").click(function() {
            _isEditing = false;
            _isCalendar = false;
            _isChain = false;

            _$blockCalendar.hide();
            _expandNormal();
        });
        $("#op_addtocalendar_but").click(function() {
            _isEditing = false;
            _isCalendar = true;
            _isChain = true;
            _expandCalendar();

            // TEMP: не показываем операции на фин. цель
            var htmlOptions = '<option value="0">Расход</option><option value="1">Доход</option><option value="2">Перевод со счёта</option>';
            $("#op_type").html(htmlOptions);
            $.sexyCombo.changeOptions("#op_type");
            setType("0");
            // EOF TEMP
        });

        // кнопка расчёта суммы
        _$node.find('#btnCalcSum').click(function(){
            var calculator = $('#op_amount');
            $(calculator).val(calculate($(calculator).val()));
        });

        // кнопка расчёта суммы для поля перевода
        _$node.find('#btnCalcSumTransfer').click(function(){
            var calculator = $('#op_transfer');
            $(calculator).val(calculate($(calculator).val()));
        });

        // поле суммы со встроенным калькулятором
        $('#op_amount,#op_transfer').live('keypress',function(e){
            if (e.keyCode == 13){
                $(this).val(calculate($(this).val()));
            }
            if (!e.altKey && !e.shiftKey && !e.ctrlKey){

                var chars = '1234567890. +-*/';
                if (chars.indexOf(String.fromCharCode(e.which)) == -1){
                    var keyCode = e.keyCode;
                    if (keyCode != 13 && keyCode != 46 && keyCode !=8 && keyCode !=37 && keyCode != 39 && e.which != 32)
                        return false;
                }
                return true;
            }
        });
        
        $("#op_date").datepicker().datepicker('setDate', new Date());

        // обмен валют для мультивалютных переводов
        $('#op_conversion').change(function(){
            // рассчитываем реальный множитель конвертации в зависимости от валют счетов
            if (_accountCurrency.id == _defaultCurrency.id || _transferCurrency.id == _defaultCurrency.id) {
                // если перевод с использованием валюты по умолчанию
                if (_accountCurrency.id == _defaultCurrency.id) {
                    _realConversionRate = 1 / parseFloat($(this).val());
                } else {
                    _realConversionRate = parseFloat($(this).val());
                }
            } else {
                // если перевод без использования валюты по умолчанию
                _realConversionRate = 1 / parseFloat($(this).val());
            }

            var result = parseFloat(tofloat(calculate($('#op_amount').val()))) * _realConversionRate;
            result = result.toFixed(4);

            if (!isNaN(result) && result != 'Infinity') {
                $("#op_transfer").val(result);
            }
        });


        $('#op_amount').change(function(){
            var result = parseFloat(tofloat(calculate($('#op_amount').val()))) * _realConversionRate;
            result = Math.round(result*100)/100;

            if (!isNaN(result) && result != 'Infinity') {
                $("#op_transfer").val(result);
            }
        });

        $('#op_transfer').change(function(){
            var result = 0;
            if (_selectedType == "2") {
                if (_accountCurrency.id == _defaultCurrency.id || _transferCurrency.id == _defaultCurrency.id) {
                    // если перевод с использованием валюты по умолчанию
//                    if (_accountCurrency.id == _defaultCurrency.id) {
//                        _realConversionRate = 1 / parseFloat($(this).val());
//                    } else {
//                        _realConversionRate = parseFloat($(this).val());
//                    }
                } else {
                    // если перевод без использования валюты по умолчанию
                    result = parseFloat(tofloat(calculate($(this).val()))) * parseFloat(tofloat(calculate($("#op_amount").val())));
                    result = result.toFixed(4);
                }

                if (!isNaN(result) && result != 'Infinity') {
                    $("#op_conversion").val(result);
                }
            }
        });

        $('#op_account').change( function(){_changeAccountForTransfer();});
        $('#op_AccountForTransfer').change( function(){_changeAccountForTransfer();});

        _initBlockCalendar();
    }

    function _initBlockCalendar() {
        _$blockCalendar = _$node.find('#operationEdit_planning');
        
        _$blockCalendar.find('#cal_date_end').datepicker();
        
        _$blockWeekdays = _$blockCalendar.find('#operationEdit_weekdays');
        _$blockRepeating = _$blockCalendar.find('#operationEdit_repeating');

        // переключаемся между разными периодами повторений
        _$blockCalendar.find('#cal_repeat').change(function(){
            if ($(this).val()=="7") { // неделя
                _$blockWeekdays.show();
                _$blockRepeating.show();
            } else if ($(this).val()=="0") { // Не повторять
                _$blockRepeating.hide();
                _$blockRepeating.hide();
            } else { // день, месяц, год
                _$blockWeekdays.hide();
                _$blockRepeating.show();
            }
        });

        // переключаемся между режимами повторения
        // по количеству раз или до определённой даты
        _$blockCalendar.find('.rep_type').click(function(){
            _$blockCalendar.find('#cal_count,#cal_infinity,#cal_date_end').attr('disabled','disabled');
            _$blockCalendar.find('.rep_type:checked').closest('div').find('input,select').removeAttr('disabled');
        });
    }

    function _expandNormal() {
        if (!_isCalendar) {
            if (_isEditing)
                _$dialog.data('title.dialog', 'Редактирование операции').dialog('open');
            else
                _$dialog.data('title.dialog', 'Добавление операции').dialog('open');
        } else {
            if (_isEditing)
                if (_isChain)
                    _$dialog.data('title.dialog', 'Редактирование серии операций').dialog('open');
                else
                    _$dialog.data('title.dialog', 'Редактирование операции в календаре').dialog('open');
            else
                if (_isChain)
                    _$dialog.data('title.dialog', 'Добавление серии операций').dialog('open');
        }

        // если открываем в первый раз, инициализируем комбобоксы
        if (!_sexyAccount)
            _initSexyCombos();


        // TEMP: показываем операции перевода на фин. цель
        var htmlOptions = '<option value="0">Расход</option><option value="1">Доход</option><option value="2">Перевод со счёта</option><option value="4">Перевод на фин. цель</option>';
        $("#op_type").html(htmlOptions);
        $.sexyCombo.changeOptions("#op_type");
        setType("0");
        // EOF TEMP
    }

    function _expandCalendar() {
        _isCalendar = true;

        if (_isEditing)
            _$dialog.data('title.dialog', 'Редактирование операции в календаре').dialog('open');
        else
            _$dialog.data('title.dialog', 'Добавление серии операций').dialog('open');

        // если открываем в первый раз, инициализируем комбобоксы
        if (!_sexyAccount)
            _initSexyCombos();

        _$blockCalendar.show()

        $('#operationEdit_planning').show();
    }

    function _changeOperationType() {
        // Расход или Доход
        if (_selectedType == "0" || _selectedType == "1") {
            $("#op_category_fields,#op_tags_fields").show();
            $("#op_target_fields,#op_transfer_fields,#div_op_transfer_line").hide();

            // скрываем недоступные для выбора категории
            // для доходных операций скрываем расходные категории,
            // для расходных операций скрываем доходноые категории
            var typ = (_selectedType == "0") ? -1 : +1;

            // генерируем список категорий
            var htmlOptions = '';

            var catPrint = function (list, type) {
                var str = '';

                // пробегаем по родительским категориям
                for (var keyParent in list) {
                    // если категория выбранного типа или универсальная
                    if (list[keyParent].type == type || list[keyParent].type == '0') {
                        // выводим название категории
                        str = str + '<option value="' + keyParent + '">' + list[keyParent].name + '</option>';

                        // выводим дочерние категории
                        for (var keyChild in list[keyParent].children) {
                            str = str + '<option value="' + keyChild + '">&mdash; ' + list[keyParent].children[keyChild].name + '</option>';
                        }
                    }
                }

                return str;
            }

            // выводим частоиспользуемые категории
            var recent = _modelCategory.getRecentCategories();
            var recentFiltered = {};

            for (var key in recent) {
                if (parseInt(recent[key].type) == typ || recent[key].type == '0')
                    recentFiltered[key] = recent[key];
            }

            var list = {
                "-1": {
                    id: "-1",
                    type: "0",
                    name: "Часто используемые",
                    children: recentFiltered
                }
            };

            htmlOptions = htmlOptions + catPrint(list, typ);
            htmlOptions = htmlOptions + catPrint(_modelCategory.getUserCategoriesTree(), typ);

            // обновляем список категорий
            $("#op_category").html(htmlOptions);
            $.sexyCombo.changeOptions("#op_category");
            setCategory("-1");

        //Перевод со счёта
        } else if (_selectedType == "2") {
            $("#op_category_fields,#op_target_fields").hide();
            $("#op_tags_fields,#op_transfer_fields").show();

            if (!_sexyTransfer) {
                _sexyTransfer = $.sexyCombo.create({
                    id : "op_AccountForTransfer",
                    name: "op_AccountForTransfer",
                    container: "#div_op_transfer",
                    dropUp: false,
                    filterFn: _sexyFilter,
                    data: _accOptionsData,
                    changeCallback: function() {
                        _selectedTransfer = this.getHiddenValue();

                        _changeAccountForTransfer();
                    }
                });

                // выбираем первую опцию по умолчанию
                _sexyTransfer.setComboValue(_sexyTransfer.options[0].text);
            }
            
            _changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if (_selectedType == "4") {
            $("#op_target_fields").show();
            $("#op_tags_fields,#op_transfer_fields,#op_category_fields").hide();

            if (!_sexyTarget) {
                _sexyTarget = $.sexyCombo.create({
                    id : "op_target",
                    name: "op_target",
                    container: "#div_op_target",
                    dropUp: false,
                    filterFn: _sexyFilter,
                    data: [{value: "", text: "-"}],
                    changeCallback: function() {
                        _selectedTarget = this.getHiddenValue();

                        t = parseInt($("#op_target :selected").attr("target_account_id"));

                        var option = _sexyTarget.options.filter('[value="' + _selectedTarget + '"]').eq(0);
                        $("#op_amount_done").text(formatCurrency(option.attr("amount_done")));
                        $("#op_amount_target").text(formatCurrency(option.attr("amount")));
                        $("#op_percent_done").text(formatCurrency(option.attr("percent_done")));
                        $("#op_forecast_done").text(formatCurrency(option.attr("forecast_done")));
                    }
                });
            }

            // обновляем опции
            refreshTargets();
        }
    }

    function _saveOperation() {
        if (!_validateForm()){
            return false;
        }

        $('#op_btn_Save').attr('disabled', 'disabled');
        $.jGrowl("Операция сохраняется", {theme: 'green'});

        var tip = $('#op_type').val();
        var id = $('#op_id').val();

        if ($('#op_accepted').val() == '')
            if (_isCalendar)
                $('#op_accepted').val("0");
            else
                $('#op_accepted').val("1");

//alert($('#op_id').val());
//alert($('#op_type').val());
//alert($('#op_account').val());
//alert(_selectedType);
//alert(_selectedCategory);
//alert($('#op_AccountForTransfer').val());
        var account = $('#op_account').val();

        var amount1 = tofloat(calculate($('#op_amount').val())); // сумма операции
        var amount2 = $('#op_transfer').val(); // сумма к получению при обмене валют

        var chain = null;
        if (_isCalendar)
            if (_isEditing && _isChain)
                chain = $('#op_chain_id').val();
            else
                chain = '';
        var time = '12:00';
        var last = $('#cal_date_end').val();
        if (last == "00.00.0000")
            last = "";
        var every = $('#cal_repeat').val();
        var repeat = $('#cal_count').val();
        
        var week = '0000000';
        if(every == '7'){
            week = $('.week #cal_mon:checked').length.toString() +
                $('.week #cal_tue:checked').length.toString() +
                $('.week #cal_wed:checked').length.toString() +
                $('.week #cal_thu:checked').length.toString() +
                $('.week #cal_fri:checked').length.toString() +
                $('.week #cal_sat:checked').length.toString() +
                $('.week #cal_sun:checked').length.toString();
        }

        easyFinance.models.accounts.editOperationById(
            id,
            $('#op_accepted').val(),
            _selectedType,
            _selectedAccount,
            _selectedCategory,
            $('#op_date').val(),
            $('#op_comment').val(),
            amount1,
            _selectedTransfer,
            _realConversionRate,
            amount2, // сумма к получению при обмене валют
            _selectedTarget,
            //$('#op_close:checked').length,
            $('#op_close2').val(),
            $('#op_tags').val(),
            chain, time, last, every, repeat, week,

            function(data){
                // В случае успешного сохранения, закрываем диалог и обновляем календарь
                $('#op_btn_Save').removeAttr('disabled');
                _$dialog.dialog("close");
							
                if (data.result) {
                    refreshTargets();

                    $.jGrowl(data.result.text, {theme: 'green'});
                    if (!_isCalendar)
                        $.jGrowl("<a class='white' href='/operation/#account="+account+"'>Перейти к операциям</a>", {theme: 'green',life: 2500});
                    if (tip == 4)
                        MakeOperation();// @todo: заменить на отправку event'a!
                } else if (data.error) {
                    $.jGrowl(data.error.text, {theme: 'red', stick: true});
                }
            }
        );

        $(this).dialog("close");

        return true;
    }

    function _validateForm() {
        $error = '';

        var comment = $('#op_comment').val();
        if (comment.indexOf('<') != -1 || comment.indexOf('>') != -1) {
            $.jGrowl("Комментарий не должен содержать символов < и >!", {theme: 'red', life: 2500});
            return false;
        }

        var tags = $('#op_tags').val();
        if (tags.indexOf('<') != -1 || tags.indexOf('>') != -1) {
            $.jGrowl("Тэги не должны содержать символов < и >!", {theme: 'red', life: 2500});
            return false;
        }

        var date = $('#op_date').val();
        if (! date.match(/^\d\d?\.\d\d?\.\d\d\d\d$/)) {
            $.jGrowl('Вы ввели неверное значение в поле "дата"!', {theme: 'red', stick: true});
            return false;
        }

        var opType = $("#op_type option:selected").val();

        // при добавлении обычной операции
        // проверяем заполнение всех полей
        if (!_isCalendar) {
            if (opType == "0" || opType == "1") {
                // для доходов и расходов
                if (_selectedCategory == '' || _selectedCategory == '-1') {
                        $.jGrowl('Выберите категорию!', {theme: 'red', stick: true});
                        return false;
                }
            } else if (opType == "2") {
                if (_selectedTransfer == '') {
                    $.jGrowl('Укажите счёт для перевода!', {theme: 'red', stick: true});
                    return false;
                }
            } else if (opType == "4") {
                if (_selectedTarget == '' || _selectedTarget == '0') {
                    $.jGrowl('Укажите финансовую цель!', {theme: 'red', stick: true});
                    return false;
                }
            }

            if (_selectedAccount == ''){
                $.jGrowl('Вы ввели неверное значение в поле "счёт"!', {theme: 'red', stick: true});
                return false;
            }

            if (_selectedType == ''){
                $.jGrowl('Вы ввели неверное значение в поле "тип операции"!', {theme: 'red', stick: true});
                return false;
            }

            var sum = calculate($('#op_amount').val());

            if (sum < 1) {
                $.jGrowl('Сумма должна быть больше нуля!', {theme: 'red', stick: true});
                return false;   
            }

            if (!/[\-]?[0-9]+([\.][0-9]+)?/.test(sum)){
                $.jGrowl('Вы ввели неверное значение в поле "сумма"!', {theme: 'red', stick: true});
                return false;
            }

            if (_selectedType == '4') {
                /**
                 *@FIXME Написать обновление финцелей
                 */
                 //alert("tratata");

                var amount = parseFloat($("#op_target option:selected").attr("amount"));
                var amount_done = parseFloat($("#op_target option:selected").attr("amount_done"));
                $("#op_amount_done").text(formatCurrency($("#op_target :selected").attr("amount_done")));
                //alert('1');
                if ((amount_done + parseFloat($("#op_amount").val())) >= amount) {
                    if (confirm('Закрыть финансовую цель?')) {
                        $("#op_close").attr("checked","checked");
                        $("#op_close2").attr("value","1");
                    }
                }
            }
        }

        // проверки при редактировании операций и цепочек в календаре
        if (_isCalendar) {
            if ($('#cal_repeat').val() != "0") {
                if ($('#cal_rep_every').attr('checked')) {
                    var count = $('#cal_count').val();
                    if (isNaN(count) || parseInt(count) < 1) {
                        $.jGrowl('Укажите число повторений операции!', {theme: 'red', stick: true});
                        return false;
                    } else if (parseInt(count) > 499) {
                        $.jGrowl('Операция может повторяться не более 500 раз!', {theme: 'red', stick: true});
                        return false;
                    }
                } else {
                    var dateEnd = $('#cal_date_end').val();
                    if (dateEnd == "") {
                        $.jGrowl('Укажите дату окончания повторений!', {theme: 'red', stick: true});
                        return false;
                    } else {
                        var dateStart = $('#op_date').val();
                        var dS = $.datepicker.parseDate('dd.mm.yy', dateStart);
                        var dE = $.datepicker.parseDate('dd.mm.yy', dateEnd);

                        if (dS >= dE) {
                            $.jGrowl('Дата начала повторений должна быть меньше даты окончания повторений!', {theme: 'red', stick: true});
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    function _clearForm() {
        $("#op_btn_Save").removeAttr('disabled');

        $('#op_id,#op_accepted,#op_chain_id').val('');
        $('#op_amount,#op_conversion,#op_transfer,#op_AccountForTransfer,#op_comment,#op_tags').val('');
        $('span#op_amount_target').text();

        $('span#op_amount_done').text();
        $('span#op_forecast_done').text();
        $('span#op_percent_done').text();

        $('#op_close').removeAttr('checked');

        $('.week input').removeAttr('checked');

        // сбрасываем параметры повторения операции
        $('#cal_repeat').val("0");
        $('#operationEdit_repeating,#operationEdit_weekdays').hide();
        $('#cal_rep_every').attr('checked', 'checked');
        $('#cal_count').val("1");
        $('.week input').removeAttr('checked');
        $('#cal_date_end').val("");

        _$blockCalendar.hide();
    }

    function _changeAccountForTransfer() {
        // prevents datepicker from auto-popup
        _$noFocus.hide();

        _accountCurrency = _modelAccounts.getAccountCurrency(_selectedAccount);
        _transferCurrency = _modelAccounts.getAccountCurrency(_selectedTransfer);

        if (_selectedType == "2" && _selectedAccount != "" && _selectedTransfer != "" &&
            _accountCurrency.id != _transferCurrency.id) {
                $('#div_op_transfer_line').show();

                if (_accountCurrency.id == _defaultCurrency.id || _transferCurrency.id == _defaultCurrency.id) {
                    // обмен с участием валюты по умолчанию
                    var str = _defaultCurrency.text + ' за ';
                    str += (_accountCurrency.id == _defaultCurrency.id) ? _transferCurrency.name : _accountCurrency.name;
                    $('#op_conversion_text').text (str);

                    // определяем курс                    
                    data = (_accountCurrency.id == _defaultCurrency.id) ? _transferCurrency.cost : _accountCurrency.cost;
                    data /= _defaultCurrency.cost;
                    data = data.toString();
                    i = data.indexOf('.');
                    data = data.substr(0, i+5);

                    $('#op_conversion').val(data).change();
                } else {
                    // обмен без участия валюты по умолчанию
                    $('#op_conversion_text').text (_accountCurrency.name + ' за ' + _transferCurrency.name);

                    // определяем курс
                    data = _transferCurrency.cost;
                    data /= _accountCurrency.cost;
                    data = data.toString();
                    i = data.indexOf('.');
                    data = data.substr(0, i+5);
                    $('#op_conversion').val(data).change();
                }
        } else {
            $('#div_op_transfer_line').hide();
            $('#op_conversion').val('');
        }
    }

    function refreshAccounts() {
        if (!_sexyAccount)
            return;

        var data = $.extend({}, _modelAccounts.getAccounts());
        if (!data)
            data = {};

        var htmlAccounts = '';
        for (key in data ) {
            htmlAccounts = htmlAccounts + '<option value="' + key + '">'
                + data[key].name + ' (' + _modelAccounts.getAccountCurrencyText(key) + ')' + '</option>';
        }

        var curAccount = _selectedAccount;
        $("#op_account").html(htmlAccounts);
        $.sexyCombo.changeOptions("#op_account");
        if (_selectedAccount == '') {
            // выбираем первую опцию по умолчанию
            _sexyAccount.setComboValue(_sexyAccount.options[0].text);
        } else {
            setAccount(curAccount);
        }

        if (_sexyTransfer) {
            curAccount = _selectedTransfer;
            $("#op_AccountForTransfer").html(htmlAccounts);
            $.sexyCombo.changeOptions("#op_AccountForTransfer");

            if (_selectedTransfer == '') {
                // выбираем первую опцию по умолчанию
                _sexyTransfer.setComboValue(_sexyTransfer.options[0].text);
            } else {
                setTransfer(curAccount);
            }
        }
    }

    function refreshCategories() {
        if (_sexyCategory) {
            _changeOperationType();
            // выбираем первую опцию по умолчанию
            _sexyCategory.setComboValue(_sexyCategory.options[0].text);
        }
    }

    function refreshTargets() {
        if (!_sexyTarget)
            return;

        var data = res.user_targets;
        if (!data)
            data = {};

        var t;
        var o = '';
        for (var v in res['user_targets']) {
            t = res['user_targets'][v];
            if (t['done']=='0')
            o += '<option value="'+t['id']+'" target_account_id="'+t['account']+'" amount_done="'+t['amount_done']+
                '"percent_done="'+t['percent_done']+'" forecast_done="'+t['forecast_done']+'" amount="'+t['amount']+'">'+t['title']+'</option>';
        }

        $("#op_target").html(o);
        $.sexyCombo.changeOptions("#op_target");
        // выбираем первую опцию по умолчанию
        _sexyTarget.setComboValue(_sexyTarget.options[0].text);
    }

    // public variables

    // public functions
    /**
     * @desc init widget
     * @usage init(nodeSelector, accountsModel, categoryModel)
     */
    function init(nodeSelector, modelAccounts, modelCategory) {
        if (!modelAccounts || !modelCategory)
            return null;

        _$node = $(nodeSelector);

        _modelAccounts = modelAccounts;
        _modelCategory = modelCategory;

        _defaultCurrency = easyFinance.models.currency.getDefaultCurrency();

        // load tags cloud and setup tags dialog
        _initTags();

        // setup form
        _initForm();

        $(document).bind('accountsLoaded', refreshAccounts);
        $(document).bind('accountAdded', refreshAccounts);
        $(document).bind('accountDeleted', refreshAccounts);

        $(document).bind('categoriesLoaded', refreshCategories);
        //$(document).bind('categoryAdded', refreshCategories);
        $(document).bind('categoryEdited', refreshCategories);
        $(document).bind('categoryDeleted', refreshCategories);

        return this;
    }

    function setSum(sum){
        _oldSum = Math.abs(sum);
        $('#op_amount').val(_oldSum);
    }

    function _setSexyComboValue(combo, value) {
        if (!combo)
            return;

        var str = combo.options.filter('[value="' + value + '"]').eq(0).text();
        combo.setComboValue(str, false, false)
    }

    function setType(id){
        _setSexyComboValue(_sexyType, id);
    }

    function setAccount(id){
        if (_sexyAccount)
            _setSexyComboValue(_sexyAccount, id);
        else
            _selectedAccount = id;
    }

    function setCategory(id){
        _setSexyComboValue(_sexyCategory, id);
    }

    function setTransfer(id){
        _setSexyComboValue(_sexyTransfer, id);
    }

    function setTarget(id){
        _setSexyComboValue(_sexyTarget, id);
    }

    function showForm() {
        _$dialog.dialog("open");

        if (!_sexyAccount)
            _initSexyCombos();
    }

    /**
     * Функция заполняет форму данными
     * @param data: данные для заполнения
     * @param isEditing: true если редактируем операцию
     */
    function fillForm(data, isEditing) {
        _isEditing = isEditing;

        _expandNormal();

        if (!_sexyAccount)
            _initSexyCombos();

        if (isEditing && data.id) {
            $('#op_id').val(data.id);
        } else {
            $('#op_id').val('');
        }

        if (typeof data.accepted != 'undefined')
            $('#op_accepted').val(data.accepted);
        else
            $('#op_accepted').val("1");

        var typ = data.type;
        setType(typ);

        if (data.transfer != "" && data.tr_id != null) {
            if (data.tr_id == "0") {
                // from this account
                setAccount(data.account_id || data.account);
                
                // to this account
                setTransfer(data.transfer);
            } else {
                // original operation id
                $('#op_id').val(data.tr_id);
                
                // to this account
                setTransfer(data.account_id || data.account);

                // from this account
                setAccount(data.transfer);
            }
        } else {
            // to this account
            setAccount(data.account_id || data.account);

            // from this account
            setTransfer(data.transfer);
        }
        
        if (typ == "2" && data.curs) {
            // перевод с обменом валют
            setSum(Math.round(Math.abs(data.money)*100)/100);

            if (_accountCurrency.id == _defaultCurrency.id || _transferCurrency.id == _defaultCurrency.id) {
                // обмен с участием валюты по умолчанию, выводим в особом формате
                $('#op_conversion').val(Math.round((1 / parseFloat(data.curs))*1000)/1000).change();
            } else {
                $('#op_conversion').val(data.curs).change();
            }
        } else {
            if (isNaN(data.money)) {
                $("#op_amount").val("");
            } else {
                if (data.moneydef)
                    setSum(Math.abs(data.moneydef))
                else
                    setSum(Math.abs(data.money));
            }
        }

        setCategory(data.cat_id);

        setTarget(data.target_id);

        if (typeof data.date == "string") {
            $('#op_date').val(data.date);
        } else {
            var dp = $('#op_date');
            dp.datepicker('setDate', new Date());
            data.date = dp.val();
        }
        
        if (data.tags)
            $('#op_tags').val(data.tags);
        else
            $('#op_tags').val('');

        // первая операция по счёту показывает начальный баланс счёта
        // в этом случае нельзя редактировать сумму и комментарий
        if ($('#op_comment').val() == "Начальный остаток"){
            $('#op_amount').attr('disabled', 'disabled');
            $('#op_comment').attr('disabled', 'disabled');
        } else {
            $('#op_amount').removeAttr('disabled');
            $('#op_comment').removeAttr('disabled');
        }
    }

    function fillFormCalendar(data, isEditing, isChain) {
        _isCalendar = true;
        _isEditing = isEditing;
        _isChain = isChain;

        _expandCalendar();

        fillForm(data, isEditing);

        // TEMP: не показываем операции на фин. цель
        var htmlOptions = '<option value="0">Расход</option><option value="1">Доход</option><option value="2">Перевод со счёта</option>';
        $("#op_type").html(htmlOptions);
        $.sexyCombo.changeOptions("#op_type");
        setType(data.type);
        // EOF TEMP

        // заполняем атрибуты цепочки / события
        $('#op_chain_id').val(data.chain || '');

        if (typeof data.accepted != 'undefined')
            $('#op_accepted').val(data.accepted);
        else
            $('#op_accepted').val("0");

        $('#cal_date_end').val(data.last || '');
        $('#cal_count').val(data.repeat || "0");
        $('#cal_repeat').val(data.every || '0').change();

        if (data.every && data.every == "7"){
            var i = 0;
            $('.week input').each(function(){
                if (data.week.toString().substr(i, 1) == '1'){
                    $(this).attr('checked', 'checked');
                }
                i++;
            });
        }

        if (_isCalendar && isEditing && !isChain)
            $('#operationEdit_planning').hide();
        else
            $('#operationEdit_planning').show();
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        setCategory: setCategory,
        setSum: setSum,
        setAccount: setAccount,
        setTransfer: setTransfer,
        showForm: showForm,
        fillForm: fillForm,
        fillFormCalendar: fillFormCalendar,
        refreshAccounts: refreshAccounts,
        refreshCategories: refreshCategories,
        refreshTargets: refreshTargets
    };
}(); // execute anonymous function to immediatly return object
