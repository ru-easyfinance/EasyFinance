var Tooltip = null;

$(function() {
    Tooltip = _Tooltip.bind();
});

_Tooltip = {
    tooltip: null,

    bind: function() {
        var self = this;

        if(!this.tooltip) this.tooltip = $('.b-tooltip').eq(0);
        this.hide();
        return {
            show: function(params) {
                self.show(params);
            },
            hide: function(params) {
                self.hide(params);
            }
        }
    },

    show: function(params) {
        if(params.content) $('.b-tooltip-container', this.tooltip).html(params.content);
        if(params.el) {
            var el = params.el,
                elw = el.width(),
                elh = el.height(),
                elp = el.position(),
                parentp = el.offsetParent().position();
            this.tooltip.css({
                'top': elp.top + parentp.top + elh + 5,
                'left': '50%',
                'margin-left': - (this.tooltip.width() /2)
            }).addClass('position');
        }
        this.tooltip.show();
    },

    hide: function() {
        this.tooltip.hide().removeClass('position');
    }
}