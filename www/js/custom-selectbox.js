$(function() {
    $('.b-custom-select:not(".active")').bind('change.customselect', function() {
        var el = $(this);
        el.parent().siblings('input').val($('option:selected', el).text());
    }).css('opacity', 0).addClass('active').trigger('change.customselect');
});