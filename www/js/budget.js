$(document).ready(function() {

var budget = easyFinance.models.budget();
budget.load(res.budget/*{
    list : {
        1 : {
                name : 'name',
                total : 667,
                children :[
                    {
                    id : 1,
                    name : 'c_name',
                    total : 667,
                    cur : 'rur',
                    limit_red : 45,
                    limit_green : 35,
                    limit_strip : 24,
                    mean_expenses : 123.44,//вроде средний расход
                    type : 0
                    }
                ]
            }
        },
    main :  {
        total:6776,
        cur : 'rur',
        expense_all : 999,
        income_all : 676,
        balance : 333,
        period : 30
    }
}*/)
easyFinance.widgets.budget(budget);
})
