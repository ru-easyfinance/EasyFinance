var widgetBudget = null;

$(function() {
    // обновляем бюджет при изменении месяца в виджете выбора месяца
    $(document).bind('monthPickerChanged', onMonthChanged);
    function onMonthChanged(evt) {
        var dt = evt.startDate
        widgetBudget && widgetBudget.reload(dt);
    }


    var modelBudget = easyFinance.models.budget;
    widgetBudget = easyFinance.widgets.budget(modelBudget);
    easyFinance.widgets.budgetMaster(modelBudget, widgetBudget);


    easyFinance.widgets.calendarMonthPicker.init("#budgetMonthPicker");
})
