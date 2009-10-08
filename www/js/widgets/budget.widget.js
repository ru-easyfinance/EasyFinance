
easyFinance.widgets.budget = function(model){
    if (!model){return {};}

    /**
     * @desc {html} список бюджетов сформированный в хтмл
     */
    var _$_list = model.print_list();
    /**
     * @desc {} системный объект хранящий общую информацию о бюджете
     */
    var _info = model.print_info();
    /**
     * @desc {html} остаток,ср доход и расход по бюджетам.сформирован в хтмл
     */
    var _$_group = _info.group;
    /**
     * @desc {Int} Общий бюджет
     */
    var _$_total = _info.total;


    $('.budget .waste_list form').append(_$_list);
    $('.budget .f_field3').html(_$_group);
    $('.budget #total_budget').val(_$_total);

    /**
     * @desc маска для инпута с годом
     */
    $('input#year').keyup(function(){
        var str = $('input#year').val();
        $('input#year').val(str.match(/[0-9]{0,4}/));
    });
    /**
     * @desc подсветка строки в списке при наведении
     */
    $('.waste_list form tr').live('mouseover',function(){
        $(this).addClass('act');
    }).live('mouseout',function(){
        $(this).removeClass('act');
    });
    return {};
}