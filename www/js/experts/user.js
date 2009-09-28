//DROP TABLE `experts`, `experts_attach_content`, `experts_categories`, `experts_cost`, `experts_post`, `experts_rank`, `experts_review`, `experts_topic`;
//разделение на 2 файла

$(document).ready(function() {
    var list = function(order){
        $.post(
            '/experts/get_experts_list/',
            {order: order},
            function(data){
                html = '';
                for(key in data)
                {
                    id = ''+data[key].id;
                    name = ''+data[key].user_name;
                    rating = ''+data[key].rating;
                    mini_desc = ''+data[key].min_desc;
                    img=''+data[key].img;//@todo

                    html = html+'<dt>'+name+'</dt>'+
                            '<dd id="'+id+'">'+
				'<img src="'+img+'" alt="'+name+'" />'+
				'<ul class="rating">'+
                                    '<li class="h">Рейтинг:</li>'+
                                    '<li class="star">'+
                                        '<div style="width: '+rating+'%;">'+rating+'%</div>'+
                                    '</li>'+
                                    '<li class="result"><b></b></li>'+
				'</ul>'+
				mini_desc+
				'<div class="more"><span id="expert_profile">Читать полностью</span></div>'+
                            '</dd>';
                }
                $('.experts_list_in').html(html);
            },
            'json'
        );
    }
    var profile = function(id){
        $.post(
            '/experts/get_expert',
            {expert_id: id},
            function(data){

//////////////////////////основная инфа
                id = ''+data.id;
                name = ''+data.user_name;
                rating = ''+data.rating;
                description = ''+data.description;
                img=''+data.img;//@todo

                html = '<dl class="experts_one"><dt>'+name+'</dt>'+
                            '<dd id="'+id+'">'+
				'<img src="'+img+'" alt="'+name+'" />'+
				'<ul class="rating">'+
                                    '<li class="h">Рейтинг:</li>'+
                                    '<li class="star">'+
                                        '<div style="width: '+rating+'%;">'+rating+'%</div>'+
                                    '</li>'+
                                    '<li class="result"><b></b></li>'+
				'</ul>'+
				description+
				'<div class="more"><span id="expert_profile">Читать полностью</span></div>'+
                            '</dd></dl>';
///////////////////////////////  услуги темы и сертификаты


                $('#profile').html(html);
            },
            'json'
        );
    }

    list();

    $('#profile').hide();

    $('#expert_profile').live('click',
        function(){
            profile($(this).closest('dd').attr('id'));
            $('.experts_list_in').hide();
            $('#profile').show();

        });

    $('#ic1').click(
        function(){
            list();
            $('.experts_list_in').show();
            $('#profile').hide();
            $('#mail_list').hide();
        });

    var mail_list={};
    $('#ic2').click(function(){
        $.post(
        '/mail/mail_list/',
        {},
        function(data){
            mail_list = data;
            $('#mail_list').show();

            
            str='<table><tr><th>Категория</th><th>Название</th><th>Дата</th><th>Удалить</th></tr>';
            for(key in data)
            {
                str = str +'<tr id="'+key+'"s><td>'+
                    data[key].category +'</td><td>'+
                    data[key].title +'</td><td>'+
                    data[key].date +'</td><td class="del">Удалить</td></tr>';
            }
            str=str+'</table>';
            $('#mail_list').html(str)
        },
        'json'
    )
    return false;
    });

    //@todo

    $('#mail_list .del').live('click',function(){
        var id = $(this).closest('tr').attr('id');
        $.post(
        '/mail/del_mail/',
        {id : mail_list[key].id},
        function(data){
            
        },
        'json'
        )
        $('tr#'+id).empty();
        $('#mail_desc').html('');
    });

    $('#mail_list tr').live('click',
    function (){
        key = $(this).attr('id');
        str = '<table><tr><th>Категория</th><td>'+
                    mail_list[key].category +'</td></tr><tr><th>Название</th><td>'+
                    mail_list[key].title +'</td></tr><tr><th>Дата</th><td>'+
                    mail_list[key].date +'</td></tr></table><div class="block3">'+
                    mail_list[key].text+'adasd</div>';
        $('#mail_desc').html(str);
    });
});
