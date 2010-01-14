/**
 * @desc Operations Journal Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.operationsJournal = function(){
    // private constants

    // private variables
    var _modelAccounts = null;

    var _account = '';
    var _accountName = '';
    var _category = '';
    var _categoryName = '';
    var _type = '';
    var _sumFrom = '';
    var _sumTo = '';
    var _dateFrom = '';
    var _dateTo = '';

    var _$node = null;

    var _journal = null;

    var _$txtDateFrom = null;
    var _$txtDateTo = null;

    var _$dialogFilterType = null;
    var _$dialogFilterSum = null;
    var _$dialogFilterAccount = null;
    var _$dialogFilterCategory = null;

    var _sexyCategoryInitialized = false;

    // private functions
    //
    // форматирует и выводит таблицу с данными
    // данные получаются из модели методом loadJournal
    // и передаются в эту функцию
    function _showInfo(data) {
        if (data == null)
            return;

        _journal = data;

        var tr, tp, pageTotal, curMoney;
        tr = '';
        pageTotal = 0;
        
        // Собираем данные для заполнения в таблицу
        for(var v in data) {
            // см. тикет #357
            if (data[v].account_name == null)
                continue;

            if (  data[v].transfer > 0 ) {
                tp = 'Перевод';
            } else if (data[v].virt == 1) {
                tp = 'Фин.цель';
            } else {
                if (data[v].drain == 1) {
                    tp = 'Расход';
                } else {
                    tp = 'Доход';
                }
            }

            // не учитываем в балансе и итогах операции перевода
            if (data[v].transfer > 0)
                curMoney = 0;
            else
                curMoney = parseFloat(data[v].money * res.currency[data[v].account_currency_id].cost);

            pageTotal = pageTotal + curMoney;

            tr += "<tr id='op" + (data[v].virt ? 'v' : 'r') + data[v].id 
                + "' value='" + data[v].id
                + "' moneyCur='" + curMoney.toString()
                + "' trId='" + data[v].tr_id 
                + "'>"
                    + "<td class='check'>"
                    + "<input type='checkbox' /></td>"
                    + '<td class="light"><a href="#">' + tp + '</a></td>';

                // @fixme: отвалился перевод в связи с изменением журнала счетов $('#op_account :selected').val()
                //if (data[v].transfer != _account && data[v].transfer != 0){
                //    tr += '<td class="summ '+ (-data[v].money>=0 ? 'sumGreen' : 'sumRed') +'"><span><b>'+formatCurrency(-data[v].money)+'</b></span></td>'
                //} else {
                    // если перевод осуществляется между счетами с разными валютами,
                    // то в переменной imp_id хранится сумма в валюте целевого счёта
                    if (data[v].imp_id == null)
                        tr += '<td class="summ ' + (data[v].money>=0 ? 'sumGreen' : 'sumRed') + '"><span><b>'+formatCurrency(data[v].money)+'</b></span></td>'
                    else
                        tr += '<td class="summ ' + (data[v].money>=0 ? 'sumGreen' : 'sumRed') + '"><span><b>'+formatCurrency(data[v].imp_id)+'</b></span></td>'
                //}

                tr += '<td class="light"><span>'+data[v].date+'</span></td>'
                + '<td class="big"><span>'+ ((data[v].cat_name == null)? '' : data[v].cat_name) +'</span></td>'
                + '<td class="big">'+ (data[v].account_name ? data[v].account_name : '&nbsp;')
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

        // Выводим итоги по счёту/странице
        pageTotal = Math.round(pageTotal*100)/100;
        if (_account != '') {            
            $('#lblOperationsJournalAccountBalance')
                .html('<b>Остаток по счёту: </b>' + formatCurrency(_modelAccounts.getAccountBalanceTotal(_account)) +' руб.')
                .show();
        }

        $('#lblOperationsJournalSum').html('<b>Баланс операций: </b>' + formatCurrency(pageTotal) + ' руб.<br>').show();
    }

    function _deleteChecked(){
        if (!confirm("Вы действительно хотите удалить выбранные операции?"))
            return false;

        var _ops = [];
        var ids = []
        var virts = [];
        var key = 0;
        var $trs = $('#operations_list tr .check input:checked').closest('tr');
        $trs.each(function(){
            ids[key] = $(this).attr('value')
            _ops[key] = $.extend(true, {}, _journal[ids[key]]);
            key++;
        });

        _modelAccounts.deleteOperationsByIds(ids, virts, function(data) {
            // remove rows from table
            for (var key in _ops) {
                _deleteOperationFromTable(_ops[key]);
            }
            
            _onCheckClicked();
            $.jGrowl("Операции удалены", {theme: 'green'});
        });

        return true;
    }

    function _deleteOperation(id){
        if (!confirm("Вы действительно хотите удалить эту операцию?"))
            return false;

        var _op = $.extend(true, {}, _journal[id]);

        _modelAccounts.deleteOperationsByIds([id], [_journal[id].virt], function(data) {
            _deleteOperationFromTable(_op);

            $.jGrowl("Операция удалена", {theme: 'green'});

            _recalcTotal();
        });

        return true;
    }

    function _deleteOperationFromTable(op) {
        var opVirt = op.virt ? 'v' : 'r';
        var opTransferId = op.tr_id;
        var opId = op.id;

        // remove row from table
        $('#operations_list tr[id="op' + opVirt + opId + '"]').remove();

        // remove paired operation if it's a transfer
        if (opTransferId != null) {
            if (opTransferId == "0")
                $('#operations_list tr[trid="' + opId + '"]').remove();
            else
                $('#operations_list tr[id="op' + opVirt + opTransferId + '"]').remove();
        }
    }

    function _editOperation(){
        $(".op_addoperation").show();
        var operation = $(this).parent().attr('class');
        if (operation == 'edit') {
            $("#op_addoperation_but").addClass('act');
            $(".op_addoperation").show();

            easyFinance.widgets.operationEdit.fillForm(_journal[$(this).closest('tr').attr('value')]);
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
            
            easyFinance.widgets.operationEdit.fillForm(_journal[$(this).closest('tr').attr('value')]);
            $(this).closest('form').attr('action','/operation/add/');
            $('#date').datepicker('setDate', new Date() );
        }

        return false;
    }

    function _onCheckClicked() {
        var $row = $(this).parent().parent();
        var id = $row.attr('value');
        var trid = $row.attr('trid');

        // auto-check paired transfer operations
        var $pair = null;
        if (trid != "null") {
            if (trid == "0")
                $pair = $('#operations_list tr[trid="' + id + '"] input');
            else
                $pair = $('#operations_list tr[id="opv' + trid + '"] input');

            if ($pair) {
                if ($(this).attr('checked'))
                    $pair.attr('checked', 'checked');
                else
                    $pair.removeAttr('checked');
            }
        }

        // show/hide 'remove checked' link
        if (_$node.find('table input').is(':checked'))
            $('#remove_all_op').show();
        else
            $('#remove_all_op').hide();
    }

    function _sexyFilter (input, text){
        if (this.wrapper.data("sc:lastEvent") == "click")
            return true;

        if (text.toLowerCase().indexOf(input.toLowerCase()) != -1)
            return true;
        else
            return false;
    }

    function _initFilters() {
        // сброс фильтров
        $('#linkOperationsJournalClearFilters').click(function(){
            _type = '';
            _sumFrom = '';
            _sumTo = '';
            _category = '';
            _account = '';
            _accountName = '';

            $('#lblOperationsJournalAccountBalance').hide();

            loadJournal();
        });

        // фильтр по типу операции
        _$dialogFilterType = $('#dialogFilterType').dialog({title: "Выберите тип операции", autoOpen: false, width: "420px"});
        _$dialogFilterType.find('a').click(function(){
            _type = $(this).attr('value');
            loadJournal();
            _$dialogFilterType.dialog('close');

            return false;
        });
        $('#btnFilterType').click(function(){_$dialogFilterType.dialog('open');});

        // фильтр по сумме
        _$dialogFilterSum = $('#dialogFilterSum').dialog({title: "Выберите сумму", autoOpen: false, width: "460px"});
        _$dialogFilterSum.find('input[type=text]').live('keyup',function(e){
            FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
        });
        _$dialogFilterSum.find('a').click(function(){
            // убираем фильтр
            _$dialogFilterSum.find('#txtFilterSumFrom').val('');
            _$dialogFilterSum.find('#txtFilterSumTo').val('');
            _sumFrom = '';
            _sumTo = '';
            loadJournal();
            _$dialogFilterSum.dialog('close');

            return false;
        });
        _$dialogFilterSum.find('#btnFilterSumSave').click(function(e){
            // ставим фильтр
            _sumFrom = Math.abs(tofloat(_$dialogFilterSum.find('#txtFilterSumFrom').val()));
            _sumTo = Math.abs(tofloat(_$dialogFilterSum.find('#txtFilterSumTo').val()));
            loadJournal();
            _$dialogFilterSum.dialog('close');

            return false;
        });
        $('#btnFilterSum').click(function(){_$dialogFilterSum.dialog('open');});

        // фильтр по категории
        // заполняем диалог ссылками на доступные категории
        _$dialogFilterCategory = $('#dialogFilterCategory').dialog({title: "Выберите категорию", autoOpen: false, width: "420px"});
        _$dialogFilterCategory.find('#btnFilterCategorySave').click(function(){
            var $combo = $('#selectFilterCategory');
            _category = $combo.attr('value');
            _categoryName = $combo.find('option:selected').text();
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
        $('#btnFilterCategory').click(function(){
            _$dialogFilterCategory.dialog('open');
            if (!_sexyCategoryInitialized) {
                _sexyCategoryInitialized = true;
                $("#selectFilterCategory").sexyCombo({
                    filterFn: _sexyFilter
                });
            }
        });

        // фильтр по счёту
        _$dialogFilterAccount = $('#dialogFilterAccount').dialog({title: "Выберите счёт", autoOpen: false});
        _$dialogFilterAccount.find('a').click(function(){
            $('#lblOperationsJournalAccountBalance').hide();
            $('#lblOperationsJournalSum').hide();

            _account = $(this).attr('value');
            _accountName = $(this).text();
            loadJournal();
            _$dialogFilterAccount.dialog('close');

            return false;
        });
        $('#btnFilterAccount').click(function(){_$dialogFilterAccount.dialog('open');});
    }

    function _printFilters(){
        var txt = '';
        var strCat = '';

        if (_type != '')
            txt = txt + 'операции: ' + ['доход', 'расход', 'перевод', '', 'фин. цель'][_type];

        if (_type != '' && _category != '')
            txt = txt + ', ';

        if (_category != '')
            txt = txt + 'категория: ' + _categoryName;

        if ((_type != '' || _category != '') && (_sumFrom != '' || _sumTo != ''))
            txt = txt + ', ';

        if (_sumFrom != '' || _sumTo != '')
            txt = txt + 'сумма: ';

        if (_sumFrom != '')
            txt = txt + 'от ' + _sumFrom;

        if (_sumFrom != '' && _sumTo != '')
            txt = txt + ' ';

        if (_sumTo != '')
            txt = txt + 'до ' + _sumTo;

        if (_sumFrom != '' || _sumTo != '')
            txt = txt + ' руб';

        if (txt != '' && _accountName != '')
            txt = txt + ', ';

        if (_account != '')
            txt = txt + 'счёт: ' + _accountName;

        if (txt == '') {
            _$node.find('#divOperationsJournalFilters').hide();
        } else {
            _$node.find('#lblOperationsJournalFilters').text(txt).parent().show();
        }
    }

    function _recalcTotal() {
        // recalc total
        var pageTotal = 0;
        var rows = $('#operations_list tr');
        for (var i=0; i<rows.length; i++) {
            pageTotal = pageTotal + parseFloat($(rows[i]).attr('moneycur'));
        }
        pageTotal = Math.round(pageTotal*100)/100;
        $('#lblOperationsJournalSum').html('<b>Баланс операций: </b>' + pageTotal + ' руб.<br>').show();
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

        _modelAccounts = model;

        _$txtDateFrom = $('#dateFrom');
        _$txtDateTo = $('#dateTo');

        _dateFrom = _$txtDateFrom.val();
        _dateTo = _$txtDateTo.val();

        $("#dateFrom, #dateTo").datepicker({dateFormat: 'dd.mm.yy'});

        $('#btn_ReloadData').click(loadJournal);
        $(document).bind('operationAdded', loadJournal);
        $(document).bind('operationEdited', loadJournal);
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

        if (_modelAccounts)
            _accountName = _modelAccounts.getAccountNameById(account);

        if (easyFinance.widgets.operationEdit && account != '')
            easyFinance.widgets.operationEdit.setAccount(account);
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
        _printFilters();

        _dateFrom = $('#dateFrom').val();
        _dateTo = $('#dateTo').val();

        _modelAccounts.loadJournal(_account, _category, _dateFrom, _dateTo, _sumFrom, _sumTo, _type, _showInfo);
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
