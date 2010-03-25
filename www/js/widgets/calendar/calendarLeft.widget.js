easyFinance.widgets.calendarLeft = function(){
    var _$node = null;
    var _model = null;
    
    var _$blockOverdue = null;
    var _$blockFuture = null;

    var _operation = null;

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
        var _operation = easyFinance.models.accounts.getOverdueOperationById(id);
        if (_operation) {
            easyFinance.widgets.operationEdit.fillFormCalendar(_operation, true, false);
            return;
        }

        _operation = easyFinance.models.accounts.getFutureOperationById(id);
        promptSingleOrChain("edit", function(isChain){
            easyFinance.widgets.operationEdit.fillFormCalendar(_operation, true, isChain);
        });
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

        var str = '';
        for (var key in _data) {
            var event = _data[key];
            var cur = easyFinance.models.accounts.getAccountCurrencyText(event.account_id);
            cur = (cur === null) ? '' : cur;
            
            str += '<li class="line" id="calendarLeftOverdue'+event.id+'">'+
                        (event.comment != "" ? event.comment+'<br>' : "")+
                        '<span class="sum">' + '<span class="' + (event.money>=0 ? 'sumGreen' : 'sumRed')+'">' + event.money +'&nbsp;</span>'
                        + cur + '</span>'+
                        '<span class="date">'+event.date.substr(0, 5)+
                        ((event.source && event.source != "") ? ' <img src="/img/i/mail_drafts.png" style="vertical-align:middle;">' : "") + '</span>'+
                        shorter(easyFinance.models.category.getUserCategoryNameById(event.cat_id), 20)+
                        '<div class="cont"><ul>'+
                        '<li title="Подтвердить" class="accept"><a></a></li>'+
                        '<li title="Редактировать" class="edit"><a></a></li>'+
                        '<li title="Удалить" class="del"><a></a></li></ul></div></li>';
        }
        
        // меняем иконку в левой панели, если есть просроченные события
        if (str != "") {
            $("li#c4").addClass("warning");
            str = '<h2 style="color: red;">Просроченные операции</h2><ul>' + str + "</ul>";
        } else {
            $("li#c4").removeClass("warning");
        }

        _$blockOverdue.html(str);
    }

    function _redrawFuture(data) {
        var _data = data.future ? data.future : res.calendar.future;
        
        var periodicLeft = '';
        for (var key in _data) {
            var event = _data[key];
            var cur = easyFinance.models.accounts.getAccountCurrencyText(event.account_id);
            cur = (cur === null) ? '' : cur;

            periodicLeft += '<li class="line" id="calendarLeftFuture'+event.id+'">'+
                        (event.comment != "" ? event.comment+'<br>' : "")+
                        '<span class="sum">' + '<span class="' + (event.money>=0 ? 'sumGreen' : 'sumRed')+'">' + event.money +'&nbsp;</span>'
                        + cur + '</span>'+
                        '<span class="date">'+event.date.substr(0, 5)+
                        ((event.source && event.source != "") ? ' <img src="/img/i/mail_drafts.png" style="vertical-align:middle;">' : "") + '</span>'+
                        shorter(easyFinance.models.category.getUserCategoryNameById(event.cat_id), 20)+
                        '<div class="cont"><ul>'+
                        '<li title="Подтвердить" class="accept"><a></a></li>'+
                        '<li title="Редактировать" class="edit"><a></a></li>'+
                        '<li title="Удалить" class="del"><a></a></li></ul></div></li>';
        }

        if (periodicLeft != '') {
            periodicLeft = '<h2>Запланированные операции</h2><ul>' + periodicLeft + '</ul>';
        }

        _$blockFuture.html(periodicLeft);
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
