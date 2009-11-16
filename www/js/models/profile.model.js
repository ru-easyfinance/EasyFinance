/**
 * @desc Profile Model
 * @author Alexander [Rewle] Ilichov
 */

easyFinance.models.mail = function(){

var u_info;
var did;

function userInfo()
{
    $.get("/profile/load_main_settings/",
        {},
        function(data){
            u_info = {};
            u_info = $.extend(u_info,data);
            $('input#login').val(data['profile']['login']);
            $('input#mail').val(data['profile']['mail']);
        },
        'json')
}

function userInfoSave()
{
    if (($('input#pass').val()) && ($('input#newpass').val()) &&(($('input#newpass').val()) == ($('input#newpass2').val()))) {
        $.post('/profile/save_main_settings/', {
            help:       $('input#help:checked').length,/*@deprecated */
            guide:      $('input#guide:checked').length,/*@deprecated */
            login:      $('input#login').val(),
            pass:       $('input#pass').val(),
            newpass:    $('input#newpass').val(),
            mail:       $('input#mail').val()
        }, function(data) {
            if (data.result)
                $.jGrowl(data.result.text, {theme: 'green'});
            if (data.error)
                $.jGrowl(data.error.text, {theme: 'red'});
        }, 'json');
    }
}




var cur_list;
var cur_ids = {};
var u_cur_list;
function loadCurrency()
{
    $.get("/profile/load_currency/",
        {},
        function(data){
            cur_list = data['currency'];
            u_cur_list = data['profile']['currency'];
            cur_ids = $.extend(cur_ids,cur_list)
            for (key in u_cur_list)
            {
                did = key;
                break;
            }
            cur_upd(cur_list);
            ucur_upd(u_cur_list);
            def_upd();
        },
        'json')
}
function userCurrencyUpdate(list)
{
    var str = "";
    //opt ="";
    for (var k in cur_ids)
    {
        cur_ids[k]=0;
    }
    for(var key in list)
    {
        cur_ids[key]=1;
        $('.col .all #'+key).remove();
        str = str + '<li id="'+key+'"><a>'+list[key]['charCode']+' '+list[key]['name']+'</a></li>';
    }
    $('.col .user').html(str);

}

function currencyUpdate(list)
{
    str = "";

    for(key in list)
    {
        str = str + '<li id="'+key+'"><a>'+list[key]['charCode']+' '+list[key]['name']+'</a></li>';
    }
    $('.col .all').html(str);
}
/**
 * @deprecated
 */
function def_upd()
{
    var id;
    $('.col li').each(function(){
        txt = $(this).html();
        id = $(this).attr('id');
        if(cur_list[id]['uses']=='1')
            $(this).html('<b>'+txt+'</b>')
    })
    opt ="";
    id = $('#def_cur :selected').attr('id');
    did = (isNaN(id))?did:id;
    for (key in cur_ids)
    {
        attr = did==key ? ' SELECTED="SELECTED" ':'';
        if (cur_ids[key])
            opt = opt + '<option id="'+key+'" '+attr+'>'+cur_list[key]['name']+'</option>';
    }
    $('#def_cur').html(opt);
}

function userCurrencySave()
{
    var defaultCurrency =$('#def_cur :selected').attr('id');
    var userCurrency='';
    $('.user li').each(function(){
        userCurrency += $(this).attr('id') + ',';
    })
    $.post(
        '/profile/save_currency/',
        {
            currency: userCurrency,
            currency_default: defaultCurrency
        }, function(){
            $.jGrowl("Валюты сохранены", {theme: 'green'});
        }
    );
}
}