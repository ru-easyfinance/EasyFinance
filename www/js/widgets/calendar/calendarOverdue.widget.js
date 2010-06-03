easyFinance.widgets.calendarOverdue = function(){
    var _$node = null;
    var _$table = null;

    function _floatingMenuClicked(){
        var id = $(this).closest("tr").val();

        var cl = $(this).attr("class");
        if (cl == "accept")
            _menuAccept(id);
        else if (cl == "edit")
            _menuEdit(id);
        else if (cl == "del")
            _menuDelete(id);

        return false;
    }

    function acceptAll() {
        var ids = [];

        _$table.find("tr").each(function(index, node) {
           ids.push($(node).val());
        });

        $.jGrowl("События подтверждаются...", {theme: 'green'});
        easyFinance.models.accounts.acceptOperationsByIds(ids, function(data) {
            if (data.result) {
                if (data.result.text)
                    $.jGrowl(data.result.text, {theme: 'green'});
            } else if (data.error) {
                if (data.error.text)
                    $.jGrowl(data.error.text, {theme: 'red'});
            }
        });
    }

    function _menuAccept(id){
        $.jGrowl("Регулярная операция подтверждается...", {theme: 'green'});
        easyFinance.models.accounts.acceptOperationsByIds([id], function(data) {
            if (data.result) {
                if (data.result.text)
                    $.jGrowl(data.result.text, {theme: 'green'});
            } else if (data.error) {
                if (data.error.text)
                    $.jGrowl(data.error.text, {theme: 'red'});
            }
        });
    }

    function _menuEdit(id){
        var op = easyFinance.models.accounts.getOverdueOperationById(id);
        easyFinance.widgets.operationEdit.fillFormCalendar(op, true, false);
    }

    function _menuDelete(id){
        if (confirm("Вы уверены, что хотите удалить событие?")) {
            $.jGrowl("Событие удаляется...", {theme: 'green'});
            easyFinance.models.accounts.deleteOperationsByIds([id], [], function(data) {
                if (data.result) {
                    if (data.result.text)
                        $.jGrowl(data.result.text, {theme: 'green'});
                } else if (data.error) {
                    if (data.error.text)
                        $.jGrowl(data.error.text, {theme: 'red'});
                }
            });
        }
    }

    function init(selector, modelAccounts){
        _$node = $(selector);
        _$table = _$node.find(".content table");

        _model = modelAccounts;

        // init floating menu
        $(selector + ' .cont li').live('click', _floatingMenuClicked);

        $(selector + ' div.content tr').live('click', function(){
            $(this).find('li.edit').click();

            return false;
        });

        $(document).bind("operationEdited", redraw);
        $(document).bind("operationsAccepted", redraw);
        $(document).bind("operationsDeleted", redraw);
        $(document).bind("operationsChainAdded", redraw);
        $(document).bind("operationsChainEdited", redraw);
        $(document).bind("operationsChainDeleted", redraw);

        _$node.find("#btnOverdueAcceptAll").click(acceptAll);

        redraw(res.calendar);
    }

    function redraw(_data){
        var data = _data.overdue ? _data.overdue : res.calendar.overdue;
        _$table.empty();

        var empty = true;
        for (var key in data){
            empty = false;

            var $tr = $("<tr>").addClass("child").val(data[key].id);
            var $td = $("<td>");

            $tr.append($td.addClass("col1 date").text(data[key].date.substr(0, 5)));

            if (data[key].source && data[key].source != "") {
                $td.get(0).innerHTML = '<img src="/img/i/mail_drafts.png" style="vertical-align:middle;"> ' + $td.get(0).innerHTML;
            }

            $tr.append($("<td>").addClass("col2 money")
                .html('<div class="abbr">' + easyFinance.models.accounts.getAccountCurrencyText(data[key].account_id) + '</div><div class="number sumRed">' + formatCurrency(data[key].money) + '</div>'));

            $tr.append($("<td>").addClass("col3").text(data[key].account_id == '' ? '' : easyFinance.models.accounts.getAccountNameById(data[key].account_id)));

            $tr.append($("<td>").addClass("col4")
                .append($("<span>").text(easyFinance.models.category.getUserCategoryNameById(data[key].cat_id))).append("&nbsp;")
                .append('<div class="cont"><ul style="z-index: 1006;"><li class="accept" title="Подтвердить"><a></a></li><li class="edit" title="Редактировать"><a></a></li><li class="del" title="Удалить"><a></a></li></ul></div>'));

            _$table.append($tr);
        }

        if (empty)
            _$node.hide();
        else
            _$node.show();
    }

    return{
        init : init,
        redraw : redraw,
        acceptAll: acceptAll
    }
}();
