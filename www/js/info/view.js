print_targets(0);
$('li.del').live('click',
    function(){
        del_target($(this).closest('div.object2'))
    });
$('li.edit').live('click',
    function(){
        edit_target($(this).closest('div.object2'))
    });

    