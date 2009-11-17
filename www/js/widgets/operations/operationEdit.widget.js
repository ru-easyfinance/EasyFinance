/**
 * @desc Add/Edit Operation Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.operationEdit = function(){
    // private constants

    // private variables
    var _model = null;

    var _$node = null;

    var _cat = 0; // current category
    var _oldSum = 0; // нужно для редактирования

    var _selectedAccount = '';
    var _selectedType = '';
    var _selectedCategory = '';

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
        //$('#op_infobut1').html('<ul><li>fwfer</li></ul>');
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

    function _initForm(){
        // for correct sexyCombo initialization
        $(".op_addoperation").show();

        $("#op_account").sexyCombo({
            filterFn: _sexyFilter,
            changeCallback: function() {
                _selectedAccount = this.getCurrentHiddenValue();

                _changeAccountForTransfer();
                // reload operation journal
                // operationsJournalReload();
                easyFinance.widgets.operationsJournal.setAccount(_selectedAccount);
                $('#btn_ReloadData').click();
            }
        });

        $("#op_type").sexyCombo({
            filterFn: _sexyFilter,
            changeCallback: function() {
                _selectedType = this.getCurrentHiddenValue();
                _changeTypeOperation('add'); 
            }
        });
        
        $("#op_category").sexyCombo({
            filterFn: _sexyFilter,
            changeCallback: function() {
                _selectedCategory = this.getCurrentHiddenValue();
            }
        });
        $(".op_addoperation").hide();

        $('#op_btn_Save').click(function(){
            _saveOperation();

            return false;
        });

        $('#op_btn_Cancel').click(function(){
            _clearForm();
            $(".op_addoperation").hide();
            return false;
        });

        $("#op_addoperation_but").click(function(){
            $(this).toggleClass("act");
            if($(this).hasClass("act")){
                $(".op_addoperation").show();
            } else {
                $(".op_addoperation").hide();
            }
        });
        $('#op_amount').live('keyup',function(e){
            FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
        });

        $('.calculator-trigger').click(function(){
            $(this).closest('div').find('#op_amount,#amount').val(tofloat($('#op_amount').val()));
        })
        $("#op_date").datepicker().datepicker('setDate', new Date());

        $('#op_amount,#op_currency').change(function(){
            if (_selectedType == "2") {
                /*
                 *@TODO Дописать округление
                 */
                var result = Math.round($('#op_amount').val() / $('#op_currency').val());
                if (!isNaN(result) && result != 'Infinity') {
                    $("#op_convertSumCurrency").html("конвертация: "+result);
                    TransferSum = result;
                }
            }
        });

        $('#op_AccountForTransfer').change( function(){_changeAccountForTransfer();});

        $('#op_target').change(function(){
            t = parseInt($("#op_target :selected").attr("target_account_id"));
            $("span.op_currency").each(function(){
                if (t != 0){
                    //$(this).text(" "+res['accounts'][$("#op_target :selected").attr("target_account_id")]['cur']);
                }
            });
            $("#op_amount_done").text(formatCurrency($("#op_target :selected").attr("amount_done")));
            $("#op_amount_target").text(formatCurrency($("#op_target :selected").attr("amount")));
            $("#op_percent_done").text(formatCurrency($("#op_target :selected").attr("percent_done")));
            $("#op_forecast_done").text(formatCurrency($("#op_target :selected").attr("forecast_done")));
        });
    }

    function _changeTypeOperation() {
        // запоминаем выбранную ранее категорию,
        // чтобы при переключении типа операции
        // заново её выбрать, по возможности
        var _newcat = _cat;

        // Расход или Доход
        if (_selectedType == "0" || _selectedType == "1") {
            $("#op_category_fields,#op_tags_fields").show();
            $("#op_target_fields,#op_transfer_fields").hide();
            if (_selectedType == "1")
                    $.post('/category/cattypechange/',{
                        type : 1
                    },function(data){
                        $("#op_category").html(data);
                        if (_newcat) {
                            $('#op_category').val(_newcat);
                            $.sexyCombo.changeOptions("#op_category", _newcat);
                        } else {
                            $.sexyCombo.changeOptions("#op_category");
                        }
                    },'json');
            if (_selectedType == "0")
                $.post('/category/cattypechange/',{
                        type : -1
                    },function(data){
                        $("#op_category").html(data);
                        if (_newcat) {
                            $('#op_category').val(_newcat);
                            $.sexyCombo.changeOptions("#op_category", _newcat);
                        } else {
                            $.sexyCombo.changeOptions("#op_category");
                        }
                    },'json');
                //toggleVisibleCategory($('#op_category'),-1);//отображает в списке категорий для добавления операции доходные
        //Перевод со счёта
        } else if (_selectedType == "2") {
            $("#op_category_fields,#op_target_fields").hide();
            $("#op_tags_fields,#op_transfer_fields").show();
            _changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if (_selectedType == "4") {
            $('#op_target').remove('option :not(:first)');
            var o = '';
            var t;
            for (var v in res['user_targets']) {
                t = res['user_targets'][v];
                if (t['done']=='0')
                o += '<option value="'+v+'" target_account_id="'+t['account']+'" amount_done="'+t['amount_done']+
                    '"percent_done="'+t['percent_done']+'" forecast_done="'+t['forecast_done']+'" amount="'+t['money']+'">'+t['title']+'</option>';
            }
            $("#op_target_fields").show();
            $("#op_tags_fields,#op_transfer_fields,#op_category_fields").hide();
            $('#op_target').html(o);
            $('#op_target').change();
        }
    }

    function _saveOperation() {
        if (!_validateForm()){
            return false;
        }
        $.jGrowl("Операция сохраняется", {theme: 'green'});
        var suum = tofloat($('#op_amount').val());
        var tip = $('#op_type').val();

        easyFinance.models.accounts.editOperationById(
            $('#op_id').val(),
            $('#op_type').val(),
            $('#op_account').val(),
            $('#op_category').val(),
            $('#op_date').val(),
            $('#op_comment').val(),
            tofloat($('#op_amount').val()),
            $('#op_AccountForTransfer').val(),
            $('#op_currency').val(),
            TransferSum,
            $('#op_target').val(),
            //$('#op_close:checked').length,
            $('#op_close2').val(),
            $('#op_tags').val(),

            function(data){
                // В случае успешного добавления, закрываем диалог и обновляем календарь
                if (data.length == 0) {
                    _clearForm();
                    /// переписать
                    var o = '';
                    var t;
                    for (var v in res['user_targets']) {
                        t = res['user_targets'][v];
                        if (v != $('#op_target').val())
                        o += '<option value="'+v+'" target_account_id="'+t['account']+'" amount_done="'+t['amount_done']+
                            '"percent_done="'+t['percent_done']+'" forecast_done="'+t['forecast_done']+'" amount="'+t['money']+'">'+t['title']+'</option>';
                        else{
                            t['amount_done']=(parseFloat(t['amount_done'])+parseFloat(suum)).toString();
                            o += '<option value="'+v+'" target_account_id="'+t['account']+'" amount_done="'+/*(parseFloat(t['amount_done'])+parseFloat(suum)).toString()*/t['amount_done']+
                            '"percent_done="'+t['percent_done']+'" forecast_done="'+t['forecast_done']+'" amount="'+t['money']+'">'+t['title']+'</option>';
                    }
                    }
                    /// переписать
                    $('#op_target').html(o);
                    $.jGrowl("Операция успешно сохранена", {theme: 'green'});
                    if (tip == 4)
                        MakeOperation();// @todo: заменить на отправку event'a!
                } else {
                    var e = '';
                    for (var v in data) {
                        e += data[v]+"\n";
                    }
                    $.jGrowl("Ошибки при сохранении : " + e, {theme: 'red', stick: true});
                }
            }
        );

        return true;
    }

    function _validateForm() {
        $error = '';

        if (_selectedAccount == ''){
            $.jGrowl('Вы ввели неверное значение в поле "счёт"!', {theme: 'red', stick: true});
            return false;
        }

        if (_selectedType == ''){
            $.jGrowl('Вы ввели неверное значение в поле "тип операции"!', {theme: 'red', stick: true});
            return false;
        }

        if (_selectedCategory == ''){
            $.jGrowl('Вы ввели неверное значение в поле "категория"!', {theme: 'red', stick: true});
            return false;
        }
        
        if (isNaN(parseFloat($('#op_amount').val()))){
            $.jGrowl('Вы ввели неверное значение в поле "сумма"!', {theme: 'red', stick: true});
            return false;
        }

        var opType = $("#op_type option:selected").val();

        //Запрос подтверждения на выполнение операции в случае ухода в минус.
        var am = tofloat($('#op_amount').val()+'.0');

        // см. тикет #306
        //var tb = tofloat(res['accounts'][$("#op_account option:selected").val()]['total_balance']);
        var tb = tofloat(_model.getAccountBalanceTotal($("#op_account option:selected").val()));
        var ab = tofloat(_model.getAccountBalanceAvailable($("#op_account option:selected").val()));

        // @ticket 401
        // при редактировании расходных операций
        // учитываем то, что при увеличении суммы со счёта будет списана
        // не вся сумма, а только разница между старым и новым значением
        if (opType != 1 && $('form').attr('action').indexOf('edit') != -1)
            am = am - _oldSum;

        //* && $("#op_type option:selected").val() != 1*/)
        if ( (am-tb)>0 && opType!=1){
            if (!confirm('Данная транзакция превышает остаток средств на вашем счёте! Продолжить ?'))
                return false;
            //$.jGrowl('Введённое значение суммы превышает общий остаток средств на данном счёте!!!', {theme: 'red', stick: true});
        }//*/

        // @TODO: вернуть эту проверку, когда можно будет делать перевод из резерва обратно в доступные деньги
        //alert(res['accounts'][$("#op_account option:selected").val()]['total_balance']);
        //alert(res['accounts'][$("#op_account option:selected").val()]['reserve']);
        //если сумма совершаемой операции превышает сумму доступного остатка(Общий - резерв на финцели)
        // тогда предупреждаем пользователя и в случае согласия снимаем нехватающую часть денег с фин цели.
        /*
        if ((am - ab)>0) {
            alert ("Введённая сумма операции превышает доступный остаток счёта с учётом резерва.\n\
Переведите деньги с финансовой цели и повторите операцию ещё раз!");
            return false;
        }
        */

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
        $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags').val('');//#op_date убрал

        $('span#op_amount_target').text();

        $('span#op_amount_done').text();
        $('span#op_forecast_done').text();
        $('span#op_percent_done').text();

        $('#op_close').removeAttr('checked');

        $('form').attr('action','/operation/add/');
    }

    function _changeAccountForTransfer() {
        if (_selectedType == "2" &&
            $('#op_account :selected').attr('currency') != $('#op_AccountForTransfer :selected').attr('currency')) {
                $('#op_operationTransferCurrency').show();
                $.post('/operation/get_currency/', {
                        SourceId : $("#op_account").val(),
                        TargetId : $("#op_AccountForTransfer").val()
                    }, function(data){
                        $('#op_operationTransferCurrency :first-child').html('Курс <b>'+
                            $('#op_account :selected').attr('abbr')+'</b> к <b>'+$('#op_AccountForTransfer :selected').attr('abbr')+'</b>');
                        $('#op_currency').val(data);
                    }, 'json'
                );
        } else {
            $('#op_operationTransferCurrency').hide();
        }
    }

    // public variables

    // public functions
    /**
     * @desc init widget
     * @usage init(nodeSelector, model)
     */
    function init(nodeSelector, model) {
        if (!model)
            return null;

        _$node = $(nodeSelector);

        _model = model;

        // load tags cloud and setup tags dialog
        _initTags();

        // setup form
        _initForm();

        return this;
    }

    function setCategory(cat){
        var $combo, strOption;
        
        _cat = cat;
        
        $combo = $('#op_category');
        $combo.val(_cat);
        
        strOption = $combo.find(":selected").text();

        $.sexyCombo.changeOptions("#op_category", _cat);
        //$.sexyCombo.selectOption("#op_category", strOption);
    }

    function setSum(sum){
        _oldSum = Math.abs(sum);
        $('#op_amount').val(_oldSum);
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        setCategory: setCategory,
        setSum: setSum
    };
}(); // execute anonymous function to immediatly return object