/**
 * @desc Operations Journal Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.operationsJournal = function(){
    // private constants

    // private variables
    var _modelAccounts = null,
        _account = '',
        _accountName = '',
        _category = '',
        _categoryName = '',
        _type = '-1',
        _sumFrom = '',
        _sumTo = '',
        _dateFrom = '',
        _dateTo = '',
        _search_field = '',
        _$node = null,
        _journal = null,
        _$txtDateFrom = null,
        _$txtDateTo = null,
        _$checkAll = null,
        _$comboAccount = null,
        _$comboCategory = null,
        _$dialogFilterType = null,
        _$dialogFilterSum = null,
        OPERATION_TYPES = ['расход', 'доход', 'перевод', '', 'фин. цель'],
        break_symbol = $.browser.mozilla ? '<wbr/>' : '&shy;',

        rowsCollection = {},
        clearFilterBtn,
        deleteBtn;

    function insertWBR(td_contents) {
        var res = '';
        for (i=0;i<=td_contents.length;i+=5)
            res += td_contents.substr(i, 5) + break_symbol;
        return res;
    }

    // private functions
    //
    // форматирует и выводит таблицу с данными
    // данные получаются из модели методом loadJournal
    // и передаются в эту функцию
    function _showInfo(data) {
        if (!data) return false;

        _journal = data.operations;

        var tr = '',
            tp,
            pageTotal = 0,
            curMoney,
            strMoney,
            prevMoney;

        // Убираем прелоадер
        DataTables.preloader(false);

        //Сбрасываем коллекцию данных перед ее повторным заполнением
        DataTables._clear();

        // заполняем таблицу
        var queue = [null],
            typ;
        $(_journal).each(function(i) {
            if ( this.transfer > 0 ) {
                typ = 'перевод';
                operationType = 'transfer';
            } else if (this.virt == '1') {
                typ = 'перевод на финансовую цель';
                operationType = 'target';
            } else {
                typ = (this.drain == 1) ? 'расход' : 'доход';
                operationType = (this.drain == 1) ? 'outcome' : 'income';
            }

            var comment = (this.tags ? "[" + this.tags + "] " : "") + (this.comment || ""),
                tooltipHtml = '<b>Тип:</b> ' + typ + '<br><b>Счёт:</b> ' + ( res.accounts[this.account_id] ? res.accounts[this.account_id].name : '' ) + ((comment) ? '<br><b>Комментарий:</b> ' : '') + comment.replace("\n", "<br>", "g"),
                rowID = 'op' + ((this.virt == '1') ? 'v' : 'r') + this.id;

            curMoney = (_account == '' && this.transfer > 0) ? 0 : parseFloat(this.money * easyFinance.models.currency.getCurrencyCostById(this.account_currency_id));
            strMoney = (_account == '' && this.virt != "1") ? formatCurrency(this.moneydef, false, true) : formatCurrency(this.money, false, true);

            DataTables._set([
                '<input type="checkbox" />',
                this.date.substr(0, 5),
                '<i class="b-icon operation ' + operationType + '"></i>',
                '<span class="' + (this.money >= 0 ? 'sumGreen' : 'sumRed') + '">' + strMoney + '</span>',
                this.cat_name,
                ((this.tags ? '[' + this.tags + '] ' : '') + (this.comment || '')),
                '<ul class="b-row-menu-block" id="' + rowID + '" trid="' + this.target_id + '"><li><a href="#edit" title="Редактировать"></li><li><a href="#del" title="Удалить"></li><li><a href="#add" title="Добавить"></li></ul>'
            ], true);

            rowsCollection[rowID] = {
                title: tooltipHtml,
                moneyCur: curMoney.toString(),
                trId: this.target_id,
                account: this.account_name,
                value: i
            }
        });

        DataTables.draw();

        if(deleteBtn) deleteBtn.hide();

        $(document).trigger('table.ready');

        // Выводим итоги по счёту/странице
        pageTotal = Math.round(pageTotal * 100) / 100;

        data.period_change = parseFloat(data.period_change);
        data.list_before = parseFloat(data.list_before);

        setSumInDefaultCurrency('#balance_before', data.list_before);
        setSumInDefaultCurrency('#lblOperationsJournalSum', data.period_change);
        setSumInDefaultCurrency('#balance_after', parseFloat(data.list_before) + parseFloat(data.period_change));
    }

    function _deleteChecked(){
        if (!confirm("Вы действительно хотите удалить выбранные операции?"))
            return false;

        var _ops = [],
            ids = [],
            virts = [];

        $('td input:checked', DataTables.table).closest('tr').each(function(key) {
            var id = $(this).find('.b-row-menu-block').attr('id'),
                value = rowsCollection[id].value;

            if (id.indexOf("opv") != -1) {
                virts[key] = _journal[value].id;
            } else {
                ids[key] = _journal[value].id;
            }

            _ops[key] = $.extend(true, {}, _journal[value]);
        });

        _modelAccounts.deleteOperationsByIds(ids, virts, function(data) {
            if (data.result) {
                // remove rows from table
                for (var key in _ops) {
                    _deleteOperationFromTable(_ops[key]);
                }

                _$checkAll.removeAttr('checked');

                $.jGrowl(data.result.text, {theme: 'green'});
            } else if (data.error & data.error.text) {
                $.jGrowl(data.error.text, {theme: 'red'});
            }
        });

        return true;
    }

    function _deleteOperation(id){
        if (!confirm("Вы действительно хотите удалить эту операцию?"))
            return false;

        var _op = $.extend(true, {}, _journal[id]),
            ids = [],
            virts = [];

        if (_op.virt == "1") {
            virts.push(_op.id);
        } else {
            ids.push(_op.id);
        }

        _modelAccounts.deleteOperationsByIds(ids, virts, function(data) {
            if (data.result) {
                _deleteOperationFromTable(_op);

                $.jGrowl(data.result.text, {theme: 'green'});

                _recalcTotal();
            } else if (data.error && data.error.text) {
                $.jGrowl(data.error.text, {theme: 'red'});
            }
        });

        return true;
    }

    function _deleteOperationFromTable(op) {
        var opVirt = (op.virt == '1') ? 'v' : 'r',
            opTransferId = op.tr_id,
            opId = op.id;

        // remove row from table
        DataTables.grid.fnDeleteRow($('tr ul[id="op' + opVirt + opId + '"]', DataTables.table).closest('tr').get(0));

        // remove paired operation if it's a transfer
        if (opTransferId != null) {
            var selector = '';
            if (opTransferId == "0") {
                selector = 'tr ul[trid="' + opId + '"]';
            } else {
                selector = 'tr ul[id="op' + opVirt + opTransferId + '"]';
            }
            DataTables.grid.fnDeleteRow($(selector, DataTables.table).closest('tr').get(0));
        }
    }

    function _editOperation(el, operation) {
        var operation = (operation) ? operation : el.parent().attr('class'),
            rowValue;
        try {
            rowValue = rowsCollection[el.closest('tr').find('.b-row-menu-block').attr('id')].value;
        } catch(e) {
            rowValue = null;
        }
        if(!rowValue && rowValue != 0) return false;
        if (operation == 'edit') {
            easyFinance.widgets.operationEdit.fillForm(_journal[rowValue], true);
        } else if(operation == 'del') {
            _deleteOperation(rowValue);
        } else if(operation == 'add') {
            // при создании копии проставляем текущую дату
            // остальные атрибуты остаются те же
            var copy = $.extend(true, {}, _journal[rowValue]);
            copy.date = new Date();
            easyFinance.widgets.operationEdit.fillForm(copy, false);
        }

        return false;
    }

    function _onCheckClicked(el) {
        var el = $(el),
            elID = el.closest('tr').find('.b-row-menu-block').attr('id'),
            id = _journal[rowsCollection[elID].value].id,
            trid = rowsCollection[elID].trId;

        // auto-check paired transfer operations
        var $pair = null;
        if (trid) {
            if (trid == "0") {
                $pair = $('.b-row-menu-block[trid="' + id + '"]', DataTables.table).closest('tr').find('input');
            } else {
                $pair = $('.b-row-menu-block[id="opv' + trid + '"]', DataTables.table).closest('tr').find('input');
                if (!$pair.length) $pair = $('.b-row-menu-block[id="opr' + trid + '"]', DataTables.table).closest('tr').find('input');
            }

            if ($pair) (el.attr('checked')) ? $pair.attr('checked', 'checked').trigger('click.checked') : $pair.removeAttr('checked').trigger('click.checked');
        }
    }

    /*function _sexyFilter (input, text){
        if (this.wrapper.data("sc:lastEvent") == "click")
            return true;

        if (text.toLowerCase().indexOf(input.toLowerCase()) != -1)
            return true;
        else
            return false;
    }*/

    function _initFilters() {
        // фильтр по типу операции
        $('.type-sort', DataTables.table).unbind().click(function() {
            Tooltip.show({
                selector: '#dialogFilterType',
                el: $(this),
                targetPos: true,
                modal: true,
                callback: function(container) {
                    $('a', container).click(function() {
                        _type = $(this).attr('value');
                        loadJournal();
                        Tooltip.hide(true);
                        return false;
                    });
                }
            });
        });

        // фильтр по сумме
        $('.type-sum', DataTables.table).unbind().click(function() {
            Tooltip.show({
                selector: '#dialogFilterSum',
                el: $(this),
                targetPos: true,
                modal: true,
                callback: function(container) {
                    $('input[type="text"]', container).each(function(e) {
                        FloatFormat(this, String.fromCharCode(e.which) + $(this).val());
                    });
                    $('input[type="text"]:first', container);
                    $('form', container).submit(function(e) {
                        _sumFrom = Math.abs(tofloat($('#txtFilterSumFrom', container).val()));
                        _sumTo = Math.abs(tofloat($('#txtFilterSumTo', container).val()));
                        loadJournal();
                        Tooltip.hide(true);
                        return false;
                    });
                    $('a', container).click(function() {
                        _sumFrom = '';
                        _sumTo = '';
                        loadJournal();
                        Tooltip.hide(true);
                        return false;
                    });
                }
            });
        });

        // фильтр по категории
        _$comboCategory = $('#cat_filtr').change(function() {
            _category = $(this).val();
            loadJournal();
            return false;
        });

        // фильтр по счёту
        _$comboAccount = $('#account_filtr').change(function() {
            var el = $(this);
            _account = el.val();
            _accountName = $('option:selected', el).text();
            loadJournal();
            return false;
        });

        // фильтр по счёту
        $('#grid_search_field').keyup(function() {
            var el = $(this);
            _search_field = el.val();
            return false;
        });
    }

    function _printFilters(){
        var txt = '';

        if (_type != '-1')
            txt += 'Тип операций: ' + OPERATION_TYPES[_type];

        if ((_type != '-1') && (_sumFrom != '' || _sumTo != ''))
            txt += ', ';

        if (_sumFrom != '' || _sumTo != '')
            txt += 'сумма: ';

        if (_sumFrom != '')
            txt += 'от ' + _sumFrom;

        if (_sumFrom != '' && _sumTo != '')
            txt += ' ';

        if (_sumTo != '')
            txt += 'до ' + _sumTo;

        if (_sumFrom != '' || _sumTo != '')
            txt += ' руб';

        if (txt == '') {
            if(clearFilterBtn) clearFilterBtn.hide();
        } else {
            if(!clearFilterBtn) {
                clearFilterBtn = $('<span class="paging_full_numbers"><em>Удалить фильтр:</em> <span class="ui-corner-tl ui-corner-tr ui-corner-bl ui-corner-br fg-button ui-state-default">' + txt + '</span></span>').appendTo($('.fg-toolbar')).click(function() {
                    _type = '-1';
                    _sumFrom = '';
                    _sumTo = '';
                    _category = '';
                    _account = '';
                    _accountName = '';
                    $('#cat_filtr option:first, #account_filtr option:first').attr('selected', true).parent().trigger('change');
                    loadJournal();
                });
            }
            clearFilterBtn.show().find('.fg-button').html(txt);
        }
    }

    function _recalcTotal() {
        // recalc total
        var pageTotal = 0,
            rows = $('tbody tr', DataTables.table);

        for (var i = 0, forlength = rows.length; i < forlength; i++) {
            try {
                pageTotal += parseFloat(rowsCollection[$(rows[i]).find('.b-row-menu-block').attr('id')].moneyCur);
            } catch(e) {  }
        }
        pageTotal = Math.round(pageTotal * 100) / 100;

        $('#lblOperationsJournalSum').html('<b>Баланс: </b>' + pageTotal + ' ' + (_modelAccounts.getAccountCurrency(_account) ? _modelAccounts.getAccountCurrency(_account).text : easyFinance.models.currency.getDefaultCurrencyText())).show();

        if ($.browser.mozilla) {
            break_symbol = "<wbr>";
        } else {
            break_symbol = "&shy;";
        }
        $("#lblOperationsJournalSum").html(insertWBR($("#lblOperationsJournalSum").html()));

        if (pageTotal >= 0) {
            $('#lblOperationsJournalSum').addClass('sumGreen');
        } else {
            $('#lblOperationsJournalSum').addClass('sumRed');
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
        _modelAccounts = model;
        _$txtDateFrom = $('#dateFrom');
        _$txtDateTo = $('#dateTo');
        _dateFrom = _$txtDateFrom.val();
        _dateTo = _$txtDateTo.val();

        $("#dateFrom, #dateTo").datepicker({dateFormat: 'dd.mm.yy'});

        $('#btn_ReloadData').click(loadJournal);

        $('.js-operation-loadcsv').click(loadJournal_CSV);

        // #874. Обновляем данные об остатках на счетах
        // после добавления операции
        //$(document).bind('operationAdded', loadJournal);
        $(document).bind('accountsLoaded', loadJournal);
        $(document).bind('operationEdited', loadJournal);
        $('#remove_all_op').click(_deleteChecked);

        // биндим клик на чекбоксе в заголовке
        _$checkAll = $('th input', DataTables.table);
        _$checkAll.click(function() {
            var el = $(this);
            if(el.attr('checked')) {
                $('td input', DataTables.table).attr('checked', 'checked').trigger('click.checked');
            } else {
                $('td input', DataTables.table).removeAttr('checked').trigger('click.checked');
            }
        });

        $('#dataGrid_paginate span').click(function() {
            _$checkAll.trigger('click');
        });

        $(document).bind('table.ready', function() {
            $('td input', DataTables.table).unbind('click.checked').bind('click.checked', function() {
                var el = $(this);
                if(el.attr('checked') && !$('td input:not(:checked)', DataTables.table).length) {
                    _$checkAll.attr('checked', 'checked');
                } else {
                    _$checkAll.removeAttr('checked');
                }

                if(!deleteBtn) {
                    deleteBtn = $('<span class="paging_full_numbers"><span class="ui-corner-tl ui-corner-tr ui-corner-bl ui-corner-br fg-button ui-state-default">Удалить выделенное</span></span>').appendTo($('.fg-toolbar')).click(function() {
                        _deleteChecked();
                    });
                }

                if($('input:checked', DataTables.table).length) {
                    deleteBtn.show();
                } else {
                    deleteBtn.hide();
                }

                _onCheckClicked(this);
            });

            $('tbody tr', DataTables.table).unbind('mouseover.tooltip, mouseout.tooltip').bind('mouseover.tooltip', function() {
                var id = $(this).find('.b-row-menu-block').attr('id');
                if(rowsCollection[id]) {
                    Tooltip.show({
                        content: rowsCollection[id].title,
                        el: $(this)
                    });
                }
            }).bind('mouseout.tooltip', function() {
                Tooltip.hide();
            });

            $('div[id*="_paginate"]').click(function(e) {
                var el = $(this);
                $('input[type="checkbox"]', DataTables.grid).removeAttr('checked');
                $('.paging_full_numbers:not([id])').hide();
                $(document).trigger('table.ready');
            });
        });

        // Биндим щелчки на строках и кнопках тулбокса (править, удалить, копировать)
        // Для ускорения работы биндим событие на всю таблицу и потом через делегирование событий выбираем target

        DataTables.table.unbind('click.row').bind('click.row', function(e) {
            var target = $(e.target),
                targetTag = target[0].tagName.toLowerCase();

            if(targetTag == 'td') {
                _editOperation(target, 'edit');
            } else if(targetTag == 'a') {
                _editOperation(target, target.attr('href').split('#')[1]);
                return false;
            }
        });

        // настраиваем диалоги фильтров
        _initFilters();

        return this;
    }

    function setAccount(account) {
        if (!account) return false;

        _account = account;
        _$comboAccount.val(_account).trigger('change');

        if (_modelAccounts)
            _accountName = _modelAccounts.getAccountNameById(account);

        if (easyFinance.widgets.operationEdit && account != '')
            easyFinance.widgets.operationEdit.setAccount(account);
    }

    function setCategory(cat) {
        _category = cat;
        _$comboCategory.val(_category).trigger('change');
    }

    function setDateFrom(dateStr) {
        _$txtDateFrom.val(dateStr);
        _dateFrom = dateStr;
    }

    function setDateTo(dateStr) {
        _$txtDateTo.val(dateStr);
        _dateTo = dateStr;
    }

    function loadJournal(asCSV) {
        _printFilters();

        _dateFrom = $('#dateFrom').val();
        _dateTo = $('#dateTo').val();

        // Показываем прелоадер
        DataTables.preloader(true);

        _modelAccounts.loadJournal({
            account: _account,
            category: _category,
            dateFrom: _dateFrom,
            dateTo: _dateTo,
            sumFrom: _sumFrom,
            sumTo: _sumTo,
            type: _type,
            search_field: _search_field
        }, _showInfo, (asCSV == true) ? true : false);
    }

    function loadJournal_CSV() {
        loadJournal(true);
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
    easyFinance.widgets.operationsJournal.init('.b-operations-journal-grid', easyFinance.models.accounts);
});