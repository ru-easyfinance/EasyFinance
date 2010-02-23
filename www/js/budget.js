$(document).ready(function() {
    easyFinance.models.category.load(function(){
        var modelBudget = easyFinance.models.budget;
        modelBudget.load(res.budget);
        var widget = easyFinance.widgets.budget(modelBudget);
        easyFinance.widgets.budgetMaster(modelBudget, widget);
    });
})
