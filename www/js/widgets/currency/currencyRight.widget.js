easyFinance.widgets.currencyRight = function(){
    var _model;

    function load(modelCurrency){
        _model = modelCurrency;
        redraw();
    }

    function redraw(){
        var data = _model.getCurrencyList();
        var currencyList = '', key;
        var defaultCurrency = _model.getDefaultCurrencyId();
        for(key in data) {
            if (defaultCurrency != key) {        
                currencyList += '<div class="line"><span class="valuta">' +
                    (data[key]['name'] || '') + '</span><span class="exchangeRate ' +
                    (data[key]['progress'] || '') +'">' +
                    (data[key]['cost'] || '') + '</span></div>';
            }
        }

        if (currencyList != ''){
            $('dl.info dd').html(currencyList).parent().show();
        } else {
            $('dl.info').hide();
        }
    }

    return{
        load : load,
        redraw : redraw
    }
}();
