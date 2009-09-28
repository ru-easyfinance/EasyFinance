$(document).ready(function(){

    $('#ic2,#ic1').click(function(){
        item=$(this).closest('ul').find('li');
        $(item).removeClass('act');
        $(item).find('div').hide();
        $(item).find('a').show();
        $(this).attr('class','act');
        $(this).find('a').hide();
        $(this).find('div').show();

    });
});
