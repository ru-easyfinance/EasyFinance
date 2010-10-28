easyFinance.widgets.currencyRight = function(){
    var _model;

    var row_template =
        '<div class="b-rates-row">\
            <span class="b-rates-row-curname">{%name%}</span>\
            <span class="b-rates-row-rateprogress b-rates-row-rateprogress__{%progress%}">{%cost%}</span>\
        </div>';

    function load(modelCurrency){
        _model = modelCurrency;
        redraw();
    }

    function redraw() {
        var data = $.extend({}, _model.getCurrencyList());

        delete data[_model.getDefaultCurrencyId()]; // удаляем дефолтную валюту из списка

        var currencyList = [];
        for(var key in data) {
            currencyList.push( templetor(row_template, data[key]) );
        }

        currencyList.push('<a class="b-rates-row" href="/my/profile/#currency">Настроить валюты</a>');

        $('dl.b-rates dd').html( currencyList.join('') );
    }

    $(load);

    return{
        load : load,
        redraw : redraw
    }
}();
