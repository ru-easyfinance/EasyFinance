$(document).ready(function() {

var budget = easyFinance.models.budget();
budget.load(res.budget);
easyFinance.widgets.budget(budget);
})
