(function($){
    $.fn.collapsible_table = function(params) {
        var defaultSettings = {
            collapserSelector: ".js-collapsible_table-collapser",
            collapsedClass: 'b-collapsible_table-thead__collapsed',
            expandedClass: 'b-collapsible_table-thead__expanded'
        };
        var settings = $.extend({}, defaultSettings, params);

        var that = this;

        function onToggleCollapse(evt) {
            var trgt = $(evt.target);
            var thead = trgt.closest('thead');

            thead.next('tbody').toggleClass('hidden');
            thead.toggleClass(settings.collapsedClass).toggleClass(settings.expandedClass);
        };

        $(settings.collapserSelector).live('click', onToggleCollapse);
    }
})(jQuery);