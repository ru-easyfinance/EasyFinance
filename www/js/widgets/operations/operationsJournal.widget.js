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
    var _type = '-1';
    var _sumFrom = '';
    var _sumTo = '';
    var _dateFrom = '';
    var _dateTo = '';
    var _search_field = '';

    var _$node = null;

    var _journal = null;

    var _$txtDateFrom = null;
    var _$txtDateTo = null;
    var _$checkAll = null;

    var _$comboAccount = null;
    var _$comboCategory = null;

    var _$dialogFilterType = null;
    var _$dialogFilterSum = null;
    var _$dialogFilterAccount = null;
    var _$dialogFilterCategory = null;

    var _sexyCategoryInitialized = false;

    var OPERATION_TYPES = ['расход', 'доход', 'перевод', '', 'фин. цель'];

    var break_symbol;

    function insertWBR(td_contents)
    {
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
        if (data == null)
            return;

        //if (navigator.userAgent.Contains("Firefox"))
        if ($.browser.mozilla) {
            break_symbol = "<wbr>";
        } else {
            break_symbol = "&shy;";
        }

        $("#balance_before").html(data.list_before+" "+easyFinance.models.currency.getDefaultCurrencyText());
        $("#balance_after").html(data.list_after+" "+easyFinance.models.currency.getDefaultCurrencyText());

        _journal = data.operations;

        var tr, tp, pageTotal, curMoney, prevMoney;
        tr = '';
        pageTotal = 0;

        // Собираем данные для заполнения в таблицу
        for(var v in _journal) {
            // см. тикет #357
            if (_journal[v].account_name == null)
                continue;

            if (  _journal[v].transfer > 0 ) {
                tp = 'transfer';
            } else if (_journal[v].virt == "1") {
                tp = 'target';
            } else {
                if (_journal[v].drain == 1) {
                    tp = 'outcome';
                } else {
                    tp = 'income';
                }
            }

            // в режиме "все счета" не учитываем в балансе и итогах операции перевода (по ним баланс всегда ноль)
            if (_account == '' && data[v].transfer > 0)
                curMoney = 0;
            else
                curMoney = parseFloat(_journal[v].money * easyFinance.models.currency.getCurrencyCostById(_journal[v].account_currency_id));

            pageTotal = pageTotal + curMoney;

            var typ = '';
            if ( _journal[v].transfer > 0 ) {
                typ = 'перевод';
            } else if (_journal[v].virt == "1") {
                typ = 'перевод на финансовую цель';
            } else {
                if (_journal[v].drain == 1) {
                    typ = 'расход';
                } else {
                    typ = 'доход';
                }
            }

            var tooltipHtml = '<b>Тип:</b> ' + typ + '<br>';
            tooltipHtml += '<b>Счёт:</b> ' + ( res.accounts[_journal[v].account_id] ? res.accounts[_journal[v].account_id].name : '' ) + '<br>';
            tooltipHtml += '<b>Комментарий:</b><br> ' + _journal[v].comment.replace("\n", "<br>", "g");

            tr += "<tr id='op" + (_journal[v].virt == "1" ? 'v' : 'r') + _journal[v].id
                + "' title='" + tooltipHtml
                + "' value='" + v
                + "' moneyCur='" + curMoney.toString()
                + "' trId='" + _journal[v].tr_id
                + "' account='" + _journal[v].account_name
                + "'>"
                    + "<td class='check'>"
                    + "<input type='checkbox' /></td>"
                    + '<td class="light">'+_journal[v].date.substr(0, 5)+'</td>';

            tr += '<td class="light"><span>'+'<div class="operation ' + tp + '"></div>'+'</span></td>'

            // если перевод осуществляется между счетами с разными валютами,
            // то в переменной imp_id хранится сумма в валюте целевого счёта
            var money = (_journal[v].imp_id == null) ? _journal[v].money : _journal[v].imp_id;

            // #1361. Если выводим все счета,
            // то приводим суммы к валюте по умолчанию
            var strMoney = '';
            if (_account == '') {
                if (_journal[v].moneydef && _journal[v].account_currency_id != easyFinance.models.currency.getDefaultCurrencyId()) {
                    // в мультивалётных операциях есть свой курс,
                    // в поле moneydef уже сконвертированное значение

                    if (_journal[v].account_currency_id != easyFinance.models.currency.getDefaultCurrencyId()) {
                        // перевод без использования основной валюты. конвертируем по курсу
                        money = Math.abs(prevMoney); // QUICK FIX. правильно сделать это на сервере
                        ////_journal[v].money * easyFinance.models.currency.getCurrencyCostById(_journal[v].account_currency_id);
                    } else {
                        money = _journal[v].moneydef;
                    }
                } else {
                    if (_journal[v].type == "2" && _journal[v].account_currency_id != easyFinance.models.currency.getDefaultCurrencyId()) {
                        // для переводов учитываем курс
                        money = money * parseFloat(_journal[v].curs);
                    } else {
                        // расходы / доходы / цели
                        money = easyFinance.models.currency.convertToDefault(money, _journal[v].account_currency_id);
                    }

                    prevMoney = money;
                }
            }
            strMoney = formatCurrency(money);

            tr += '<td class="summ ' + (_journal[v].money>=0 ? 'sumGreen' : 'sumRed') + '"><span><b>'+strMoney+'&nbsp;</b></span></td>'

            tr += '<td class="big"><span>'+ ((_journal[v].cat_name == null)? '' : _journal[v].cat_name) +'</span></td>'
            + '<td class="big">'+ (_journal[v].comment ? shorter(_journal[v].comment, 24) : '&nbsp;')
                +'<div class="cont" style="top: -17px"><span>'+'</span><ul>'
                +'<li class="edit"><a title="Редактировать">Редактировать</a></li>'
                +'<li class="del"><a title="Удалить">Удалить</a></li>'
                +'<li class="add"><a title="Копировать">Копировать</a></li>'
                +'</ul></div>'
            +'</td></tr>';
        }

        // очищаем таблицу
        $('#operations_list').find('tr').remove();

        // заполняем таблицу
        $('#operations_list tbody').append(tr);

        // Выводим итоги по счёту/странице
        pageTotal = Math.round(pageTotal*100)/100;
        if (_account != '' && _account != "undefined") {
            $('#lblOperationsJournalAccountBalance')
                .html('<b>Остаток по счёту: </b>' + formatCurrency(_modelAccounts.getAccountBalanceTotal(_account)) + ' ' + _modelAccounts.getAccountCurrency(_account).text);//.show();
        }

        $('#lblOperationsJournalSum').html('<b>Баланс операций: </b>' + formatCurrency(pageTotal) + ' ' + easyFinance.models.currency.getDefaultCurrencyText()).show();

        if (pageTotal >= 0) {
            $('#lblOperationsJournalSum').addClass('sumGreen');
        } else {
            $('#lblOperationsJournalSum').addClass('sumRed');
        }
        if (data.list_before >= 0) {
            $('#balance_before').addClass('sumGreen');
        } else {
            $('#balance_before').addClass('sumRed');
        }
        if (data.list_after >= 0) {
            $('#balance_after').addClass('sumGreen');
        } else {
            $('#balance_after').addClass('sumRed');
        }
        if  (_modelAccounts.getAccountBalanceTotal(_account) >= 0) {
            $('#lblOperationsJournalAccountBalance').addClass('sumGreen');
        } else {
            $('#lblOperationsJournalAccountBalance').addClass('sumRed');
        }
        $("#balance_before").html(insertWBR($("#balance_before").html()));
        $("#balance_after").html(insertWBR($("#balance_after").html()));
        $("#lblOperationsJournalSum").html(insertWBR($("#lblOperationsJournalSum").html()));
        $("#lblOperationsJournalAccountBalance").html(insertWBR($("#lblOperationsJournalAccountBalance").html()));
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
            var id = $(this).attr('id');
            var value = $(this).attr('value');

            if (id.indexOf("opv") != -1)
                virts[key] = _journal[value].id;
            else
                ids[key] = _journal[value].id;

            _ops[key] = $.extend(true, {}, _journal[value]);

            key++;
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

        var _op = $.extend(true, {}, _journal[id]);

        var ids = [];
        var virts = [];

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
        var opVirt = (op.virt == "1") ? 'v' : 'r';
        var opTransferId = op.tr_id;
        var opId = op.id;

        // remove row from table
        $('#operations_list tr[id="op' + opVirt + opId + '"]').mouseout().unbind().remove();

        // remove paired operation if it's a transfer
        if (opTransferId != null) {
            var selector = '';
            if (opTransferId == "0") {
                selector = '#operations_list tr[trid="' + opId + '"]';
            } else {
                selector = '#operations_list tr[id="op' + opVirt + opTransferId + '"]';
            }
            $(selector).mouseout().unbind().remove();
        }
    }

    function _editOperation(){
        var operation = $(this).parent().attr('class');
        if (operation == 'edit') {
            easyFinance.widgets.operationEdit.fillForm(_journal[$(this).closest('tr').attr('value')], true);
        } else if(operation == 'del') {
            _deleteOperation($(this).closest('tr').attr('value'));
        } else if(operation == 'add') {
            // при создании копии проставляем текущую дату
            // остальные атрибуты остаются те же
            var copy = $.extend(true, {}, _journal[$(this).closest('tr').attr('value')]);
            copy.date = new Date();
            easyFinance.widgets.operationEdit.fillForm(copy, false);
        }

        return false;
    }

    function _onCheckClicked() {
        var $row = $(this).parent().parent();
        var id = _journal[$row.attr('value')].id;
        var trid = $row.attr('trid');

        // auto-check paired transfer operations
        var $pair = null;
        if (trid != "null") {
            if (trid == "0") {
                $pair = $('#operations_list tr[trid="' + id + '"] input');
            } else {
                $pair = $('#operations_list tr[id="opv' + trid + '"] input');
                if ($pair.length == 0) {
                    $pair = $('#operations_list tr[id="opr' + trid + '"] input');
                }
            }

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
            _type = '-1';
            _sumFrom = '';
            _sumTo = '';
            _category = '';
            $('#cat_filtr').get(0).options[0].selected = true;

            _account = '';
            _accountName = '';
            $('#account_filtr').get(0).options[0].selected = true;

            $('#lblOperationsJournalAccountBalance').hide();

            loadJournal();

            return false;
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
        _$comboCategory = _$node.find('#cat_filtr');
        _$comboCategory.change(function(){
            _category = $(this).val();

            loadJournal();

            return false;
        });

        // фильтр по счёту
        _$comboAccount = _$node.find('#account_filtr');
        _$comboAccount.change(function(){
            $('#lblOperationsJournalAccountBalance').hide();
            $('#lblOperationsJournalSum').hide();

            _account = $(this).val();
            _accountName = this.options[this.selectedIndex].text;
            loadJournal();

            return false;
        });
    }

    function _printFilters(){
        var txt = '';

        if (_type != '-1')
            txt = txt + 'операции: ' + OPERATION_TYPES[_type];

        if (_type != '-1' && _category != '')
            txt = txt + ', ';

        if (_category != '')
            txt = txt + 'категория: ' + easyFinance.models.category.getUserCategoryNameById(_category);

        if ((_type != '-1' || _category != '') && (_sumFrom != '' || _sumTo != ''))
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

        //if (_account != '')
        //    txt = txt + 'счёт: ' + _accountName;

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

        $('#lblOperationsJournalSum').html('<b>Баланс: </b>' + pageTotal + ' ' + (_modelAccounts.getAccountCurrency(_account) ? _modelAccounts.getAccountCurrency(_account).text : easyFinance.models.currency.getDefaultCurrencyText())).show();

        //if (navigator.userAgent.Contains("Firefox"))
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

        $('#btn_CSV').click(loadJournal_CSV);

        // #874. Обновляем данные об остатках на счетах
        // после добавления операции
        //$(document).bind('operationAdded', loadJournal);
        $(document).bind('accountsLoaded', loadJournal);
        $(document).bind('operationEdited', loadJournal);
        $('#remove_all_op').click(_deleteChecked);

        // биндим клик на чекбоксе в заголовке
        _$checkAll = $('#operations_list_header th input');
        _$checkAll.click(function(){
            if($(this).attr('checked'))
                $('#operations_list .check input').attr('checked','checked');
            else
                $('#operations_list .check input').removeAttr('checked');

            // show/hide 'remove checked' link
            if (_$node.find('table input').is(':checked'))
                $('#remove_all_op').show();
            else
                $('#remove_all_op').hide();
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
                // edit operation
                $(this).find('li.edit a').click();
                return false;
            }
        })

        $('#operations_list .cont').live('click', function(){
            // #1349. do nothing!
            return false;
        });

        $('#operations_list a').live('click', _editOperation);

        // show selection & floating menu
        $('tr','#operations_list').live('mouseover',function(){
            $('#operations_list tr').removeClass('act').find('.cont ul').hide();
            $(this).closest('tr').addClass('act').find('.cont ul').show();
        });

        $('#operation_list tr.item').live('mouseout',
            function(){
                $('.qtip').remove();
                $(this).removeClass('act');
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
        if (account == "undefined")
            return;

        _account = account;
        _$comboAccount.val(_account);

        if (_modelAccounts)
            _accountName = _modelAccounts.getAccountNameById(account);

        if (easyFinance.widgets.operationEdit && account != '')
            easyFinance.widgets.operationEdit.setAccount(account);
    }

    function setCategory(cat) {
        _category = cat;
        _$comboCategory.val(_category);
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

        _search_field = $("#search_field").val();

        _modelAccounts.loadJournal(_account, _category, _dateFrom, _dateTo, _sumFrom, _sumTo, _type, _search_field, _showInfo, false);
    }

    function loadJournal_CSV() {
        _printFilters();

        _dateFrom = $('#dateFrom').val();
        _dateTo = $('#dateTo').val();
    _search_field = $("#search_field").val();

        _modelAccounts.loadJournal(_account, _category, _dateFrom, _dateTo, _sumFrom, _sumTo, _type, _search_field, _showInfo, true);
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
