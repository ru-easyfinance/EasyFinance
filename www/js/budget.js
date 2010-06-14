var widgetBudget = null;

$(document).ready(function() {
    easyFinance.widgets.calendarMonthPicker.init("#budgetMonthPicker");

    // обновляем бюджет при изменении месяца в виджете выбора месяца
    $(document).bind('monthPickerChanged', function(e) {
        widgetBudget.reload(e.startDate);
    });

    easyFinance.models.category.load(function(){
        var modelBudget = easyFinance.models.budget;
        modelBudget.load(res.budget);
        widgetBudget = easyFinance.widgets.budget(modelBudget);
        easyFinance.widgets.budgetMaster(modelBudget, widgetBudget);
    });
})
