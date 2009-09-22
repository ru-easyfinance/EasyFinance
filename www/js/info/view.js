
$('.add2 span').live('click',
    function(){
        add_target()
    });
$('li.del').live('click',
    function(){
        del_target($(this).closest('div.object2'))
    });
$('li.edit').live('click',
    function(){
        edit_target($(this).closest('div.object2').attr('id'))
    });

    