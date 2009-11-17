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
    $.sexyCombo.changeOptions("#op_account", data.account_id);

    easyFinance.widgets.operationEdit.setCategory(data.cat_id);
    easyFinance.widgets.operationEdit.setSum(Math.abs(data.money));

    var typ = 0;
    if (data.tr_id=='1') {
        // transfer
        typ = 2;
    } else {
        if (data.virt=='1') {
            typ = 4;
        } else {
            if (data.drain=='1') {
                typ = 0;
            } else {
                typ = 1; //@todo
            }
        }
    }
    $('#op_type').val(typ);
    $.sexyCombo.changeOptions("#op_type", typ);
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
    // загружаем журнал транзакций
    // по умолчанию показываются операции по всем счетам
    easyFinance.widgets.operationsJournal.setAccount('');
    easyFinance.widgets.operationsJournal.loadJournal();
});