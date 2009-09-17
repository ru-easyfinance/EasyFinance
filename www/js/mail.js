$(document).ready(function(){
///////////////////////////////////import///////////////////////////////////////
/* Write New Message Popup Window */
    $('#mail-popup').dialog({
        autoOpen: false,
        title: 'Новое сообщение',
        width: 600,
        buttons: {
            "Закрыть": function() {
                $(this).dialog("close");
            }
        }
    });
    /*
     * @desc write mail
     *  click on yandex icon : 'write'=)
     */
    $('#mail-write').live('click',function(){
        $('#mail-popup').dialog('open');
        return false;
    });
    /* Read Message Popup Window */
    $('#mail-popup-read').dialog({
        autoOpen: false,
        title: 'Cообщение',
        width: 600,
        buttons: {
            "Закрыть": function() {
                $(this).dialog("close");
            }
        }
    });
    /*
     * @desc read mail
     *  click mail on maillist :)
     */
    $('.mail-title').live('click',function(){
        $('#mail-popup-read').dialog('open');
        id = $(this).closest('tr').attr('id');
        $('#mail-popup-read #mail-from').text(mails[id]['from']);
        $('#mail-popup-read #mail-date').text(mails[id]['date']);

        $("#mail-popup-read #mail-subject-read").text(mails[id]['title']);
        $("#mail-popup-read #mail-text-read textarea").text(mails[id]['text']);//@todo html text
        return false;
    });
///////////////////////////////////////functional///////////////////////////////
/**
 *@TODO clear form;add mail
 */
var mails = {};
/*
 * @desc send message block
 * send to user(future user and mail)
 * send to draft
 */
load_mails();//@todo избавиться в этом месте от неё
/*
 * @desc reload maillist
 *  click on yandex icon : 'reload'=)
 */
$('#mails-reload').live('click',function(){
    load_mails();
})

/*
 * @desc delete mails
 *  click on yandex icon : 'delete'=(
 */
$('#mails-delete').live('click',function(){
    var ids =[];
    $('.item input:ckeked').closest('tr').each(function(){
        id = $(this).attr('id');
        ids[id]=1;
    });
    $.post('/mail/del_mail/',{ids: ids.toString()},function(data){
        for (key in ids){
            if(ids[key]){
                $('.item#'+key).remove();
                mails[key] = 0;
            }
        }
    },'json');
})

$('#mail-popup button').live('click',function(){
    if ($(this).attr('id')=='mail_save'){
        $('#mail-popup input#category').val('draft');
        $('#mail-popup input#mail-to').val('');
    }
    else
    {
        $('#mail-popup input#category').val('message');
    }
    
        $.post('/mail/add_mail/', $('#mail-popup input,textarea', function(data){
            if (data['sucess']=='1'){
                $.extend(mails,mails,data.mail);//@todo in model
                for(key in data.mail)
                        break;
                str = '';
                str = str +'<tr class="item" id='+key+'>'
                    +'<td><input type="checkbox" value=""/></td>'
                    +'<td class="mail-title"><a href="#">'+data.mail[key]['title']+'</a></td>'
                    +'<td><b>'+data.mail[key]['category']+'</b></td>'
                    +'<td>'+data.mail[key]['date']+'</td>'
                +'</tr>';
                $('.mail_list table').append(str);
                return false;//todo
            }
        },'json'))
    
})

/*
 * @desc read mail
 * resend mail
 */
$('#mail-popup-read button').live('click',function(){
    $('#mail-popup').dialog('open');
    $('#mail-popup #mail-to').val($('#mail-from').text());//to == login!!!!!!!!
    $('#mail-popup-read').dialog('close');
})



//////////////////////////////////////functions/////////////////////////////////
/*
 * @todo no use AJAX whithout upload maillist
 * @desc upload and print maillist
 */
function load_mails()
{
    $.get('/mail/mail_list/',{},function(data){
        mails = $.extend({},data);
        str ='';
        $('.mail_list table tr.item').remove();
        for (key in mails)
        {
            if (mails[key]){
                str = str +'<tr class="item" id='+key+'>'
                    +'<td><input type="checkbox" value=""/></td>'
                    +'<td class="mail-title"><a href="#">'+mails[key]['title']+'</a></td>'
                    +'<td><b>'+mails[key]['category']+'</b></td>'
                    +'<td>'+mails[key]['date']+'</td>'
                +'</tr>';
            }
        }
        $('.mail_list table').append(str);

    },'json');
}
})