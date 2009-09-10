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
    if(!$('input#pass').val)
    {
        return false;
    }

    if((($('input#newpass').val) != ($('input#newpass2').val))&($('input#newpass').val))
    {
        return false;
    }
    else if (($('input#newpass').val) == ($('input#newpass2').val))
    {
            $.post('/profile/save_main_settings/', $('input'));
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
    str = "";
    //opt ="";
    for (k in cur_ids)
    {
        cur_ids[k]=0;
    }
    for(key in list)
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

function def_upd()//not work
{
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
    
    $.post('/profile/save_currency/', {currency: ids.toString(), currency_default: def});
}
