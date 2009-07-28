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
    $('#btn_EditAccount').click(function(){ location.href="/accounts/edit/"+$('#account :selected').val() });
    $('#btn_Save').click(function(){ addOperation(); })
    $('#btn_Cancel').click(function(){ operationAddInVisible(); });
    $('#btn_ReloadData').click(function(){ loadOperationList(); });
    $('#account').change(function(){
        //changeAccountForTransfer();
        loadOperationList();
    });
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

    function loadOperationList() {
        $.get('/operation/listOperations/',{
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            category: $('#category :selected').val(),
            account: $('#account :selected').val()
        },function(data, textStatus){
            alert(this);
            // data could be xmlDoc, jsonObj, html, text, etc...
            this; // the options for this ajax request
        },'json');
        //$('div#list');
    }
});

function addOperation() {
    var type = $('#type_add').val();
    var bill_id = $('#account :selected').val();
    var cat_id = $('#cat_id_old :selected').val();
    var sum = $('#pos_mc').val();
    $('#POS_SUM').html(sum);
    var dateTo = $('#dateTo').val();
    var comment = $('#comment').val();

    var toAccount = '';
    var currency = '';
    var modules = "operation";
    var action = "addOperation";
    target_id = '';

    if (type == '2') {
        toAccount = document.getElementById("selectAccountForTransfer").value;
        currency = document.getElementById("currency_add").value;
        action = "addTransfer";
    } else if (type == '4' || type == '5') {
        //TODO Сделать проверку на валюту
        //if ($("#selectAccount").val())
        action = 'addTargetOperation';
        target_id = $("#target_sel").val();
        close = $("#close_ed").attr('checked')?1:0;
        if (!checkTarget('add')){
            return false;
        }
    }

    if (sum == 0 || sum == 'NaN'){
        alert('Вы ввели неверное значение в поле "сумма"!');return false;
    }

    if (!validateForm()){
        return false;
    }
    $.post('/operation/add/', {
        modules: modules,
        action: action,
        a: bill_id,
        cat_id: cat_id,
        dateTo: dateTo,
        type: type,
        comment: comment,
        sum: sum,
        toAccount: toAccount,
        currency: currency,
        target_id: target_id,
        close: close
    }, function(data, textStatus){
       // data could be xmlDoc, jsonObj, html, text, etc...
       //this; // the options for this ajax request
       // textStatus can be one of:
       //   "timeout"
       //   "error"
       //   "notmodified"
       //   "success"
       //   "parsererror"
    }, 'json');

//        success : operationAfterInsert,
//        complete : onPosSelect

    visibleMessage('Операция добавляется, подождите несколько секунд...');
}
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

function onAjaxSuccess(data) {
    $("#operationDataList").html(data);
    $("#operationDataList").show();
    $("#goodOperation").hide();
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

function changeAccountForTransfer() {
    var id =$("#selectAccountForTransfer").val();
    var currentId = $("#selectAccount").val();

    $.get('/index.php',{
        modules:"operation",
        action:"getCurrency",
        id:id,
        currentId:currentId,
        type:"add"
    },changeTransferCurrency);
}

function changeAccountForTransferEdit() {
    var id =document.getElementById("selectAccountForTransferEdit").value;
    var currentId = document.getElementById("selectAccount").value;

    $.get('/index.php',{
        modules:"operation",
        action:"getCurrency",
        id:id,
        currentId:currentId,
        type:"edit"
    },changeTransferCurrencyEdit);
}

function changeTransferCurrency(data) {
    $("#operationTransferCurrency").html(data);
}

function changeTransferCurrencyEdit(data) {
    $("#operationTransferCurrencyEdit").html(data);
}

function changeTypeOperation(id) {
    switch ($('#type_'+id).val()) {
        case '0': //Расход
            $("#old_cat").show();
            $("#old_cat_edit").show();
            $("#target_fields").hide();
            $("#target_fields_info").hide();
            $("#transferSelect").hide();
            $("#transferSelectEdit").hide();
            break;
        case '1': //Доход
            $("#old_cat").show(); $("#old_cat_edit").show();
            $("#target_fields").hide();
            $("#target_fields_info").hide();
            $("#transferSelect").hide();
            $("#transferSelectEdit").hide();
            break;
        case '2'://Перевод со счёта
            $("#old_cat").hide(); $("#old_cat_edit").hide();
            $("#target_fields").hide();
            $("#target_fields_info").hide();
            $("#transferSelect").show();
            $("#transferSelectEdit").show();
            if (id == 'add')
            {
                changeAccountForTransfer();
            }else{
                changeAccountForTransferEdit();
            }
            break;
        case '3':
            break;
        case '4': //Перевод на финансовую цель
            $("#target_fields").show();
            changeTarget();
            changeTargetEdit();
            $("#target_fields_info").show();
            $("#old_cat").hide();
            $("#transferSelect").hide();
            break;
    }

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