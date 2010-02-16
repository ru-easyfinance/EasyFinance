easyFinance.widgets.currencyRight = function(){
    var _model;

    function load(model){
        _model = model;
        print();
    }

    function print(){
        var data = _model.getCurrencyList();
        var currencyList = '', key;
        var defaultCurrency = _model.getDefaultCurrencyId();
        for(key in data) {
            if (defaultCurrency != key) {        
                currencyList += '<div class="line"><span class="valuta">' +
                    (data[key]['name'] || '') + '</span><span class="' +
                    (data[key]['progress'] || '') +'">' +
                    (data[key]['cost'] || '') + '</span></div>';
            }
        }
        if (currencyList != ''){
            $('dl.info dd').html(currencyList).parent().show();
        }else{
            $('dl.info').hide();
        }

    }


    return{
        load : load,
        print : print
    }
};
