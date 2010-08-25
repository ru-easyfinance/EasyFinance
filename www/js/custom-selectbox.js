$(function() {
    $('.b-custom-select:not(".active")').bind('change.customselect', function() {
        var el = $(this);
        el.siblings('input').val($('option:selected', el).text());
    }).addClass('active').trigger('change.customselect');
});