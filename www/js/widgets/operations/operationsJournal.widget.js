/**
 * @desc Operations Journal Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.operationsJournal = function(){
    // private constants

    // private variables
    var _model = null;

    var _account = '';
    var _category = '';
    var _dateFrom = '';
    var _dateTo = '';

    var _$node = null;

    var _journal = null;

    var _$txtDateFrom = null;
    var _$txtDateTo = null;
    
    var _$dialogFilterAccount = null;

    // private functions
    function _showInfo(data) {        
        if (data == null)
            return;

        _journal = data;

        var tr, tp;
        tr = '';
        
        // Собираем данные для заполнения в таблицу
        for(var v in data) {
            if (data[v].tr_id > 0) {
                tp = 'Перевод';
            }else if (data[v].virt == 1) {
                tp = 'Фин.цель';
            } else {
                if (data[v].drain == 1) {
                    tp = 'Расход';
                } else {
                    tp = 'Доход';
                }
            }
            
            tr += "<tr id='op" + data[v].id + "' value='"+data[v].id+"'><td class='check'><input type='checkbox' /></td>"
                + '<td class="light"><a href="#">' + tp + '</a></td>'

                if (data[v].transfer != $('#op_account :selected').val() && data[v].transfer != 0){
                    tr += '<td class="summ"><span><b>'+formatCurrency(-data[v].money)+'</b></span></td>'
                } else {
                    // если перевод осуществляется между счетами с разными валютами,
                    // то в переменной imp_id хранится сумма в валюте целевого счёта
                    if (data[v].imp_id == null)
                        tr += '<td class="summ"><span><b>'+formatCurrency(data[v].money)+'</b></span></td>'
                    else
                        tr += '<td class="summ"><span><b>'+formatCurrency(data[v].imp_id)+'</b></span></td>'
                }

                tr += '<td class="light"><span>'+data[v].date+'</span></td>'
                + '<td class="big"><span>'+ ((data[v].cat_name == null)? '' : data[v].cat_name) +'</span></td>'
                + '<td class="no_over big">'+data[v].account_name
                    +'<div class="cont" style="top: -10px"><span>'+'</span><ul>'
                    +'<li class="edit"><a title="Редактировать">Редактировать</a></li>'
                    +'<li class="del"><a title="Удалить">Удалить</a></li>'
                    +'<li class="add"><a title="Копировать">Копировать</a></li>'
                    +'</ul></div>'
                +'</td></tr>';
        }
        // Очищаем таблицу
        $('#operations_list').find('tr:gt(0)').remove();
        
        // Заполняем таблицу
        $('#operations_list').append(tr);
    }

    function _deleteChecked(){
        if (!confirm("Вы действительно хотите удалить выбранные операции?"))
            return false;

        var ids = [];
        var key = 0;
        var $trs = $('#operations_list tr .check input:checked').closest('tr');
        $trs.each(function(){
            ids[key] =$(this).attr('value');
            key++;
        });

        _model.deleteOperationsById(ids, function(data) {
            // remove rows from table
            $trs.remove();
            $.jGrowl("Операции удалены", {theme: 'green'});
        });

        return true;
    }

    function _deleteOperation(id){
        if (!confirm("Вы действительно хотите удалить эту операцию?"))
            return false;

        _model.deleteOperationsById([id], function(data) {
            // remove row from table
            $('#operations_list tr[id="op' + id + '"]').remove();
            $.jGrowl("Операция удалена", {theme: 'green'});
        });

        return true;
    }

    function _editOperation(){
        var operation = $(this).parent().attr('class');
        if (operation == 'edit') {
            fillForm(_journal[$(this).closest('tr').attr('value')]);
            if ($('#op_comment').val() == "Начальный остаток"){
                $('#op_amount').attr('disabled', 'disabled');
                $('#op_comment').attr('disabled', 'disabled');
            } else {
                $('#op_amount').removeAttr('disabled');
                $('#op_comment').removeAttr('disabled');
            }

            $('form').attr('action','/operation/edit/');
        } else if(operation == 'del') {
            _deleteOperation($(this).closest('tr').attr('value'));
            //if (_journal[$(this).closest('tr').attr('value')].virt == "1"){
            //    deleteTarget($(this).closest('tr').attr('value'), $(this).closest('tr'));
            //} else {
            //    deleteOperation($(this).closest('tr').attr('value'), $(this).closest('tr'));
            //}
        } else if(operation == 'add') {
            // @todo: kick this out!
            fillForm(_journal[$(this).closest('tr').attr('value')]);
            $(this).closest('form').attr('action','/operation/add/');
            $('#date').datepicker('setDate', new Date() );
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

        _$txtDateFrom = $('#dateFrom');
        _$txtDateTo = $('#dateTo');

        _dateFrom = _$txtDateFrom.val();
        _dateTo = _$txtDateTo.val();

        //loadJournal();

        $('#btn_ReloadData').click(loadJournal);
        $('#remove_all_op').click(_deleteChecked);

        $('#operations_list th input').change(function(){
            if($(this).attr('checked'))
                $('#operations_list .check input').attr('checked','checked');
            else
                $('#operations_list .check input').removeAttr('checked');
        })

        // Биндим щелчки на строках и кнопках тулбокса (править, удалить, копировать)
        $('#operations_list tr').live('dblclick',function(){
            $(this).find('li.edit a').click();
        })

        $('#operations_list a').live('click', _editOperation);

        // show selection
        $('tr:not(:first)','#operations_list').live('mouseover',function(){
            $('#operations_list tr').removeClass('act').find('.cont ul').hide();
            $(this).closest('tr').addClass('act').find('.cont ul').show();
        });

        // hide selection
        $('.mid').mousemove(function(){
            if (!$('ul:hover').length && !$('.act:hover').length)
                $('#operations_list tr').removeClass('act').find('.cont ul').hide();
        });

        // настраиваем диалоги фильтров
        _$dialogFilterAccount = $('#dialogFilterAccount').dialog({title: "Выберите счёт", autoOpen: false});
        _$dialogFilterAccount.find('a').click(function(){
            _account = $(this).attr('value');
            loadJournal();
            _$dialogFilterAccount.dialog('close');

            return false;
        });
        $('#btnFilterAccount').click(function(){_$dialogFilterAccount.dialog('open')});

        return this;
    }

    function setAccount(account) {
        _account = account;
    }

    function setCategory(cat) {
        _category = cat;
    }

    function setDateFrom(dateStr) {
        _$txtDateFrom.val(dateStr);
        _dateFrom = dateStr;
    }

    function setDateTo(dateStr) {
        _$txtDateTo.val(dateStr);
        _dateTo = dateStr;
    }

    function loadJournal() {
        _dateFrom = $('#dateFrom').val();
        _dateTo = $('#dateTo').val();

        _model.loadJournal(_account, _category, _dateFrom, _dateTo, _showInfo);
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        setAccount: setAccount,
        setCategory: setCategory,
        setDateFrom: setDateFrom,
        setDateTo: setDateTo,
        loadJournal: loadJournal
    };
}(); // execute anonymous function to immediatly return object

$(document).ready(function() {
    easyFinance.widgets.operationsJournal.init('.operation_list', easyFinance.models.operation);
});