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
    var _isAccepted = true;

    var _accOptionsData = null;
    var _accountOptions = null;

    var _oldSum = 0; // нужно для редактирования

    var _selectedAccount = '';
    var _selectedType = '0';
    var _selectedCategory = '-1';
    var _selectedTransfer = '';
    var _selectedTarget = '';

    var _$ufdAccount = null;
    var _$ufdType = null;
    var _$ufdCategory = null;
    var _$ufdTransfer = null;
    var _$ufdTarget = null;

    // #1248. обновляем списки только при открытии диалога
    var _dirtyAccounts = true;
    var _dirtyAccountsTransfer = true;
    var _dirtyCategories = true;
    var _dirtyTargets = true;

    // для мультивалютных переводов
    var _defaultCurrency = null;
    var _accountCurrency = null;
    var _transferCurrency = null;
    var _realConversionRate = 0;

    var _$blockCalendar = null;
    var _$blockWeekdays = null;
    var _$blockRepeating = null;
    var _$blockReminders = null;
    var _reminders = null;

    var _buttonsNormal = null;
    var _buttonsEditAccept = null;

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

    function _initUFDs() {
        if (!_$ufdType) {
            // если указатель пустой, инициализируем UFD выбора типа операции
            _$ufdType = $("#op_type");
            _$ufdType.ufd({manualWidth: 144, zIndexPopup: 1300, unwrapForCSS: true});
            _$ufdType.change();
        }

        if (!_$ufdCategory) {
            // если указатель пустой, инициализируем UFD выбора категории
            _$ufdCategory = $('#op_category');
            _$ufdCategory.ufd({manualWidth: 345, zIndexPopup: 1300, unwrapForCSS: true});
            _refreshCategories();
            _$ufdCategory.change();
        }

        if (!_$ufdAccount) {
            // #870. Запомним заранее выбранный аккаунт, если он был задан
            // (после инициализации это значение сбрасывается)
            var preAccount = _selectedAccount;

            // если указатель пустой, инициализируем UFD выбора счёта
            _$ufdAccount = $("#op_account");
            _$ufdAccount.ufd({manualWidth: 140, zIndexPopup: 1300, unwrapForCSS: true});
            _refreshAccounts();
            _$ufdAccount.change();

            if (preAccount) {
                setAccount (preAccount);
            }
        }
    }

    function _opConversionChange() {
        // пересчитываем курс перевода
        if (_transferCurrency.id == _defaultCurrency.id) {
            // покупаем валюту по умолчанию
            // отображаемый курс совпадает с реальным коэффициентом
            _realConversionRate = parseFloat($(this).val())
        } else {
            // обмен без участия валюты по умолчанию
            _realConversionRate = roundToSignificantFigures(1/parseFloat($(this).val()), 4);
        }

        var result = parseFloat(tofloat(calculate($('#op_amount').val()))) * _realConversionRate;
        result = roundToCents(result);

        if (!isNaN(result) && result != 'Infinity') {
            $("#op_transfer").val(result);
        }
    }

    function _opAmountChange(){
        var result = parseFloat(tofloat(calculate($('#op_amount').val()))) * _realConversionRate;
        result = roundToCents(result);

        if (!isNaN(result) && result != 'Infinity') {
            $("#op_transfer").val(result);
        }
    }

    function _opTransferChange() {
        var transfer = parseFloat($(this).val());
        var amount = parseFloat($("#op_amount").val());

        if (!isNaN(transfer) && !isNaN(amount)) {
            _realConversionRate = roundToSignificantFigures(transfer / amount, 4);
            _displayConversion();
        }
    }

    function _showCalc(selector) {
        var $field = $(selector);
        if ($field.val() != '')
            $field.val(calculate($field.val()));
        $.rwCalculator.node = $field;
        $.rwCalculator.functions.show.call($field);
    }

    function _initForm(){
        _buttonsNormal = {
            "Отмена": function() {
                // закрываем диалог
                $(this).dialog("close");
            },
            "Сохранить": function() {
                _saveOperation();
            }
        };

        _buttonsEditAccept = {
            "Отмена": function() {
                // закрываем диалог
                $(this).dialog("close");
            },
            "Сохранить": function() {
                _saveOperation();
            },
            "Редактировать и подтвердить": function() {
                $('#op_accepted').val("1");
                _saveOperation();
            }
        };

        _$dialog = $('form.op_addoperation').dialog({
            dialogClass: 'dlgOperationEdit',
            autoOpen: false,
            title: 'Новая операция',
            width: 400,
            open: function(event, ui) {
                $('#div_op_comment').html('<textarea rows="1" cols="1" id="op_comment"></textarea>');
            },
            beforeclose: function() {
                // очищаем форму перед закрытием
                _clearForm();

                // #1134. скрываем окно тегов, если оно открыто
                $('.op_tags_could').dialog("close");
            },
            buttons: _buttonsNormal
        });

        // настраиваем переключение между
        // обычным режимом и планированием
        $("#op_addoperation_but").click(function() {
            showForm();
        });

        $("#op_addtocalendar_but").click(function() {
            showFormCalendar();
        });

        $('#op_amount').keypress(function(e){
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { //Enter keycode
                $(this).val(calculate($(this).val()));
            }
        });

        // кнопка расчёта суммы TODO
        _$node.find('#btnCalcSum').click(function(){
            _showCalc("#op_amount");
        });

        // кнопка расчёта суммы для поля перевода
        _$node.find('#btnCalcSumTransfer').click(function(){
            _showCalc("#op_transfer");
        });

        $('#op_amount,#op_transfer').rwCalculator();

        $("#op_date").datepicker().datepicker('setDate', new Date());

        // обмен валют для мультивалютных переводов
        $('#op_conversion').change(_opConversionChange);
        $('#op_amount').change(_opAmountChange);
        $('#op_transfer').change(_opTransferChange);

        // выбор типа операции
        $("#op_type").change( function() {
            _selectedType = $(this).val();
            _changeOperationType();
        });

        // смена счёта
        $('#op_account').change( function() {
            _selectedAccount = $(this).val();
            _changeAccountForTransfer();

            // @todo: глючит, не даёт выбрать счёт
            // открываем журнал операций для этого счёта
            //if (easyFinance.widgets.operationsJournal) {
            //    easyFinance.widgets.operationsJournal.setAccount(_selectedAccount);
            //    $('#btn_ReloadData').click();
            //}
        });

        // смена счёта для перевода
        $('#op_AccountForTransfer').change( function(){
            _selectedTransfer = $(this).val();
            _changeAccountForTransfer();
        });

        // выбор категории
        $("#op_category").change( function() {
            _selectedCategory = $(this).val();
        });

        _initBlockCalendar();
    }

    function _initBlockCalendar() {
        _$blockCalendar = _$node.find('#operationEdit_planning');
        _$blockReminders = $("#operationEdit_reminders");

        _$blockCalendar.find('#cal_date_end').datepicker( { buttonImageOnly: true } );

        _$blockWeekdays = _$blockCalendar.find('#operationEdit_weekdays');
        _$blockRepeating = _$blockCalendar.find('#operationEdit_repeating');

        // переключаемся между разными периодами повторений
        _$blockCalendar.find('#cal_repeat').change(function(){
            if ($(this).val()=="7") { // неделя
                _$blockWeekdays.show();
                _$blockRepeating.show();

                var today = new Date();
                var day = today.getDay();
                // вс - 0, пн - 1, вт - 2 и т.п.
                day = (day == 0) ? day = 6 : day = day - 1;

                var i = 0;
                $('.week input').each(function(){
                    if (i == day)
                        $(this).attr('checked', 'checked');
                    i++;
                });
            } else if ($(this).val()=="0") { // Не повторять
                _$blockWeekdays.hide();
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

    // открываем и инициализируем диалог с заданным заголовком и классом
    function _openDialog(dialogTitle, dialogClass) {
        _$dialog
            .data('title.dialog', dialogTitle)
            .dialog( "option", "dialogClass", dialogClass)
            .dialog('open');

        if (!_$ufdAccount) {
            // если открываем в первый раз, инициализируем комбобоксы
            _initUFDs();
        } else {
            // при открытии окна тип операции по умолчанию - расход
            setType("0");
        }

        // обновляем список счетов
        _refreshAccounts();

        // обновляем список категорий
        _refreshCategories();

        // выставляем текующую дату
        $('#op_date').datepicker('setDate', new Date());

        // #1276. выбираем счёт, указанные в фильтре журнала операций
        var acc = $("#account_filtr").val();
        if (acc !==undefined && acc != "") {
            setAccount(acc);
        }
    }

    function _expandNormal() {
        _$dialog.dialog('option', 'buttons', _buttonsNormal);

        if (_isEditing) {
            _openDialog('Изменить операцию', 'edit');
        } else {
            _openDialog('Добавить операцию', '');
        }

        // TEMP: показываем операции перевода на фин. цель
        var htmlOptions = '<option value="0">Расход</option><option value="1">Доход</option><option value="2">Перевод со счёта</option><option value="4">Перевод на фин. цель</option>';
        $("#op_type").html(htmlOptions).ufd("changeOptions");
        // EOF TEMP

        // скрываем блок планирования
        _$blockCalendar.hide();
    }

    function _expandCalendar() {
        _$dialog.dialog('option', 'buttons', _buttonsNormal);

        var dialogTitle = '';
        var dialogClass = 'calendar';

        if (_isEditing) {
            dialogClass = 'calendar edit';

            if (_isChain) {
                dialogTitle = 'Редактировать серию операций';
            } else {
                dialogTitle = 'Редактировать операцию в календаре';
            }
        } else {
            dialogTitle = 'Добавить серию операций';
        }

        _openDialog(dialogTitle, dialogClass);

        // TEMP: СКРЫВАЕМ переводы на фин. цель
        var htmlOptions = '<option value="0">Расход</option><option value="1">Доход</option><option value="2">Перевод со счёта</option>';
        $("#op_type").html(htmlOptions).ufd("changeOptions");
        setType(data.type);
        // EOF TEMP

        if (!_reminders) {
            // если планируем операцию в первый раз,
            // инициализируем виджет напоминалок
            _reminders = easyFinance.widgets.operationReminders.init("#reminders", easyFinance.models.user, "operation");
        } else {
            // выставляем опции напоминалок по умолчанию
            _reminders.setDefaults();
        }

        // показываем блок планирования
        _$blockCalendar.show();

        // напоминалки (?)
        _$blockReminders.show();
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
                        str = str + '<option value="' + list[keyParent].id + '">' + list[keyParent].name + '</option>';

                        // выводим дочерние категории
                        for (var keyChild in list[keyParent].children) {
                            str = str + '<option value="' + list[keyParent].children[keyChild].id + '">&mdash; ' + list[keyParent].children[keyChild].name + '</option>';
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
            htmlOptions = htmlOptions + catPrint(_modelCategory.getUserCategoriesTreeOrdered(), typ);

            // обновляем список категорий
            $("#op_category").html(htmlOptions).ufd("changeOptions");
        } else if (_selectedType == "2") {
            //Перевод со счёта
            $("#op_category_fields,#op_target_fields").hide();
            $("#op_tags_fields,#op_transfer_fields").show();

            if (!_$ufdTransfer) {
                _$ufdTransfer = $("#op_AccountForTransfer");
                _$ufdTransfer.ufd({manualWidth: 140, zIndexPopup: 1300, unwrapForCSS: true});
                _ufdSetOptions(_$ufdTransfer, _accountOptions);
                _$ufdTransfer.change();
            }

            _refreshAccounts();

            _changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if (_selectedType == "4") {
            $("#op_target_fields").show();
            $("#op_tags_fields,#op_transfer_fields,#op_category_fields").hide();

            if (!_$ufdTarget) {
                // создаём выпадающий список целей
                _$ufdTarget = $("#op_target");

                _$ufdTarget.change( function() {
                    _selectedTarget = $(this).val();

                    var option = _$ufdTarget.find('option[value="' + _selectedTarget + '"]').eq(0);
                    $("#op_amount_done").text(formatCurrency(option.attr("amount_done")));
                    $("#op_amount_target").text(formatCurrency(option.attr("amount")));
                    $("#op_percent_done").text(formatCurrency(option.attr("percent_done")));
                    $("#op_forecast_done").text(formatCurrency(option.attr("forecast_done")));
                });

                _$ufdTarget.ufd({manualWidth: 140, zIndexPopup: 1300, unwrapForCSS: true});
            }

            // обновляем опции
            _dirtyTargets = true;
            _refreshTargets();
            _$ufdTarget.change();
        }
    }

    function _saveOperation() {
        if (!_validateForm()){
            return false;
        }

        var tip = $('#op_type').val();
        var id = $('#op_id').val();

        if ($('#op_accepted').val() == '')
            if (_isCalendar)
                $('#op_accepted').val("0");
            else
                $('#op_accepted').val("1");

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
        var last = '';
        var repeat = '';
        if ($('#cal_rep_every').attr('checked')){
            repeat = $('#cal_count').val();
        }else{
            last = $('#cal_date_end').val();
            if (last == "00.00.0000")
                last = "";
        }
        var every = $('#cal_repeat').val();

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

        var accepted = $('#op_accepted').val();
        var date = $('#op_date').val();
        var comment = $('#op_comment').val();
        var close2 = $('#op_close').is(':checked')?1:0;
        var tags = $('#op_tags').val();

        var reminders = _isCalendar ? easyFinance.widgets.operationReminders.getSettings() : {};
        var mailEnabled = reminders.mailEnabled;
        var mailDaysBefore = reminders.mailDaysBefore;
        var mailHour = reminders.mailHour;
        var mailMinutes = reminders.mailMinutes;

        var smsEnabled = reminders.smsEnabled;
        var smsDaysBefore = reminders.smsDaysBefore;
        var smsHour = reminders.smsHour;
        var smsMinutes = reminders.smsMinutes;

        // перед отправкой очищаем форму,
        // чтобы нельзя было сохранить
        // одну и ту же операцию дважды

        _clearForm();

        // отправляем запрос на сервер
        easyFinance.models.accounts.editOperationById(
            id,
            accepted,
            _selectedType,
            _selectedAccount,
            _selectedCategory,
            date,
            comment,
            amount1,
            _selectedTransfer,
            roundToSignificantFigures(_realConversionRate, 4),
            amount2, // сумма к получению при обмене валют
            _selectedTarget,
            close2,
            tags,
            chain, time, last, every, repeat, week,
            mailEnabled, mailDaysBefore, mailHour, mailMinutes, // параметры напоминаний по email
            smsEnabled, smsDaysBefore, smsHour, smsMinutes, // параметры напоминаний по sms

            function(data){
                // В случае успешного сохранения, закрываем диалог и обновляем календарь

                if (_isEditing) {
                    // закрываем окно после редактирования
                    _$dialog.dialog("close");
                } else {
                    // очищаем поле суммы, тегов и комментария после редактирования
                    $('#op_amount,#op_conversion,#op_transfer,#op_comment,#op_tags').val('');
                }

                if (data.result) {
                    _refreshTargets();

                    if (!_isCalendar && document.location.pathname.indexOf("/operation") == -1)
                        $.jGrowl(data.result.text + "<br><a class='white' href='/operation/#account="+account+"'>Перейти к операциям</a>", {theme: 'green',life: 2500});
                    else
                        $.jGrowl(data.result.text, {theme: 'green'});

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
        // или при подтверждении планируемой
        // проверяем заполнение всех полей
        if (!_isCalendar || $('#op_accepted').val() == "1") {
            if (_selectedAccount == ''){
                $.jGrowl('Вы ввели неверное значение в поле "счёт"!', {theme: 'red', stick: true});
                return false;
            }

            if (opType == "0" || opType == "1") {
                // для доходов и расходов

                if (_selectedCategory == '' || _selectedCategory == '-1' || _selectedCategory == "0") {
                        $.jGrowl('Выберите категорию!', {theme: 'red', stick: true});
                        return false;
                }
            } else if (opType == "2") {
                // для переводов

                if (_selectedTransfer == '') {
                    $.jGrowl('Укажите счёт для перевода!', {theme: 'red', stick: true});
                    return false;
                }

                if (_selectedAccount == _selectedTransfer) {
                    $.jGrowl('Счёт-отправитель и счёт-получатель должны различаться!', {theme: 'red', stick: true});
                    return false;
                }
            } else if (opType == "4") {
                if (_selectedTarget == '' || _selectedTarget == '0') {
                    $.jGrowl('Укажите финансовую цель!', {theme: 'red', stick: true});
                    return false;
                }
            }

            if (_selectedType == ''){
                $.jGrowl('Вы ввели неверное значение в поле "тип операции"!', {theme: 'red', stick: true});
                return false;
            }

            var sum = calculate($('#op_amount').val());

            if (sum <= 0) {
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

                var amount = parseFloat($("#op_target option:selected").attr("amount"));
                var amount_done = parseFloat($("#op_target option:selected").attr("amount_done"));
                $("#op_amount_done").text(formatCurrency($("#op_target :selected").attr("amount_done")));

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

            // проверка доступности напоминалок
            var reminders = easyFinance.widgets.operationReminders.getSettings();
            if (reminders.mailEnabled && !easyFinance.models.user.isMailRemindersAvailable()
                || reminders.smsEnabled && !easyFinance.models.user.isSmsRemindersAvailable()) {
                // уведомления не подключены!
                $("#operationEdit_noReminders").show();
                return false;
            }
        }

        return true;
    }

    function _clearForm() {
        $('#op_id,#op_accepted,#op_chain_id').val('');
        $('#op_amount,#op_conversion,#op_transfer,#op_AccountForTransfer,#op_comment,#op_tags').val('');
        $('span#op_amount_target').text();

        $('span#op_amount_done').text();
        $('span#op_forecast_done').text();
        $('span#op_percent_done').text();

        $('#op_close').removeAttr('checked');

        $('.week input').removeAttr('checked');

        // сбрасываем параметры повторения операции
        $('#cal_repeat').val("0").change();
        $('#cal_rep_every').attr('checked', 'checked');
        $('#cal_count').val("1");
        $('.week input').removeAttr('checked');
        $('#cal_date_end').val("");

        // скрываем сообщение об оплате напоминалок
        $("#operationEdit_noReminders").hide();
    }

    // обновляем поле "курс" на основе _realConversionRate
    function _displayConversion() {
        if (!_transferCurrency)
            return false;

        if (_transferCurrency.id == _defaultCurrency.id) {
            // покупаем валюту по умолчанию
            // отображаемый курс совпадает с реальным коэффициентом
            $('#op_conversion').val(roundToSignificantFigures(_realConversionRate, 4));
        } else {
            // обмен без участия валюты по умолчанию
            $('#op_conversion').val(roundToSignificantFigures(1/_realConversionRate, 4));
        }
    }

    function _changeAccountForTransfer() {
        _accountCurrency = _modelAccounts.getAccountCurrency(_selectedAccount);
        _transferCurrency = _modelAccounts.getAccountCurrency(_selectedTransfer);

        if (_transferCurrency && _accountCurrency)
            _realConversionRate = roundToSignificantFigures(_accountCurrency.cost / _transferCurrency.cost, 4);

        if (_selectedType == "2" && _selectedAccount != "" && _transferCurrency && _selectedTransfer != "" &&
            _accountCurrency && _accountCurrency.id != _transferCurrency.id) {
                $('#div_op_transfer_line').show();

                if (_accountCurrency.id == _defaultCurrency.id || _transferCurrency.id == _defaultCurrency.id) {
                    // обмен с участием валюты по умолчанию
                    var str = _defaultCurrency.text + ' за ';
                    str += (_accountCurrency.id == _defaultCurrency.id) ? _transferCurrency.name : _accountCurrency.name;
                    $('#op_conversion_text').text (str);
                } else {
                    // обмен без участия валюты по умолчанию
                    $('#op_conversion_text').text (_accountCurrency.name + ' за ' + _transferCurrency.name);
                }

                _displayConversion();
        } else {
            _displayConversion();
            $('#div_op_transfer_line').hide();
            $('#op_conversion').val('');
        }
    }

    // используется для заполнения UFD опциями
    function _ufdSetOptions($ufd, htmlOptions) {
        if ($ufd) {
            $ufd.empty();

            $ufd.html(htmlOptions);
            $ufd.find('option:first').attr('selected', 'selected');
            $ufd.ufd("changeOptions");
        }
    }

    function _refreshAccounts() {
        if (!_$ufdAccount)
            return;

        if (!_dirtyAccounts && !_dirtyAccountsTransfer) {
            return;
        }

        // составляем список счетов
        var accounts = _modelAccounts.getAccounts();
        var accountsOrdered = _modelAccounts.getAccountsOrdered();
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

        var _accOptionsData = [];
        var recentIds = {};
        if (recentCount >= accountsCount || recentCount == 0) {
            // если счетов мало (не больше частых счетов),
            // выводим все счета по алфавиту
            for (key in accounts) {
                _accOptionsData.push({value: accounts[key].id, text: accounts[key].name + ' (' + _modelAccounts.getAccountCurrencyText(accounts[key].id) + ')'});
            }
        } else {
            // если счетов много, сначала выводим часто используемые счета
            for (key in recent) {
                _accOptionsData.push({value: accounts[key].id, text: accounts[key].name + ' (' + _modelAccounts.getAccountCurrencyText(accounts[key].id) + ')'});
                recentIds[accounts[key].id] = true;
            }

            _accOptionsData.push({value: "", text: "&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;"});

            // затем выводим все остальные счета в алфавитном порядке
            for (var row in accountsOrdered) {
                // #1082. не выводим повторно частоиспользуемые счета
                if (!recentIds[accountsOrdered[row].id])
                    _accOptionsData.push({value: accountsOrdered[row].id, text: accountsOrdered[row].name + ' (' + _modelAccounts.getAccountCurrencyText(accountsOrdered[row].id) + ')'});
            }
        }

        // конструируем списки счетов
        var strOptions = '';
        for (var k in _accOptionsData) {
            strOptions += '<option value="' + _accOptionsData[k].value + '">' + _accOptionsData[k].text + '</option>';
        }

        // #1412. сохраняем для последующей
        // инициализации списка целевых счетов
        _accountOptions = strOptions;

        if (_dirtyAccounts) {
            // заполняем список счетов
            _dirtyAccounts = false;
            _ufdSetOptions(_$ufdAccount, _accountOptions);
        }

        if (_selectedType == "2") {
            if (_dirtyAccountsTransfer) {
                // заполняем список целевых счетов
                _ufdSetOptions(_$ufdTransfer, _accountOptions);
                _dirtyAccountsTransfer = false;
            }
        }
    }

    function _refreshCategories() {
        if (_$ufdCategory) {
            _changeOperationType();
        }

        if (_dirtyCategories) {
            _dirtyCategories = false;
        } else {
            return;
        }
    }

    function _refreshTargets() {
        if (!_$ufdTarget || !_dirtyTargets) {
            return;
        }

        var data = res.user_targets;
        if (!data) {
            data = {};
        }

        var t;
        var o = '';
        for (var v in res['user_targets']) {
            t = res['user_targets'][v];
            if (t['done']=='0')
            o += '<option value="'+t['id']+'" target_account_id="'+t['account']+'" amount_done="'+t['amount_done']+
                '"percent_done="'+t['percent_done']+'" forecast_done="'+t['forecast_done']+'" amount="'+t['amount']+'">'+t['title']+'</option>';
        }

        // заполняем список счетов
        _ufdSetOptions(_$ufdTarget, o);
        _dirtyTargets = false;
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

        easyFinance.models.user.reload();

        _modelAccounts = modelAccounts;
        _modelCategory = modelCategory;

        _defaultCurrency = easyFinance.models.currency.getDefaultCurrency();

        // load tags cloud and setup tags dialog
        _initTags();

        // setup form
        _initForm();

        // #1248. ставим флаг для обновления списков счетов
        var accDirty = function() {
            _dirtyAccounts = true;
            _dirtyAccountsTransfer = true;
        }

        $(document).bind('accountAdded', accDirty);
        $(document).bind('accountEdited', accDirty);
        $(document).bind('accountDeleted', accDirty);

        // #1248. ставим флаг для обновления списка категорий
        var catDirty = function() {
            _dirtyCategories = true;
        }

        $(document).bind('categoriesLoaded', catDirty);
        $(document).bind('categoryEdited', catDirty);
        $(document).bind('categoryDeleted', catDirty);

        return this;
    }

    function setSum(sum){
        _oldSum = Math.abs(sum);
        $('#op_amount').val(_oldSum == 0 ? '' : _oldSum);
    }

    function setType(id){
        _selectedType = id;
        _changeOperationType();
        if (_$ufdType) {
            _$ufdType.selectOptions(id, true).ufd("changeOptions");
        }
    }

    function setAccount(id){
        if (id == "0") {
            return;
        }

        // #1593. Ошибка подтверждения операций из АМТ
        if (id === null || id === undefined) {
            // счёт может быть не указан, например, при импорте из АМТ
            // тогда выбранный счёт будет совпадать с тем,
            // который уже был выбран раньше
            id = _selectedAccount;
        } else {
            // меняем счёт на заданный
            _selectedAccount = id;
        }

        _changeAccountForTransfer();
        if (_$ufdAccount) {
            _$ufdAccount.selectOptions(id, true).ufd("changeOptions");
        }
    }

    function setCategory(id){
        if (id == "0" || id == null) {
            return;
        }

        _selectedCategory = id;
        if (_$ufdCategory) {
            _$ufdCategory.selectOptions(id, true).ufd("changeOptions");
        }
    }

    function setTransfer(id){
        if (id ===null || id == "0") {
            return;
        }

        _selectedTransfer = id;
        _changeAccountForTransfer();
        if (_$ufdTransfer) {
            _$ufdTransfer.selectOptions(id, true).ufd("changeOptions");
        }
    }

    function setTarget(id){
        if (id ===null || id == "0") {
            return;
        }

        _selectedTarget = id;
        if (_$ufdTarget) {
            _$ufdTarget.selectOptions(id, true).ufd("changeOptions");
        }
    }

    function showForm() {
        _isEditing = false;
        _isCalendar = false;
        _isChain = false;

        _$blockReminders.hide();
        _$blockCalendar.hide();
        _expandNormal();
    }

    function showFormCalendar() {
        _isEditing = false;
        _isCalendar = true;
        _isChain = true;
        _expandCalendar();

        // TEMP: не показываем операции на фин. цель
        var htmlOptions = '<option value="0">Расход</option><option value="1">Доход</option><option value="2">Перевод со счёта</option>';
        $("#op_type").html(htmlOptions).ufd("changeOptions");
        // EOF TEMP
    }

    function _fillValues(data, isEditing) {
        if (isEditing && data.id) {
            $('#op_id').val(data.id);
        } else {
            $('#op_id').val('');
        }

        if (typeof data.accepted != 'undefined') {
            $('#op_accepted').val(data.accepted);
            if (data.accepted == "0" && !_isChain) {
                _$dialog.dialog('option', 'buttons', _buttonsEditAccept);
            } else {
                _$dialog.dialog('option', 'buttons', _buttonsNormal);
            }
        } else {
            $('#op_accepted').val("1");
            _$dialog.dialog('option', 'buttons', _buttonsNormal);
        }

        var typ = data.type;
        setType(typ);

        // перевод
        if (typ == "2") {

            // from this account
            setAccount(data.account_id || data.account);

            // to this account
            setTransfer(data.transfer);

            // перевод с обменом валют
            if (toFloat(data.curs) != 0) {
                _realConversionRate = parseFloat(data.curs);

                _displayConversion();

                setSum(roundToCents(Math.abs(data.money || data.amount || 0)));

                $("#op_amount").change();
            } else {
                setSum(Math.abs(data.money || data.amount || data.moneydef || 0));
            }
        } else {
            // обычная операция
            setAccount(data.account_id || data.account);
            setSum(Math.abs(data.money || data.amount || data.moneydef || 0));
        }

        setCategory(data.cat_id);

        setTarget(data.target_id);
        if (typeof(data.date) == "string") {
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

        $('#op_comment').val(data.comment || data.description || '');
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

    /**
     * Функция заполняет форму данными операции
     * @param data: данные для заполнения
     * @param isEditing: true если редактируем операцию
     */
    function fillForm(data, isEditing) {
        _isCalendar = false;
        _isChain = false;
        _isEditing = isEditing;

        // открываем диалог работы с обычными операциями
        _expandNormal();

        // выводим данные по операции
        _fillValues(data, isEditing);
    }

    function _fillCalendarValues(data, isEditing, isChain) {
        // заполняем атрибуты цепочки / события
        $('#op_chain_id').val(data.chain || '');

        if (typeof data.accepted != 'undefined')
            $('#op_accepted').val(data.accepted);
        else
            $('#op_accepted').val("0");

        if (isChain) {
            // выводим дату начала серии операций
            if (typeof(data.date) == "string") {
                $('#op_date').val(data.date);
            }
        }

        if (data.last && data.last != '' && data.last != '00.00.0000') {
            $('#cal_date_end').val(data.last);
            $('#cal_rep_to').attr("checked", "checked");
        } else {
            $('#cal_date_end').val('');
            $('#cal_rep_every').attr("checked", "checked");
        }

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

        if (isEditing && !isChain)
            _$blockCalendar.hide();
        else
            _$blockCalendar.show();

        // заполняем параметры напоминаний
        var reminders = {
            mailEnabled: data.mailEnabled,
            mailDaysBefore: data.mailDaysBefore,
            mailHour: data.mailHour,
            mailMinutes: data.mailMinutes,

            smsEnabled: data.smsEnabled,
            smsDaysBefore: data.smsDaysBefore,
            smsHour: data.smsHour,
            smsMinutes: data.smsMinutes
        }

        easyFinance.widgets.operationReminders.setSettings(reminders);
    }

    function fillFormCalendar(data, isEditing, isChain) {
        _isCalendar = true;
        _isEditing = isEditing;
        _isChain = isChain;

        // открываем диалог для планирования
        _expandCalendar();

        // выводим основные данные по операции
        _fillValues(data, isEditing);

        // выводим данные по планированию операции(й)
        _fillCalendarValues(data, isEditing, isChain);

        // TEMP: не показываем операции на фин. цель
        var htmlOptions = '<option value="0">Расход</option><option value="1">Доход</option><option value="2">Перевод со счёта</option>';
        $("#op_type").html(htmlOptions).ufd("changeOptions");
        setType(data.type);
        // EOF TEMP
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        setCategory: setCategory,
        setSum: setSum,
        setAccount: setAccount,
        setTransfer: setTransfer,
        showForm: showForm,
        showFormCalendar: showFormCalendar,
        fillForm: fillForm,
        fillFormCalendar: fillFormCalendar
    };
}(); // execute anonymous function to immediatly return object

