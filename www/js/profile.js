var u_info;
var did;
function user_info()
{
    $.get("/profile/load_main_settings/",
        {},
        function(data){
            u_info = data;
            $('input#login').val(data['profile']['login']);
            $('input#mail').val(data['profile']['mail']);
        },
        'json')
}
function user_info_back()
{
     $('input#login').val(u_info['profile']['login']);
     $('input#mail').val(u_info['profile']['mail']);
}
function user_info_save()
{
    if(!$('input#pass').val) {
        return false;
    }

    if((($('input#newpass').val()) != ($('input#newpass2').val()))&($('input#newpass').val())) {
        return false;
    } else if (($('input#newpass').val()) == ($('input#newpass2').val())) {
        if ($('#help:checked').length == 1){
            if($.cookie('tooltip') != '1'){
                $.cookie('tooltip', '1', {expire: 100, path : '/', domain: false, secure : '1'});
                initToltips('modern')
            }
        }else{
            if($.cookie('tooltip') != '0'){
                $.cookie('tooltip', '0', {expire: 100, path : '/', domain: false, secure : '1'});
                destroyToltips();
            }
        }
        $.post('/profile/save_main_settings/', {
            //help: ($('#help:checked').length == 1)? 1 : 0,
            guide:($('#guide:checked').length == 1)? 1 : 0,
            login: $('#login').val(),
            pass: $('#pass').val(),
            newpass: $('#newpass').val(),
            mail: $('#mail').val()
        }, function() {
            $.jGrowl("Личные данные сохранены", {theme: 'green'});
        }, 'json');
    }
}

var cur_list;
var cur_ids = {};
var u_cur_list;
function currency()
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
function ucur_upd(list)
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

function cur_upd(list)
{
    str = "";

    for(key in list)
    {
        str = str + '<li id="'+key+'"><a>'+list[key]['charCode']+' '+list[key]['name']+'</a></li>';
    }
    $('.col .all').html(str);
}

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

function user_cur_back()
{
     cur_upd(cur_list);
     ucur_upd(u_cur_list);
     def_upd();
}

function user_cur_save()
{
    ids=[];
    i=0;
    $('.user li').each(function(){
        ids[i]= $(this).attr('id') ;
        i++;
    })
    def =$('#def_cur :selected').attr('id');
    
    $.post('/profile/save_currency/', {
        currency: ids.toString(),
        currency_default: def
    }, function(){
        $.jGrowl("Валюты сохранены", {theme: 'green'});
    });
}
$(document).ready(function(){
    $('.menu5 #i3').addClass('act');
    $('.menu5 #i3').live('click',function(){
        $('.menu5 #i4').removeClass('act');
        $('.menu5 #i3').addClass('act');
        $('.block2 .ramka3#money').hide();
        $('.block2 .ramka3#profile').show();
    })
    $('.menu5 #i4').live('click',function(){
        $('.menu5 #i3').removeClass('act');
        $('.menu5 #i4').addClass('act');
        $('.block2 .ramka3#profile').hide();
        $('.block2 .ramka3#money').show();
    })
    user_info();
    $('#back_info').live('click',function(){
        user_info_back();
    });
    $('#save_info').live('click',function(){
        user_info_save();
    });
    currency();
    $('#back_cur').live('click',function(){
        user_cur_back();
    });
    $('.user li').live('click',function(){
        if (($('.user li').length>1)&&($(this).attr('id')!='1'))
        {
            var id = $(this).attr('id');
            //alert(u_cur_list[id]['charCode'])
            cur_ids[id]=0;
            $('ul.all').append($(this));
            def_upd();
        }

    })
    $('.all li').live('click',function(){
        //$(this).remove();
        var id = $(this).attr('id');
        //alert(u_cur_list[id]['charCode'])
        var s=cur_ids[id];
        cur_ids[id]=1;
        if (!s)
            $('ul.user').append($(this));
        def_upd();
    })
    $('#save_cur').live('click',function(){
        user_cur_save();
    });
});
$(document).ready(function() {
    strokuk = document.cookie.toString();
    if ($.cookie('tooltip') == '0'){
        $('#help').removeAttr('checked');
    }
    if (strokuk.indexOf('guide=uyjsdhf') == -1){
        $('#guide').removeAttr('checked');
    }
})
