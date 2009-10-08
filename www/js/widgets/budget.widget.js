
easyFinance.widgets.budget = function(model){
    if (!model){return {};}
    
    var _$_list = model.print_list();
    var _info = model.print_info();
    var _$_group = _info.group;
    var _$_total = _info.total;


    $('.budget .waste_list form').append(_$_list);
    $('.budget .f_field3').html(_$_group);
    $('.budget #total_budget').val(_$_total);

    $('input#year').keyup(function(){
        var str = $('input#year').val();
        $('input#year').val(str.match(/[0-9]{0,4}/));
    });
    $('.waste_list form tr').live('mouseover',function(){
        $(this).addClass('act');
    }).live('mouseout',function(){
        $(this).removeClass('act');
    });
    return {};
}