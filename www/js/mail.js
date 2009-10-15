$(document).ready(function(){
    // init mail widget
    easyFinance.widgets.mail.init('#widgetMail', easyFinance.models.mail);

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

$('#mail-popup button').live('click',function(){
    if ($(this).attr('id')=='mail_save'){
        $('#mail-popup input#txtMailFolder').val('drafts');
        $('#mail-popup input#mail-to').val('');
    }
    else
    {
        $('#mail-popup input#txtMailFolder').val('message');
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
                    +'<td><b>'+data.mail[key]['folder']+'</b></td>'
                    +'<td>'+data.mail[key]['date']+'</td>'
                +'</tr>';
                $('.mail_list table').append(str);
                return false;//todo
            }
        },'json'))
    
})

})