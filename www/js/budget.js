$(document).ready(function() {
    easyFinance.models.category.load(function(){
        var budget = easyFinance.models.budget();
        budget.load(res.budget);
        var widget = easyFinance.widgets.budget(budget);
        easyFinance.widgets.budgetMaster(budget, widget);
    });
})
