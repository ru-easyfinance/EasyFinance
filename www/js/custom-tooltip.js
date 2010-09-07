var Tooltip = null;

$(function() {
    Tooltip = _Tooltip.bind();
});

_Tooltip = {
    tooltip: null,
    modal: false,
    dummy: null,

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
            },
            modal: self.modal
        }
    },

    show: function(params) {
        var self = this,
            text = 'Пусто';
        if(this.modal) return false;
        if(params.content) {
            text = params.content;
        } else if(params.selector) {
            text = $(params.selector).html() || 'Элемент не был найден';
        }
        $('.b-tooltip-container', this.tooltip).html(text);
        if(params.el) {
            var el = params.el,
                elw = el.outerWidth(true),
                elh = el.height(),
                elp = el.position(),
                parentp = el.offsetParent().position();
            this.tooltip.css({
                'top': elp.top + parentp.top + elh + 5,
                'left': (params.targetPos) ? elp.left + (elw / 2) + 8 : '50%',
                'margin-left': -(this.tooltip.width() / 2)
            }).addClass('position');
            if(params.modal) {
                this.modal = true;
                $(document).bind('click.tooltip', function(e) {
                    if(!self.dummy) {
                        self.dummy = true;
                        return false;
                    }
                    if(!$(e.target).closest('.b-tooltip').length) {
                        self.hide(true);
                        self.dummy = null;
                        $(document).unbind('click.tooltip');
                    }
                });
            }
            if(params.callback) params.callback($('.b-tooltip-container', this.tooltip));
        }
        this.tooltip.show();
    },

    hide: function(resetModal) {
        if(this.modal && !resetModal) return false;
        this.tooltip.hide().removeClass('position');
        if(resetModal) this.modal = false;
    }
};