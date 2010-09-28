easyFinance.widgets.calendarLeft = function(){
    var _$node = null;
    var _model = null;

    var _$blockOverdue = null;
    var _$blockFuture = null;

    var _operation = null;

    var eventRowTemplate =
        '<li class="line {%odd%}" id="calendarLeft{%time%}{%id%}">\
            <span class="date">{%date%}{%mail_icon%}</span> \
            <span class="sum {%color%}">\
                <span class="amount">{%money%}</span>\
                <span class="cur">{%cur%}</span>\
            </span>\
            <span class="cat" title="{%cat_full%}">{%cat%}</span> \
            <span class="comment">{%comment%}</span>\
            <div class="cont"><ul>\
                <li title="Подтвердить" class="accept"><a></a></li>\
                <li title="Редактировать" class="edit"><a></a></li>\
                <li title="Удалить" class="del"><a></a></li>\
            </ul></div>\
        </li>';

    // private functions
    function _floatingMenuClicked(){
        var id = $(this).parent().closest("li").attr("id");
        id = id.replace("calendarLeftOverdue", "");
        id = id.replace("calendarLeftFuture", "");

        var cl = $(this).attr("class");
        if (cl == "accept")
            _menuAccept(id);
        else if (cl == "edit")
            _menuEdit(id);
        else if (cl == "del")
            _menuDelete(id);

        return false;
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
        // ищем операцию в списке просроченных
        _operation = easyFinance.models.accounts.getOverdueOperationById(id);
        if (_operation) {
            easyFinance.widgets.operationEdit.fillFormCalendar(_operation, true, false);
        } else {
            // для серий будущих операций спрашиваем,
            // редактировать одно событие или серию
            _operation = easyFinance.models.accounts.getFutureOperationById(id);
            calendarEditSingleOrChain(_operation);
        }
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

    function _redrawOverdue(data) {
        var _data = data.overdue ? data.overdue : res.calendar.overdue;

        var str = '',
            rows = [],
            values = {},
            i = 0;

        for (var key in _data) {
            var event = _data[key];
            var cur = easyFinance.models.accounts.getAccountCurrencyText(event.account_id);
            cur = (cur === null) ? '' : cur;
            i++;

            values = {
                odd: i % 2 ? '' : 'odd',
                time: 'Overdue',
                id: event.id,
                date: event.date.substr(0, 5),
                mail_icon: (event.source && event.source != "") ? ' <img src="/img/i/mail_drafts.png" style="vertical-align:middle;">' : "",
                cat_full: easyFinance.models.category.getUserCategoryNameById(event.cat_id),
                cat: shorter(easyFinance.models.category.getUserCategoryNameById(event.cat_id), 26),
                color: event.money >= 0 ? 'sumGreen' : 'sumRed',
                money: event.money,
                cur: cur,
                comment: event.comment != "" ? event.comment : ""
            }

            rows.push(templetor(eventRowTemplate, values));
        }

        // меняем иконку в левой панели, если есть просроченные события
        if (rows.length) {
            $(".b-leftpanel_tabs .b-leftpanel_tabs_item_operations").addClass("b-leftpanel_tabs_item_operations__warning");
            _$blockOverdue.html('<h2>Просроченные</h2><ul>' + rows.join('') + "</ul>");
        }
        else {
            $(".b-leftpanel_tabs .b-leftpanel_tabs_item_operations").removeClass("b-leftpanel_tabs_item_operations__warning");
        }

    }

    function _redrawFuture(data) {
        var _data = data.future ? data.future : res.calendar.future;

        var rows = [],
            values = {},
            i = 0;

        for (var key in _data) {
            var event = _data[key];
            var cur = easyFinance.models.accounts.getAccountCurrencyText(event.account_id);
            cur = (cur === null) ? '' : cur;

            i++;

            values = {
                odd: i % 2 ? '' : 'odd',
                time: 'Future',
                id: event.id,
                date: event.date.substr(0, 5),
                mail_icon: (event.source && event.source != "") ? ' <img src="/img/i/mail_drafts.png" style="vertical-align:middle;">' : "",
                cat_full: easyFinance.models.category.getUserCategoryNameById(event.cat_id),
                cat: shorter(easyFinance.models.category.getUserCategoryNameById(event.cat_id), 26),
                color: event.money >= 0 ? 'sumGreen' : 'sumRed',
                money: event.money,
                cur: cur,
                comment: event.comment != "" ? event.comment : ""
            }

            rows.push(templetor(eventRowTemplate, values))
        }

        if (rows.length != '') {
            _$blockFuture.html('<h2>Запланированные</h2><ul>' + rows.join('') + '</ul>');
        }
        else {
            _$blockFuture.html('')
        }
    }

    // public functions

    function init(selector, model){ // @todo: change data to MODEL
        _$node = $(selector);
        _$blockOverdue = _$node.find(".overdue");
        _$blockFuture = _$node.find(".future");

        _model = model;

        // init floating menu
        $(selector + ' .cont li').live('click', _floatingMenuClicked);

        $(selector + ' li.line').live('click', function(){
            $(this).find('li.edit').click();

            return false;
        });

        $(selector + ' .cont').live('click', function(){
            // #1349. do nothing!
            return false;
        });

        $(document).bind("operationEdited", redraw);
        $(document).bind("operationsAccepted", redraw);
        $(document).bind("operationsDeleted", redraw);
        $(document).bind("operationsChainAdded", redraw);
        $(document).bind("operationsChainEdited", redraw);
        $(document).bind("operationsChainDeleted", redraw);

        redraw();
    }
    /**
     * Выводит окошко пользователя для управления событиями
     */
    function redraw(data){
        _redrawOverdue(data || res.calendar);
        _redrawFuture(data || res.calendar);
    }

    return {init: init};
}();
