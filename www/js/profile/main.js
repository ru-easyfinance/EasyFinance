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
        if ($('.user li').length>1)
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
