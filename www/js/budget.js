$(document).ready(function() {
    $('#prev_month').click(function(){
        loadBudget('prev');
    });

    $('#next_month').click(function(){
        loadBudget('next');
    });

    $('#create_plan').click(function(){
        createPlan();
    });

    // Autoload
    loadBudget('current');

    function loadBudget(month)
    {
        $.ajax({
            type: "GET",
            url: "/budget/loadBudget/",
            data: {
                month: month,
                current_date: $('#current_date').val()
            },
            success: function(data) {
                $('#list').html(data);
            }
        });
    }

    function createPlan()
    {
        $('#list').hide();
        
        $.ajax({
            type: "GET",
            url: "/budget/create/",
            data: {
                current_date: $('#current_date').val()
            },
            success: function(data) {
                $('#form_create_plan').html(data);
            }
        });
    }
});