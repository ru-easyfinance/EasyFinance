// {* $Id$ *}
$(function() {
    // Init
    $('#amount').calculator({
        layout: [
                $.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
                'MR_7_8_9_-' + $.calculator.UNDO,
                'MS_4_5_6_*' + $.calculator.PERCENT ,
                'M+_1_2_3_/' + $.calculator.HALF_SPACE,
                'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS],
        showOn: 'opbutton',
        buttonImageOnly: true,
        buttonImage: '/img/calculator.png'
    });
    $("#date, #dateFrom, #dateTo").datepicker({dateFormat: 'dd.mm.yy'});
    
    // Bind
    $('#btn_EditAccount').click(function(){ location.href="/accounts/edit/"+$('#account :selected').val() }); // @FIXME
    $('#btn_Save').click(function(){ addOperation(); })
    $('#btn_Cancel').click(function(){ operationAddInVisible(); });
    $('#btn_ReloadData').click(function(){ loadOperationList(); });
    $('#amount,#currency').change(function(){
        if ($('#type').val() == 2) {
            //@TODO Дописать округление
            var result = Math.round($('#amount').val() / $('#currency').val());
            if (!isNaN(result) && result != 'Infinity') {
                $("#convertSumCurrency").html("конвертация: "+result);
            }
        }
    });
    $('#account').change(function(){
        changeAccountForTransfer();
        loadOperationList();
    }); 
    $('#AccountForTransfer').change( function(){ changeAccountForTransfer(); });
    $('#type').change(function(){ changeTypeOperation('add'); });
    $('#target').change(function(){
        $("span.currency").each(function(){
            $(this).text(" "+$("#target :selected").attr("currency"));
        });
        $("#amount_done").text(formatCurrency($("#target :selected").attr("amount_done")));
        $("#amount").text(formatCurrency($("#target :selected").attr("amount")));
        $("#percent_done").text(formatCurrency($("#target :selected").attr("percent_done")));
        $("#forecast_done").text(formatCurrency($("#target_sel :selected").attr("forecast_done")));
    });

    // Autoload
    loadOperationList();

    /**
     * Загружает список всех операций (с фильтром)
     * @return void
     */
    function loadOperationList() {
        $.get('/operation/listOperations/',{
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            category: $('#cat_filtr :selected').val(),
            account: $('#account :selected').val()
        }, function(data) {
            $('div#list').empty();
            if (data != null) {
                for(v in data){
                    tags = (data[v].tags!=null) ? data[v].tags : '';
                    $('div#list').append(
                        "<div value='"+data[v].id+"'><input type='checkbox' />"
                            + ((data[v].drain == 1) ? '<span>Расход</span>' : '<span>Доход</span>')
                            + '<span>'+data[v].money+'</span>'
                            + '<span>'+data[v].date+'</span>'
                            + '<span>'+data[v].cat_name+'</span>'
                            + '<span>'+data[v].account_name+'</span>'
                            + '<span>'+data[v].comment+'</span>'
                            + '<span>'+tags+'</span>'
                    )
                }
            }
            // data could be xmlDoc, jsonObj, html, text, etc...
            this; // the options for this ajax request
        },'json');
        $('input#tags').tagSuggest({
            //@TODO Дописать процедуру загрузки тегов
            tags: ['javascript', 'js2', 'js', 'jquery', 'java']
        });
    }
    
    /**
     * Добавляет новую операцию
     * @return void
     */
    function addOperation() {
        if (!validateForm()){
            return false;
        }
        $.post('/operation/add/', {
            type:      $('#type').val(),
            account:   $('#account').val(),
            category:  $('#category').val(),
            date:      $('#date').val(),
            comment:   $('#comment').val(),
            amount:    $('#amount').val(),
            toAccount: $('#AccountForTransfer').val(),
            currency:  $('#currency').val(),
            target:    $('#target').val(),
            close:     $('#close:checked').length,
            tags:      $('#tags').val()
        }, function(data, textStatus){
            for (var v in data) {
                //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                alert('Ошибка в ' + v);
            }
            // В случае успешного добавления, закрываем диалог и обновляем календарь
            if (data.length == 0) {
                clearForm();
                loadOperationList();
            }
           // data could be xmlDoc, jsonObj, html, text, etc...
           //this; // the options for this ajax request
           // textStatus can be one of:
           //   "timeout"
           //   "error"
           //   "notmodified"
           //   "success"
           //   "parsererror"
        }, 'json');
    }

    /**
     * Проверяет валидность введённых данных
     */
    function validateForm() {
        $error = '';
        if (isNaN(parseFloat($('#amount').val()))){
            alert('Вы ввели неверное значение в поле "сумма"!');
            return false;
        }
        
        if ($('#type') == 4) {
            //@FIXME Написать обновление финцелей
            amount = parseFloat($("#target_sel option:selected").attr("amount")); $("#amount").text(amount);
            amount_done = parseFloat($("#target_sel option:selected").attr("amount_done")); $("#amount_done").text(amount_done);
            if ((amount_done + parseFloat($("#amount").val())) >= amount) {
                if (confirm('Закрыть финансовую цель?')) {
                    $("#close").attr("checked","checked");
                }
            }
        }
        return true;
    }

    /**
     * Очищает форму
     * @return void
     */
    function clearForm() {
        $('#account,#type,#category,#target').val(0);
        $('#amount,#AccountForTransfer,#comment,#tags').val('');
        $('#amount_target,#amount_done,#forecast_done,#percent_done').text('');
        $('#close').removeAttr('checked');
    }
    
    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    function changeAccountForTransfer() {
        //@TODO можно оптимизировать процедуру, и не отсылать данные на сервер, если у нас одинаковая валюта на счетах
        if ($('#type :selected').val() == 2 && 
            $('#account :selected').attr('currency') != $('#AccountForTransfer :selected').attr('currency')) {
                $('#operationTransferCurrency').show();
                $.post('/operation/get_currency/', {
                        SourceId : $("#account").val(),
                        TargetId : $("#AccountForTransfer").val()
                    }, function(data){
                        $('#operationTransferCurrency :first-child').html('Курс <b>'+
                            $('#account :selected').attr('abbr')+'</b> к <b>'+$('#AccountForTransfer :selected').attr('abbr')+'</b>');
                        $('#currency').val(data);
                    }, 'json'
                );
        } else {
            $('#operationTransferCurrency').hide();
        }
    }

    /**
     * При изменении типа операции
     */
    function changeTypeOperation() {
        // Расход или Доход
        if ($('#type').val() == 0 || $('#type').val() == 1) {
            $("#category_fields,#tags_fields").show();
            $("#target_fields,#transfer_fields").hide();
        //Перевод со счёта
        } else if ($('#type').val() == 2) {
            $("#category_fields,#target_fields").hide();
            $("#tags_fields,#transfer_fields").show();
            changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if ($('#type').val() == 4) {
            $("#target_fields").show();
            $("#tags_fields,#transfer_fields,#category_fields").hide();
            changeTarget();
            changeTargetEdit();
        }
    }

});

function onPosSelect() {
    var el = $('#e_pos-box');
    if(el.attr('checked')) {
        if(document.getElementById("pos_oc")) {
            setTimeout("redirectPOS()", 1800);
        }
    }
    return true;
}

function redirectPOS() {
    window.location.href='http://www.home-money.ru/index.php?modules=e-pos&sum='+$('#POS_SUM').html();
}

function updateOperation() {
    onSumChangeEdit();
    var type = document.getElementById("type_edit").value;
    var objSel = document.getElementById("selectAccount");
    var bill_id = objSel.options[objSel.selectedIndex].value;
    var objSelCat = document.getElementById("cat_id_old_edit");
    var cat_id = objSelCat.options[objSelCat.selectedIndex].value;
    var sum = document.getElementById("pos_mc_edit").value;
    var dateTo = document.getElementById("sel1").value;
    var comment = document.getElementById("comment_edit").value;
    var toAccount = document.getElementById("selectAccountForTransferEdit").value;
    if (document.getElementById("currency_edit"))
    {
        var currency = document.getElementById("currency_edit").value;
    }
    var m_id = document.getElementById("m_id").value;
    var modules = "operation";
    var action = "updateOperation";
    if (type == '2'){
        action = "updateTransfer";
    }else if (type == '4') {
        action = "updateTargetOperation";
        if (!checkTarget('edit')){
            return false;
        }
    }
    target_sel = $("#target_sel_ed option:selected").val();
    if (sum == 0 || sum == 'NaN'){
        alert('Вы ввели неверное значение в поле "сумма"!');return false;
    }
    if (!validateForm()){
        return false;
    }
    close = $("#close_ed").attr('checked')?1:0;
    //alert(type+'-'+action+'-'+m_id+'-'+currency+'-'+toAccount);
    //return false;
    $.get('/index.php',{
        modules:modules,
        action:action,
        id:m_id,
        a:bill_id,
        cat_id:cat_id,
        dateTo:dateTo,
        type:type,
        comment:comment,
        sum:sum,
        toAccount:toAccount,
        currency:currency,
        target_id:target_sel,
        close:close
    },changeOperationList);
    visibleMessage('Операция редактируется, подождите несколько секунд...');
}

function onSumConvert(type) {
    if (document.getElementById("type_"+type).value == 2)
    {
        var currency = document.getElementById("currency_"+type).value;
        if (type == 'edit')
        {
            var sum = document.getElementById("pos_mc_edit").value;
        }else{
            var sum = document.getElementById("pos_mc").value;
        }
        result = round(sum/currency,2);
        if (result != 'NaN' || result != 'Infinity')
        {
            $("#convertSumCurrency_"+type).html("конвертация: "+result);
        }
    }
}

function visibleMessage(data) {
    $("#goodOperation").html(data);
    $("#goodOperation").show();
}

function operationAddVisible() {
    changeTypeOperation('add');
    $("#addOperation").show();$("#editOperation").hide();
}

function operationAddInVisible() {
    $("#addOperation").hide();$("#editOperation").hide();
}

function operationAfterInsert() {
    document.getElementById("comment").value = '';
    document.getElementById("pos_oc").value = '';
    changeOperationList();
}

function deleteOperation(id) {
    if (!confirm("Вы действительно хотите удалить эту запись?")) {
        return false;
    }
    $("#addOperation").hide();$("#editOperation").hide();
    var modules = "operation";
    var action = "deleteOperation";
    $.get('/index.php',{
        modules:modules,
        action : action,
        id: id,
        tr_id:0
    },changeOperationList);
    visibleMessage('Операция удаляется, подождите несколько секунд...');
}

function deleteTargetOperation(id, tr_id) {
    if (!confirm("Вы действительно хотите удалить эту запись?")) {
        return false;
    }
    $("#addOperation").hide();$("#editOperation").hide();
    var modules = "operation";
    var action = "deleteTargetOperation";
    $.get('/index.php',{
        modules:modules,
        action : action,
        id: id,
        tr_id:0
    },changeOperationList);
    visibleMessage('Операция удаляется, подождите несколько секунд...');
}

function deleteOperationTransfer(id) {
    if (!confirm("Вы действительно хотите удалить этот перевод?")) {
        return false;
    }
    $("#addOperation").hide();$("#editOperation").hide();
    var modules = "operation";
    var action = "deleteOperation";
    $.get('/index.php',{
        modules: modules,
        action : action,
        id: 0,
        tr_id:id
    },changeOperationList);
    visibleMessage('Перевод удаляется, подождите несколько секунд...');
}

function editOperation(id) {
    var modules = "operation";
    var action = "editOperation";
    $.get('/index.php',{
        modules:modules,
        action:action,
        id:id
    },operationAfterEdit);
}

function editTargetOperation(id) {
    var modules = "operation";
    var action = "editTargetOperation";
    $.get('/index.php',{
        modules:modules,
        action:action,
        id:id
    },operationAfterEdit);
}

function operationAfterEdit(data) {
    $("#addOperation").hide();
    $("#editOperation").html(data);
    $("#editOperation").show();
    $("#editOperation").show();
    changeTypeOperation('edit');
    scrollTo(0,0);
}



function changeTarget() {
    $("span.currency").each(function(){
        $(this).text(" "+$("#target_sel_ed option:selected").attr("currency"));
    });
    $("#amount_done").text(formatCurrency($("#target_sel option:selected").attr("amount_done")));
    $("#amount").text(formatCurrency($("#target_sel option:selected").attr("amount")));
    $("#percent_done").text(parseInt($("#target_sel option:selected").attr("percent_done")));
    $("#forecast_done").text(parseInt($("#target_sel option:selected").attr("forecast_done")));
}

function changeTargetEdit() {

}

function checkTarget(type) {
    $error = '';
    if (type == 'add') {
        amount_now = parseFloat($("#pos_oc").val());
        amount = parseFloat($("#target_sel option:selected").attr("amount")); $("#amount").text(amount);
        amount_done = parseFloat($("#target_sel option:selected").attr("amount_done")); $("#amount_done").text(amount_done);
        if ((amount_done + amount_now) >= amount) {
            if (confirm('Закрыть финансовую цель?')) {
                $("#close").attr("checked","checked");
            }
        }
    } else if (type == 'edit') {
        amount_now = parseFloat($("#pos_oc_edit").val());
        amount_db = parseFloat($("#pos_mc_edit").val());
        amount = parseFloat($("#target_sel_ed option:selected").attr("amount")); $("#amount_ed").text(amount);
        amount_done = parseFloat($("#target_sel_ed option:selected").attr("amount_done")); $("#amount_done_ed").text(amount_done);
        if (((amount_done - amount_db) + amount_now) >= amount) {
            if (confirm('Закрыть финансовую цель?')) {
                $("#close_ed").attr("checked","checked");
            }
        }
    } else {
        return false;
    }
    return true;
}

function formatCurrency(num) {
    if (num=='undefined') num = 0;
    //num = num.toString().replace(/\$|\,/g,'');
    if(isNaN(num)) num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
        cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
        num = num.substring(0,num.length-(4*i+3))+' '+
        num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + '' + num + '.' + cents);
}