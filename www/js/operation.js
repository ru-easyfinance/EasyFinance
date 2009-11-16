// {* $Id: operation.js 137 2009-08-10 16:00:50Z ukko $ *}
    /*var catlast ;
    var datelast ;*/

/**
 * Функция очищает форму ввода операции
 */
function clearForm() {
    $('#op_type,#op_category,#op_target').val(0);
    $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags,#op_date').val('');

    $('span#op_amount_target').text();

    $('span#op_amount_done').text();
    $('span#op_forecast_done').text();
    $('span#op_percent_done').text();

    $('#op_close').removeAttr('checked');

    $('form').attr('action','/operation/add/');

    $('#op_type').change();
}

/**
 * Функция заполняет форму данными c массива
 * @param data данные для заполнения
 */
function fillForm(data) {
    //clearForm();

    $('#op_id').val(data.id);
    $('#op_account').val(data.account_id);

    easyFinance.widgets.operationEdit.setCategory(data.cat_id);
    easyFinance.widgets.operationEdit.setSum(Math.abs(data.money));

    if (data.tr_id=='1') {
        // transfer
        $('#op_type').val(2);
    } else {
        if (data.virt=='1') {
            $('#op_type').val(4);
        } else {
            if (data.drain=='1') {
                $('#op_type').val(0);
            } else {
                $('#op_type').val(1);//@todo
            }
        }
    }

    //////////////////////////
    //$('#target').val(data.);
    //$('#close').val(data.);
    $('#op_AccountForTransfer').val(data.transfer);
    $('#op_date').val(data.date);
    if (data.tags)
        $('#op_tags').val(data.tags);
    else
        $('#op_tags').val('');
    $('#op_comment').val(data.comment);
    $('#op_type').change();
    $(document).scrollTop(300);
}

$(document).ready(function() {
    var journalReload = function(){
        easyFinance.widgets.operationsJournal.setAccount($('#op_account :selected').val());
        $('#btn_ReloadData').click();
    }

    $('#op_btn_Save').click(journalReload);
    $('#op_account').change(journalReload);

    // загружаем журнал транзакций
    // по умолчанию показываются операции по всем счетам
    // easyFinance.widgets.operationsJournal.setAccount($('#op_account :selected').val());
    easyFinance.widgets.operationsJournal.loadJournal();
});