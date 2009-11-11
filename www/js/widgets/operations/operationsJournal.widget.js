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
    var _$dialogFilterCategory = null;

    // private functions
    //
    // форматирует и выводит таблицу с данными
    // данные получаются из модели методом loadJournal
    // и передаются в эту функцию
    function _showInfo(data) {
        if (data == null)
            return;

        _journal = data;

        var tr, tp;
        tr = '';
        
        // Собираем данные для заполнения в таблицу
        for(var v in data) {
            // см. тикет #357
            if (data[v].account_name == null)
                continue;

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
            
            tr += "<tr id='op" + (data[v].virt ? 'v' : 'r') + data[v].id + "' value='"+data[v].id+"'><td class='check'><input type='checkbox' /></td>"
                + '<td class="light"><a href="#">' + tp + '</a></td>'

                // @fixme: отвалился перевод в связи с изменением журнала счетов $('#op_account :selected').val()
                if (data[v].transfer != _account && data[v].transfer != 0){
                    tr += '<td class="summ '+ (-data[v].money>=0 ? 'sumGreen' : 'sumRed') +'"><span><b>'+formatCurrency(-data[v].money)+'</b></span></td>'
                } else {
                    // если перевод осуществляется между счетами с разными валютами,
                    // то в переменной imp_id хранится сумма в валюте целевого счёта
                    if (data[v].imp_id == null)
                        tr += '<td class="summ ' + (data[v].money>=0 ? 'sumGreen' : 'sumRed') + '"><span><b>'+formatCurrency(data[v].money)+'</b></span></td>'
                    else
                        tr += '<td class="summ ' + (data[v].money>=0 ? 'sumGreen' : 'sumRed') + '"><span><b>'+formatCurrency(data[v].imp_id)+'</b></span></td>'
                }

                tr += '<td class="light"><span>'+data[v].date+'</span></td>'
                + '<td class="big"><span>'+ ((data[v].cat_name == null)? '' : data[v].cat_name) +'</span></td>'
                + '<td class="big">'+data[v].account_name
                    +'<div class="cont" style="top: -17px"><span>'+'</span><ul>'
                    +'<li class="edit"><a title="Редактировать">Редактировать</a></li>'
                    +'<li class="del"><a title="Удалить">Удалить</a></li>'
                    +'<li class="add"><a title="Копировать">Копировать</a></li>'
                    +'</ul></div>'
                +'</td></tr>';
        }
        // Очищаем таблицу
        $('#operations_list').find('tr').remove();
        
        // Заполняем таблицу
        $('#operations_list tbody').append(tr);
    }

    function _deleteChecked(){
        if (!confirm("Вы действительно хотите удалить выбранные операции?"))
            return false;

        var ids = [];
        var virts = [];
        var key = 0;
        var $trs = $('#operations_list tr .check input:checked').closest('tr');
        $trs.each(function(){
            ids[key] =$(this).attr('value');
            virts[key] = _journal[ids[key]].virt;
            key++;
        });

        _model.deleteOperationsByIds(ids, virts, function(data) {
            // remove rows from table
            $trs.remove();
            _onCheckClicked();
            $.jGrowl("Операции удалены", {theme: 'green'});
        });

        return true;
    }

    function _deleteOperation(id){
        if (!confirm("Вы действительно хотите удалить эту операцию?"))
            return false;
        var _opVirt = _journal[id].virt ? 'v' : 'r';

        _model.deleteOperationsByIds([id], [_journal[id].virt], function(data) {
            // remove row from table
            $('#operations_list tr[id="op' + _opVirt + id + '"]').remove();
            $.jGrowl("Операция удалена", {theme: 'green'});
        });

        return true;
    }

    function _editOperation(){
        $(".op_addoperation").show();
        var operation = $(this).parent().attr('class');
        if (operation == 'edit') {
            $("#op_addoperation_but").addClass('act');
            $(".op_addoperation").show();

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
        } else if(operation == 'add') {
            // @todo: kick this out!
            $("#op_addoperation_but").addClass('act');
            $(".op_addoperation").show();
            
            fillForm(_journal[$(this).closest('tr').attr('value')]);
            $(this).closest('form').attr('action','/operation/add/');
            $('#date').datepicker('setDate', new Date() );
        }

        return false;
    }

    function _onCheckClicked() {
        if (_$node.find('table input').is(':checked'))
            $('#remove_all_op').show();
        else
            $('#remove_all_op').hide();
    }

    function _initFilters() {
        // фильтр по категории
        // заполняем диалог ссылками на доступные категории
        _$dialogFilterCategory = $('#dialogFilterCategory').dialog({title: "Выберите категорию", autoOpen: false, width: "420px", height: "80px"});
        $('#selectFilterCategory').change(function(){
            _category = $(this).attr('value');
            loadJournal();
            _$dialogFilterCategory.dialog('close');

            return false;
        });
        _$dialogFilterCategory.find('a').click(function(){
            _category = $(this).attr('value');
            loadJournal();
            _$dialogFilterCategory.dialog('close');

            return false;
        });
        $('#btnFilterCategory').click(function(){_$dialogFilterCategory.dialog('open')});

        // фильтр по счёту
        _$dialogFilterAccount = $('#dialogFilterAccount').dialog({title: "Выберите счёт", autoOpen: false});
        _$dialogFilterAccount.find('a').click(function(){
            _account = $(this).attr('value');
            loadJournal();
            _$dialogFilterAccount.dialog('close');

            return false;
        });
        $('#btnFilterAccount').click(function(){_$dialogFilterAccount.dialog('open')});
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

        $("#dateFrom, #dateTo").datepicker({dateFormat: 'dd.mm.yy'});

        $('#btn_ReloadData').click(loadJournal);
        $('#remove_all_op').click(_deleteChecked);

        // биндим клик на чекбоксе в заголовке
        $('#operations_list_header th input').click(function(){
            if($(this).attr('checked'))
                $('#operations_list .check input').attr('checked','checked');
            else
                $('#operations_list .check input').removeAttr('checked');

            _onCheckClicked();
        })

        // биндим клик на чекбоксы в содержимом
        $('#operations_list input').live('click', _onCheckClicked);

        // Биндим щелчки на строках и кнопках тулбокса (править, удалить, копировать)
        $('.light a').live('click', function(){
            $(this).closest('tr').find('li.edit a').click();
            return false;
        });

        $('#operations_list tr').live('click',function(event){
            if (event.target.type !== 'checkbox') {
                $(this).find('li.edit a').click();
                return false;
            }
        })

        $('#operations_list a').live('click', _editOperation);

        // show selection
        $('tr','#operations_list').live('mouseover',function(){
            $('#operations_list tr').removeClass('act').find('.cont ul').hide();
            $(this).closest('tr').addClass('act').find('.cont ul').show();
        });

        // hide selection
        $('.mid').mousemove(function(){
            if (!$('ul:hover').length && !$('.act:hover').length)
                $('#operations_list tr').removeClass('act').find('.cont ul').hide();
        });

        // настраиваем диалоги фильтров
        _initFilters();
        
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
    easyFinance.widgets.operationsJournal.init('.operation_list', easyFinance.models.accounts);
});