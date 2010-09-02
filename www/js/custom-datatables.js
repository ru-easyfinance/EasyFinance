var DataTables = null;

$(function() {
    DataTables = _ActivateDataGrid.bind();
});

_ActivateDataGrid = {
    selector: 'table#dataGrid',
    pageReady: false,
    firstRun: true,
    dataGrid: null,
    styles: ['/css/datatables/datatables.css'],

    bind: function() {
        var self = this,
            grid = $(this.selector);
        if(!grid.length) return false;

        if(!this.pageReady) this.preparePage();
        this.dataGrid = grid.eq(0).dataTable({
            bJQueryUI: true,
            iDisplayLength: 50,
            sPaginationType: "full_numbers",
            sDom: '<"H"p>t',
            bAutoWidth: false,
            aoColumns: [
                {bSortable: false},
                {sClass: 'l-right', sType: 'date'},
                null,
                {sClass: 'l-right', sType: 'numeric'},
                {sType: 'string'},
                {sType: 'string'},
                null
            ],
            fnInitComplete: function() {
                self.setListeners();
            },
            oLanguage: {
                oPaginate: {
                    sFirst: 'Первая',
                    sLast: 'Последняя',
                    sNext: 'Следующая',
                    sPrevious: 'Предыдущая'
                },
                sEmptyTable: 'Нет операций по заданному фильтру',
                sInfoEmpty: 'Нет записей для отображения',
                sProcessing: 'В процессе обработки',
                sZeroRecords: 'Нет операций по заданному фильтру'
            }
        });
        this.dataGrid.fnSort([[1, 'asc']]);

        return {
            grid: this.dataGrid,
            table: grid,
            preloader: function(show) {
                if(show) {
                    self.showPreloader();
                } else {
                    self.hidePreloader();
                }
            }
        };
    },

    preparePage: function() {
        $(this.styles).each(function() {
            $('<link>').attr({
                rel: 'stylesheet',
                type: 'text/css',
                href: this
            }).appendTo('head');
        });

        this.pageReady = true;
    },

    setListeners: function() {
        var self = this,
            grid = $(this.selector);

        $('#grid_search_field').unbind('keyup.datagrid').unbind('keypress.datagrid').bind('keyup.datagrid', function(e) {
            var el = $(this);
            self.dataGrid.fnFilter(el.val());
        }).bind('keypress.datagrid', function(e) {
            if(e.keyCode == 27) {
                $(this).val('').trigger('blur');
                self.dataGrid.fnFilter('');
                return false;
            }
        });


    },

    showPreloader: function() {
        var grid = $(this.selector);
        if(this.firstRun) grid.addClass('vhidden');
        grid.closest('.b-operations-journal-grid').addClass('loading');
    },

    hidePreloader: function() {
        var grid = $(this.selector);
        grid.closest('.b-operations-journal-grid').removeClass('loading');
        if(this.firstRun) {
            this.firstRun = false;
            grid.removeClass('vhidden');
            $('<i class="b-preloader"></i>').appendTo($('.fg-toolbar'));
            grid.removeClass('vhidden').closest('.b-operations-journal-grid').addClass('small-spinner');
        }
    }
};