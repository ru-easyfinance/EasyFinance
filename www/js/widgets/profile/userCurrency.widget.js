easyFinance.widgets.userCurrency = function(){
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
        $.get("/profile/load_currency/",
            {},
            function(data){
                currencyList = data['currency'];
                userCurrencyList = easyFinance.models.currency.getCurrencyList();
                defaultCurrency = easyFinance.models.currency.getDefaultCurrencyId();
                _printCurrencyLists();
                _printOptionForDefaultCurrency();

            },
            'json'
        );
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
//        $('#back_cur').live('click',function(){
//            init();
//            $.jGrowl('Отмена изменений!', {theme : 'green'});
//        });
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
        easyFinance.models.currency.setCurrency(data,function(){
            $.jGrowl('Валюты успешно сохранены', {theme : 'green'});//@todo server ajax response
        });
    }
    return {
        init : init
    }
}();
$(document).ready(function(){
    easyFinance.widgets.userCurrency.init();
});
//var did;
//
//var cur_list;
//var cur_ids = {};
//var u_cur_list;
//function currency()
//{
//    $.get("/profile/load_currency/",
//        {},
//        function(data){
//            cur_list = data['currency'];
//            u_cur_list = data['profile']['currency'];
//            cur_ids = $.extend(cur_ids,cur_list)
//            for (key in u_cur_list)
//            {
//                did = key;
//                break;
//            }
//            cur_upd(cur_list);
//            ucur_upd(u_cur_list);
//            def_upd();
//        },
//        'json')
//}
//function ucur_upd(list)
//{
//    var str = "";
//    //opt ="";
//    for (var k in cur_ids)
//    {
//        cur_ids[k]=0;
//    }
//    for(var key in list)
//    {
//        cur_ids[key]=1;
//        $('.col .all #'+key).remove();
//        str = str + '<li id="'+key+'"><a>'+list[key]['charCode']+' '+list[key]['name']+'</a></li>';
//    }
//    $('.col .user').html(str);
//
//}
//
//function cur_upd(list)
//{
//    str = "";
//
//    for(key in list)
//    {
//        str = str + '<li id="'+key+'"><a>'+list[key]['charCode']+' '+list[key]['name']+'</a></li>';
//    }
//    $('.col .all').html(str);
//}
//
//function def_upd()
//{
//    var id;
//    $('.col li').each(function(){
//        txt = $(this).html();
//        id = $(this).attr('id');
//        if(cur_list[id]['uses']=='1')
//            $(this).html('<b>'+txt+'</b>')
//    })
//    opt ="";
//    id = $('#def_cur :selected').attr('id');
//    did = (isNaN(id))?did:id;
//    for (key in cur_ids)
//    {
//        attr = did==key ? ' SELECTED="SELECTED" ':'';
//        if (cur_ids[key])
//            opt = opt + '<option id="'+key+'" '+attr+'>'+cur_list[key]['name']+'</option>';
//    }
//    $('#def_cur').html(opt);
//}
//
//
//
//function user_cur_save()
//{
//    ids=[];
//    i=0;
//    $('.user li').each(function(){
//        ids[i]= $(this).attr('id') ;
//        i++;
//    })
//    def =$('#def_cur :selected').attr('id');
//
//    $.post('/profile/save_currency/', {
//        currency: ids.toString(),
//        currency_default: def
//    }, function(){
//        $.jGrowl("Валюты сохранены", {theme: 'green'});
//    });
//}
//
//function isCurrencyInUse(id) {
//    for (var key in res.accounts) {
//        if (res.accounts[key].currency == id)
//            return true;
//    }
//
//    return false;
//}
//
//$(document).ready(function(){
//
//    currency();
////    $('#back_cur').live('click',function(){
////        user_cur_back();
////    });
//    $('.user li').live('click',function(){
//        if (($('.user li').length>1)) {
//            var id = $(this).attr('id');
//
//            // тикет #687
//            if (isCurrencyInUse(id)) {
//                alert ("Невозможно удалить валюту, поскольку у Вас есть счета в этой валюте!");
//                return;
//            }
//
//            if (id == easyFinance.models.currency.getDefaultCurrencyId()) {
//                alert ("Невозможно удалить валюту, которая используется по умолчанию!");
//                return;
//            }
//
//            //alert(u_cur_list[id]['charCode'])
//            cur_ids[id]=0;
//            $('ul.all').append($(this));
//            def_upd();
//        }
//
//    })
//    $('.all li').live('click',function(){
//        //$(this).remove();
//        var id = $(this).attr('id');
//        //alert(u_cur_list[id]['charCode'])
//        var s=cur_ids[id];
//        cur_ids[id]=1;
//        if (!s)
//            $('ul.user').append($(this));
//        def_upd();
//    })
//    $('#save_cur').live('click',function(){
//        user_cur_save();
//    });
//});