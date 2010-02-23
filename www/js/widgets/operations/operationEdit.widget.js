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

    var accOptionsData = null;

    // private functions

    function _initTags() {
        $('a#infoicon1').click(function(){
            $('#op_infobut1').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('#op_infobut1').show();
        })
        $('a#infoicon2').click(function(){
            $('#op_infobut2').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('#op_infobut2').show();
        })
        
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
        // считаем количество всех счетов
        for (var key in accounts) {
			accountsCount++;
        }
		
        var recentCount = 0;
        var recent = res.accountsRecent;
        // считаем количество часто используемых счетов
        for (var key in recent) {
            recentCount++;
        }

        _accOptionsData = [];
        if (recentCount >= accountsCount || recentCount == 0) {
            // если счетов мало (не больше частых счетов),
            // выводим все счета по алфавиту
            for (var key in accounts) {
                _accOptionsData.push({value: accounts[key].id, text: accounts[key].name + ' (' + _modelAccounts.getAccountCurrencyText(key) + ')'});
            }
        } else {
            // если счетов много, сначала выводим часто используемые счета
            for (var key in recent) {
                _accOptionsData.push({value: accounts[key].id, text: accounts[key].name + ' (' + _modelAccounts.getAccountCurrencyText(key) + ')'});
                delete accounts[key];
            }

            _accOptionsData.push({value: "", text: "&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;"});

            // затем выводим все остальные счета в алфавитном порядке
            for (var key in accounts) {
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
        // for correct sexyCombo initialization
        //$(".op_addoperation").show();

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
        
        $(".op_addoperation").hide();

        $('#op_btn_Save').click(function(){
            _saveOperation();

            return false;
        });

        $('#op_btn_Cancel').click(function(){
            $("#op_btn_Save").removeAttr('disabled');
            $("#op_addoperation_but").click();

            return false;
        });

        $("#op_addoperation_but").click(function(){
            $(this).toggleClass("act");
            if($(this).hasClass("act")){
                _clearForm();
                $(".op_addoperation").show();

                // если открываем в первый раз, инициализируем комбобоксы
                if (!_sexyAccount)
                    _initSexyCombos();
            } else {
                $(".op_addoperation").hide();
            }
        });
        //поле суммы
        _$node.find('#btnCalcSum').click(function(){
            var calculator = $('#op_amount');
            $(calculator).val(calculate($(calculator).val()));
        });
        $('#op_amount').live('keypress',function(e){
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
        //\/поле суммы
        $('.calculator-trigger').click(function(){
            $(this).closest('div').find('#op_amount,#amount').val(tofloat($('#op_amount').val()));
        })
        $("#op_date").datepicker().datepicker('setDate', new Date());

        $('#op_amount,#op_currency').change(function(){
            if (_selectedType == "2") {
                /*
                 *@TODO Дописать округление
                 */
                var result = $('#op_currency').val() * Math.round(parseFloat(tofloat($('#op_amount').val())));
                result = Math.round(result*100)/100;
                if (!isNaN(result) && result != 'Infinity') {
                    $("#op_convertSumCurrency").html("&nbsp; конвертация: "+result);
                    TransferSum = result;
                }
            }
        });

        $('#op_account').change( function(){_changeAccountForTransfer();});
        $('#op_AccountForTransfer').change( function(){_changeAccountForTransfer();});
    }

    function _changeOperationType() {
        // Расход или Доход
        if (_selectedType == "0" || _selectedType == "1") {
            $("#op_category_fields,#op_tags_fields").show();
            $("#op_target_fields,#op_transfer_fields").hide();

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

                        //$("span.op_currency").each(function(){
                        //    if (t != 0){
                                //$(this).text(" "+res['accounts'][$("#op_target :selected").attr("target_account_id")]['cur']);
                        //    }
                        //});

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
        var suum = tofloat($('#op_amount').val());
        var tip = $('#op_type').val();
//alert($('#op_id').val());
//alert($('#op_type').val());
//alert($('#op_account').val());
//alert(_selectedType);
//alert(_selectedCategory);
//alert($('#op_AccountForTransfer').val());
        var account = $('#op_account').val();
        easyFinance.models.accounts.editOperationById(
            $('#op_id').val(),
            _selectedType,
            _selectedAccount,
            _selectedCategory,
            $('#op_date').val(),
            $('#op_comment').val(),
            tofloat(calculate($('#op_amount').val())),
            _selectedTransfer,
            $('#op_currency').val(),
            TransferSum,
            _selectedTarget,
            //$('#op_close:checked').length,
            $('#op_close2').val(),
            $('#op_tags').val(),

            function(data){
                // В случае успешного добавления, закрываем диалог и обновляем календарь
                $('#op_btn_Save').removeAttr('disabled');
							
                if (data.result) {
                    _clearForm();

                    refreshTargets();

					$.jGrowl(data.result.text, {theme: 'green'});
                    $.jGrowl("<a href='/operation/#account="+account+"' style='color:black'>Перейти к операциям</a>", {theme: 'green',life: 10000});
                    if (tip == 4)
                        MakeOperation();// @todo: заменить на отправку event'a!
                } else if (data.error) {
                    $.jGrowl(data.error.text, {theme: 'red', stick: true});
                }
            }
        );

        return true;
    }

    function _validateForm() {
        $error = '';

        var comment = $('#op_comment').val();
        if (comment.indexOf('<') != -1 || comment.indexOf('>') != -1) {
            $.jGrowl("Комментарий не должен содержать символов < и >!", {theme: 'red', life: 5000});
            return false;
        }

        var tags = $('#op_tags').val();
        if (tags.indexOf('<') != -1 || tags.indexOf('>') != -1) {
            $.jGrowl("Тэги не должны содержать символов < и >!", {theme: 'red', life: 5000});
            return false;
        }

        if (_selectedAccount == ''){
            $.jGrowl('Вы ввели неверное значение в поле "счёт"!', {theme: 'red', stick: true});
            return false;
        }

        if (_selectedType == ''){
            $.jGrowl('Вы ввели неверное значение в поле "тип операции"!', {theme: 'red', stick: true});
            return false;
        }

        var date = $('#op_date').val();
        if (! date.match(/^\d\d?\.\d\d?\.\d\d\d\d$/)) {
            $.jGrowl('Вы ввели неверное значение в поле "дата"!', {theme: 'red', stick: true});
            return false;
        }
        
        var opType = $("#op_type option:selected").val();
        
        if (opType == "0" || opType == "1") {
            // для доходов и расходов
            // || _modelCategory.isParentCategory(_selectedCategory)) {
            if (_selectedCategory == '' || _selectedCategory == '-1') {
                    $.jGrowl('Выберите категорию!', {theme: 'red', stick: true});
                    return false;
            }
        } else if (opType == "2") {
            if (_selectedTransfer == '') {
                $.jGrowl('Укажите счёт для перевода!', {theme: 'red', stick: true});
                return false;
            }
        } else if (opType = "4") {
            if (_selectedTarget == '' || _selectedTarget == '0') {
                $.jGrowl('Укажите финансовую цель!', {theme: 'red', stick: true});
                return false;
            }
        }
        
        if (!/[\-]?[0-9]+([\.][0-9]+)?/.test(calculate($('#op_amount').val()))){
            $.jGrowl('Вы ввели неверное значение в поле "сумма"!', {theme: 'red', stick: true});
            return false;
        }

        //Запрос подтверждения на выполнение операции в случае ухода в минус.
        // не выполняем проверку для кредитных карт! см. тикет 669
        if (_modelAccounts.getAccountType(_selectedAccount) != "8") {
            var am = tofloat($('#op_amount').val()+'.0');

            // см. тикет #306
            //var tb = tofloat(res['accounts'][$("#op_account option:selected").val()]['total_balance']);
            var tb = tofloat(_modelAccounts.getAccountBalanceTotal(_selectedAccount));
            var ab = tofloat(_modelAccounts.getAccountBalanceAvailable(_selectedAccount));

            // @ticket 401
            // при редактировании расходных операций
            // учитываем то, что при увеличении суммы со счёта будет списана
            // не вся сумма, а только разница между старым и новым значением
            if (opType != 1 && $('form').attr('action').indexOf('edit') != -1)
                am = am - _oldSum;
        }

        if (_selectedType == '4') {
            /**
             *@FIXME Написать обновление финцелей
             */
             //alert("tratata");

            //var amount = parseFloat($("#op_target option:selected").attr("amount"));
            //alert(amount);
            //$("#op_amount").val(amount);

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
        return true;
    }

    function _clearForm() {
        $('#op_id').val('');
        $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags').val('');

        $('span#op_amount_target').text();

        $('span#op_amount_done').text();
        $('span#op_forecast_done').text();
        $('span#op_percent_done').text();

        $('#op_close').removeAttr('checked');

        $('form').attr('action','/operation/add/');
    }

    function _changeAccountForTransfer() {
        if (_selectedType == "2" && _selectedAccount != "" && _selectedTransfer != "" &&
            res.accounts[_selectedAccount].currency != res.accounts[_selectedTransfer].currency) {
                $('#op_operationTransferCurrency').show();

                $('#op_operationTransferCurrency :first-child').html('Курс <b>'+
                        _modelAccounts.getAccountCurrencyText(_selectedAccount)+'</b> к <b>'+ _modelAccounts.getAccountCurrencyText(_selectedTransfer)+'</b>');
                        data = _modelAccounts.getAccountCurrencyCost(_selectedAccount);
                        data /= _modelAccounts.getAccountCurrencyCost(_selectedTransfer);
                data = data.toString();
                i = data.indexOf('.');
                data = data.substr(0, i+5);
                $('#op_currency').val(data);
        } else {
            $('#op_operationTransferCurrency').hide();
            $('#op_operationTransferCurrency').val('');
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

        // load tags cloud and setup tags dialog
        _initTags();

        // setup form
        _initForm();

        $(document).bind('accountsLoaded', refreshAccounts);
        $(document).bind('accountAdded', refreshAccounts);
        $(document).bind('accountDeleted', refreshAccounts);

        $(document).bind('categoriesLoaded', refreshCategories);
        $(document).bind('categoryAdded', refreshCategories);
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
        $('#op_addoperation_but').addClass("act");
        _clearForm();
        $(".op_addoperation").show();
        if (!_sexyAccount)
            _initSexyCombos();
    }

    /**
     * Функция заполняет форму данными
     * @param data данные для заполнения
     */
    function fillForm(data, isCopy) {
        //clearForm();

        if (!_sexyAccount)
            _initSexyCombos();

        if (isCopy)
            $('#op_id').val('');
        else
            $('#op_id').val(data.id);

        var typ = '0';
        if (data.tr_id != null && data.tr_id != '') {
            // transfer
            typ = '2';
        } else {
            if (data.virt=='1') {
                typ = '4';
            } else {
                if (data.drain=='1') {
                    typ = '0';
                } else {
                    typ = '1';
                }
            }
        }

        setType(typ);

        if (data.transfer != "" && data.tr_id != null) {
            if (data.tr_id == "0") {
                // from this account
                setAccount(data.account_id);
                
                // to this account
                setTransfer(data.transfer);
            } else {
                // original operation id
                $('#op_id').val(data.tr_id);
                
                // to this account
                setTransfer(data.account_id);

                // from this account
                setAccount(data.transfer);
            }
        } else {
            // to this account
            setAccount(data.account_id);

            // from this account
            setTransfer(data.transfer);
        }

        if (data.moneydef)
            setSum(Math.abs(data.moneydef))
        else
            setSum(Math.abs(data.money));

        setCategory(data.cat_id);

        setTarget(data.target_id);

        if (data.curs)
            $('#op_currency').val(data.curs);

        $('#op_date').val(data.date);
        if (data.tags)
            $('#op_tags').val(data.tags);
        else
            $('#op_tags').val('');
        $('#op_comment').val(data.comment.replace(/&quot;/g, '"'));

        $(document).scrollTop(300);
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
        refreshAccounts: refreshAccounts,
        refreshCategories: refreshCategories,
        refreshTargets: refreshTargets
    };
}(); // execute anonymous function to immediatly return object
