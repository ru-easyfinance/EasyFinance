easyFinance.widgets.userCurrency = function(model){
    var currencyModel = model || easyFinance.models.currency;
    var currencyList = {};
    var userCurrencyList = {};
    var defaultCurrency;

    function _printOptionForDefaultCurrency(){
        var options = "";
        for (var key in userCurrencyList){
            options += '<option value="' + key +
                '" ' + (key == defaultCurrency ? 'selected="selected"' : '') +
                '>'+userCurrencyList[key].name + '</option>';
        }
        $('select#def_cur').html(options);
    }

    function _printCurrencyLists(){
        var userList = '', allList = '';
        for (var key in currencyList){
            if (typeof(userCurrencyList[key]) == 'object'){
                userList += '<li id="'+key+'"><a>'+currencyList[key]['charCode']+' '+currencyList[key]['name']+'</a></li>'
            }else{
                allList += '<li id="'+key+'"><a>'+currencyList[key]['charCode']+' '+currencyList[key]['name']+'</a></li>'
            }
        }
        $('.col .user').html(userList);
        $('.col .all').html(allList);

    }

    function _isCurrencyInUse(id) {
        for (var key in res.accounts) {
            if (res.accounts[key].currency == id)
                return true;
        }

        return false;
    }

    function init(){
        currencyModel.loadAllCurrency(function(data){
            currencyList = data['currency'];
            userCurrencyList = easyFinance.models.currency.getCurrencyList();
            defaultCurrency = easyFinance.models.currency.getDefaultCurrencyId();
            _printCurrencyLists();
            _printOptionForDefaultCurrency();

        });
        $('.user li').live('click',function(){
            if (($('.user li').length > 1)) {
                var id = $(this).attr('id');
                var currency = $(this);
                delUserCurrency(id, function(){
                    $('ul.all').append(currency);
                    _printOptionForDefaultCurrency();
                });
            }

        });
        $('.all li').live('click',function(){
            var id = $(this).attr('id');
            var currency = $(this);
            addUserCurrency(id, function(){
                $('ul.user').append(currency);
                _printOptionForDefaultCurrency();
            });
        });
        $('#save_cur').live('click',function(){
            changeDefaultCurrency($('#def_cur').val());
            save();
        });
    }

    function addUserCurrency(currencyId,calback){
        if (typeof(userCurrencyList[currencyId]) != 'object'){
            userCurrencyList[currencyId] = $.extend({},currencyList[currencyId]);
            if(typeof(calback) == 'function'){
                calback();
            }
        }
    }

    function delUserCurrency(currencyId,calback){
        if ( !_isCurrencyInUse(currencyId) && currencyId != defaultCurrency){
            delete userCurrencyList[currencyId];
            if(typeof(calback) == 'function'){
                calback();
            }
        }else{
            $.jGrowl('Данная валюта используется!', {theme : 'red'});
        }
    }

    function changeDefaultCurrency(currencyId){
        defaultCurrency = currencyId;
    }

    function save(){
        var ids = [];
        for (var key in userCurrencyList){
            ids.push(key);
        }
        var data = {
            currency: ids.toString(),
            currency_default: defaultCurrency
        }
        currencyModel.setCurrency(data,function(){
            $.jGrowl('Валюты успешно сохранены', {theme : 'green'});//@todo server ajax response
        });
    }
    return {
        init : init
    }
}(easyFinance.models.currency);

