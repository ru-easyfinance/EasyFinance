$(function() {
    _ActionPanel.bind();
});

_ActionPanel = {

    bind: function() {
        this.setListeners();
    },

    setListeners: function() {
        var self = this;

        // Обработка всплывающей панели в DataTables
        if(DataTables && DataTables.table) {
            function clearDataTableHover(row) {
                row.closest('table').find('tr').removeClass('mouse-over');
                return true;
            }
            DataTables.table.mousemove(function(e) {
                var el = $(e.target),
                    row = el.closest('tr'),
                    actionPanel = $('.b-row-menu-block', row);
                self.hoverRow(e, row, actionPanel, clearDataTableHover);
            }).mouseleave(function() {
                clearDataTableHover($('td:first', DataTables.table));
            });
        }

        // Обработка всплывающей панели в списках
        if($('.efListWithTooltips').length) {
            var container = $('.efListWithTooltips');
            $('.cont', container).css('left', 0);
            function clearListHover(row) {
                row.closest('ul').find('li').removeClass('mouse-over');
                return true;
            }
            $(container).mousemove(function(e) {
                var el = $(e.target),
                    row = el.closest('li'),
                    actionPanel = $('.cont ul', row);
                //self.hoverRow(e, row, actionPanel, clearListHover);
            }).mouseleave(function() {
                clearListHover($('li:first', container));
            });
        }

        // Обработка всплывающей панели в обычных таблицах
        $(document).ajaxComplete(function() {
            if($('table:not(.processed) .cont ul').length) {
                var container = $('table:not(.processed) .cont:first ul').closest('table').addClass('processed');
                container.parent().css('position', 'relative');
                function clearTableHover(row) {
                    row.closest('table').find('tr').removeClass('mouse-over');
                    return true;
                }
                $(container).mousemove(function(e) {
                    var el = $(e.target),
                        row = el.closest('tr'),
                        actionPanel = $('.cont ul', row);
                    //Сброс позиционирования контейнера и правильное вертикальное позиционирование панели из-за специфики верстки
                    actionPanel.css('margin-top', -(actionPanel.height() / 2 + actionPanel.closest('td').height() / 2)).parent().css('position', 'static');
                    self.hoverRow(e, row, actionPanel, clearTableHover);
                }).mouseleave(function() {
                    clearTableHover($('td:first', container));
                });
            }
        });
    },

    hoverRow: function(e, row, actionPanel, clearHover) {
        !row.hasClass('mouse-over') && this.positionPanel(e, row, actionPanel) && clearHover(row) && row.addClass('mouse-over');
    },

    positionPanel: function(e, row, actionPanel) {
        actionPanel.css({
            top: 'auto',
            left: e.clientX - this.getPositionX(row),
            right: 'auto'
        });
        return true;
    },

    getPositionX: function(obj) {
        if (!obj) return 0;
        var level = obj.children().eq(0),
            positionLeft = 0;
        while(level.parent().attr('id') != 'container1') {
            level = level.offsetParent();
            positionLeft += Math.floor(level.position().left);
        }
        //Вычисление позиции для основного контейнера в ручную из-за неправильного высчитывания position().left в Webkit
        return positionLeft + ($('#container1').parent().width() - $('#container1').width() - 50) / 2;
    }
};