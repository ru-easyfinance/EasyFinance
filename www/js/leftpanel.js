/* 
 * Скрипт нужен для управления левой панелькой.
 */
$(document).ready(function (){

function update(){
$('div.listing').hide();
a_id = $('ul.control li.act').attr('id');
$('div.listing#'+a_id).show();
}

update();
$('ul.control li').click(function(){
    $('ul.control li').removeClass();
    $(this).addClass('act');
    update();
}
);


}
);

