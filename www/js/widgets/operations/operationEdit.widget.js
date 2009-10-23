/**
 * @desc Add/Edit Operation Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.operationEdit = function(){
    // private constants

    // private variables
    var _model = null;

    var _$node = null;

    // private functions
     /**
     * Получает список тегов
     */
    function _initTags() {
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

    function _initForm(){
        $('#op_btn_Save').click(function(){_saveOperation();return false;})
        $('#op_btn_Cancel').click(function(){_clearForm();
            $(".op_addoperation").hide();
            return false;});

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
        $("#op_date").datepicker();

        $('#op_amount,#op_currency').change(function(){
            if ($('#op_type').val() == 2) {
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

        $('#op_account').change(function(){_changeAccountForTransfer();});
        $('#op_AccountForTransfer').change( function(){_changeAccountForTransfer();});
        $('#op_type').change(function(){
            //createDynamicDropdown('op_type', 'op_category');

            _changeTypeOperation('add');
        });

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

    /**
     * При изменении типа операции
     */
    function _changeTypeOperation() {
        // Расход или Доход
        if ($('#op_type').val() == 0 || $('#op_type').val() == 1) {
            $("#op_category_fields,#op_tags_fields").show();
            $("#op_target_fields,#op_transfer_fields").hide();
            if ($('#op_type').val() == 1)
                    $.post('/category/cattypechange/',{
                        type : 1
                    },function(data){
                        $("#op_category").html(data);
                    },'json');
            if ($('#op_type').val() == 0)
                $.post('/category/cattypechange/',{
                        type : -1
                    },function(data){
                        $("#op_category").html(data);
                    },'json');
                //toggleVisibleCategory($('#op_category'),-1);//отображает в списке категорий для добавления операции доходные
        //Перевод со счёта
        } else if ($('#op_type').val() == 2) {
            $("#op_category_fields,#op_target_fields").hide();
            $("#op_tags_fields,#op_transfer_fields").show();
            _changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if ($('#op_type').val() == 4) {
            $('#op_target').remove('option :not(:first)');
            var o = '';
            var t;
            for (var v in res['user_targets']) {
                t = res['user_targets'][v];
                o += '<option value="'+v+'" target_account_id="'+t['account']+'" amount_done="'+t['amount_done']+
                    '"percent_done="'+t['percent_done']+'" forecast_done="'+t['forecast_done']+'" amount="'+t['money']+'">'+t['title']+'</option>';
            }
            $("#op_target_fields").show();
            $("#op_tags_fields,#op_transfer_fields,#op_category_fields").hide();
            $('#op_target').html(o);
            $('#op_target').change();
        }
    }

    /**
     * Добавляет новую операцию
     * @return void
     */
    function _saveOperation() {
        if (!_validateForm()){
            return false;
        }
        $.jGrowl("Операция сохраняется", {theme: 'green'});

        easyFinance.models.operation.editOperationById(
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
            $('#op_close:checked').length,
            $('#op_tags').val(),

            function(data){
                // В случае успешного добавления, закрываем диалог и обновляем календарь
                if (data.length == 0) {
                    _clearForm();
                    $.jGrowl("Операция успешно сохранена", {theme: 'green'});
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

    /**
     * Проверяет валидность введённых данных
     */
    function _validateForm() {
        $error = '';
        if (isNaN(parseFloat($('#op_amount').val()))){
            $.jGrowl('Вы ввели неверное значение в поле "сумма"!', {theme: 'red', stick: true});
            return false;
        }
        //Запрос подтверждения на выполнение операции в случае ухода в минус.
        var am = tofloat($('#op_amount').val()+'.0');
        var tb = tofloat(res['accounts'][$("#op_account option:selected").val()]['total_balance']);
        //* && $("#op_type option:selected").val() != 1*/)
        if ( (am-tb)>0 && $("#op_type option:selected").val()!=1){
            if (!confirm('Данная транзакция превышает остаток средств на вашем счёте. Продолжить ?'))
            //$.jGrowl('Введённое значение суммы превышает общий остаток средств на данном счёте!!!', {theme: 'red', stick: true});
            return false;
        }//*/

        //alert(res['accounts'][$("#op_account option:selected").val()]['total_balance']);
        //alert(res['accounts'][$("#op_account option:selected").val()]['reserve']);
        //если сумма совершаемой операции превышает сумму доступного остатка(Общий - резерв на финцели)
        // тогда предупреждаем пользователя и в случае согласия снимаем нехватающую часть денег с фин цели.
        if ((am - ( tb- res['accounts'][$("#op_account option:selected").val()]['reserve']))>0) {
            alert ("Введённая сумма операции превышает доступный остаток счёта.\n\
Переведите деньги с финансовой цели и повторите операцию ещё раз!");
        }

        if ($('#op_type').val() == '4') {
            /**
             *@FIXME Написать обновление финцелей
             */
             //alert("tratata");

            //var amount = parseFloat($("#op_target option:selected").attr("amount"));
            //alert(amount);
            //$("#op_amount").val(amount);

            var amount = parseFloat($("#op_target option:selected").attr("amount"));
            var amount_done = parseFloat($("#op_target option:selected").attr("amount_done"));
            $("#op_amount_done").text(amount_done);
            if ((amount_done + parseFloat($("#op_amount").val())) >= amount) {
                if (confirm('Закрыть финансовую цель?')) {
                    $("#op_close").attr("checked","checked");
                }
            }
        }
        return true;
    }

    /**
     * Очищает форму
     * @return void
     */
    function _clearForm() {
        $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags').val('');//#op_date убрал

        $('span#op_amount_target').text();

        $('span#op_amount_done').text();
        $('span#op_forecast_done').text();
        $('span#op_percent_done').text();

        $('#op_close').removeAttr('checked');

        $('form').attr('action','/operation/add/');

        $('#op_type').change();
    }

    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    function _changeAccountForTransfer() {
        if ($('#op_type :selected').val() == 2 &&
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

    // reveal some private things by assigning public pointers
    return {
        init: init
    };
}(); // execute anonymous function to immediatly return object