(function($){
    $.fn.collapsible_table = function(params) {
        var defaults = {
            collapserSelector: ".js-collapsible_table-collapser",
            collapsedClass: 'b-collapsible_table-thead__collapsed',
            expandedClass: 'b-collapsible_table-thead__expanded'
        };
        var options = $.extend({}, defaults, params);

        var that = this;

        function onToggleCollapse(evt) {
            var trgt = $(evt.target);
            var thead = trgt.closest('thead');

            thead.next('tbody').toggleClass('hidden');
            thead.toggleClass(options.collapsedClass).toggleClass(options.expandedClass);
        };

        $(options.collapserSelector).live('click', onToggleCollapse);
    }
})(jQuery);