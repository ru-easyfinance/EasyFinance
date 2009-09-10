$(document).ready(function(){
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
        if ($('.user li').length>1)
        {
            id = $(this).attr('id');
            //alert(u_cur_list[id]['charCode'])
            cur_ids[id]=0;
            $('ul.all').append($(this));
            def_upd();
        }

    })
    $('.all li').live('click',function(){
        //$(this).remove();
        id = $(this).attr('id');
        //alert(u_cur_list[id]['charCode'])
        s=cur_ids[id];
        cur_ids[id]=1;
        if (!s)
            $('ul.user').append($(this));
        def_upd();
    })
    $('#save_cur').live('click',function(){
        user_cur_save();
    });
});
