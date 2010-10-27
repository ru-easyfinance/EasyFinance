jQuery.fn.dataTableExt.oSort['ru_date-asc']  = function(a,b) {
    var ruDatea = a.split('.');
    var ruDateb = b.split('.');

    var x = (ruDatea[1] + ruDatea[0]) * 1;
    var y = (ruDateb[1] + ruDateb[0]) * 1;

    return ((x < y) ? -1 : ((x > y) ?  1 : 0));
};

jQuery.fn.dataTableExt.oSort['ru_date-desc'] = function(a,b) {
    var ruDatea = a.split('.');
    var ruDateb = b.split('.');

    var x = (ruDatea[1] + ruDatea[0]) * 1;
    var y = (ruDateb[1] + ruDateb[0]) * 1;

    return ((x < y) ? 1 : ((x > y) ?  -1 : 0));
};

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
    dataStorage: {
        pageCount: 0,
        data: []
    },
    recordsOnPage: 20,

    bind: function() {
        var self = this,
            grid = $(this.selector);
        if(!grid.length) return false;

        if(!this.pageReady) this.preparePage();
        this.dataGrid = grid.eq(0).dataTable({
            bJQueryUI: true,
            iDisplayLength: this.recordsOnPage,
            sPaginationType: "full_numbers",
            sDom: '<"H"p>t',
            bAutoWidth: false,
            aoColumns: [
                {bSortable: false},
                {sClass: 'l-right', sType: 'ru_date'},
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
        this.dataGrid.fnSort([[1, 'desc']]);
        new FixedHeader(
            this.dataGrid,
            {
                zTop:    8,
                zBottom: 7,
                zLeft:   6,
                zRight:  5
            }
        );
        $('.fixedHeader').addClass('custom-grid-style');

        return {
            grid: this.dataGrid,
            table: grid,
            preloader: function(show) {
                if(show) {
                    self.showPreloader();
                } else {
                    self.hidePreloader();
                }
            },
            _get: function() {
                return self.getDataStorage();
            },
            _clear: function() {
                self.dataStorage.data = [];
            },
            _set: function(data, push) {
                self.setDataStorage(data, push);
                self.calcPages();
                return self.getDataStorage();
            },
            count: this.recordsOnPage,
            draw: function() {
                self.draw();
            }
        };
    },

    getDataStorage: function() {
        return this.dataStorage;
    },

    setDataStorage: function(data, push) {
        if(push) {
            this.dataStorage.data.push(data);
        } else {
            this.dataStorage.data = data;
        }
    },

    draw: function() {
        this.dataGrid.fnClearTable();
        this.dataGrid.fnAddData(this.dataStorage.data);
    },

    calcPages: function() {
        this.dataStorage.pageCount = Math.ceil(this.dataStorage.data.length / this.recordsOnPage);
    },

    preparePage: function() {
        $(this.styles).each(function() {
            $('<link>').attr({
                rel: 'stylesheet',
                type: 'text/css',
                href:  window.location.protocol + '//' + window.location.hostname + this
            }).appendTo('head');
        });

        this.pageReady = true;
    },

    setListeners: function() {
        var self = this,
            grid = $(this.selector);

        $('#grid_search_field').unbind('keyup.datagrid').unbind('keypress.datagrid').bind('keyup.datagrid', function(e) {
            var el = $(this);
            //self.dataGrid.fnFilter(el.val());
        }).bind('keypress.datagrid', function(e) {
            if(e.keyCode == 27) {
                $(this).val('').trigger('blur');
                //self.dataGrid.fnFilter('');
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
