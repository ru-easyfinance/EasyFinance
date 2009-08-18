/*
 * Тут только общие функции и события для всех страниц сайта
 * 
 * {* $Id$ *}
 */
$(function() {
    // Кнопка сворачивания / разворачивания
    $('li.over3 a').click(function() {
        $(this).closest('div.ramka3').find('div.inside').toggle();
    });
});