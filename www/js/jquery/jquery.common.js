(function($) {
    // Получить имя Тэга для текущего нода
    $.fn.tagName = function() {
        var node = this.get(0);
        if(!node) {
            return '';
        }
        return this.get(0).tagName.toLowerCase();
    }
    // Перечисление тэгов
    $.tagName = 
    {
        'undefined': '',
        'input': 'input',
        'button': 'button'
        // и.тд.
    };
    // Получить HTML текушего нода
    $.fn.outerHTML = function(s) {
        return (s) ? this.before(s).remove() : $("<p>").append(this.eq(0).clone()).html();
    }
})(jQuery);