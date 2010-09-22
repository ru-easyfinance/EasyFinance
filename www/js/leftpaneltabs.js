var leftPanelTabs = (function() {
    var container,
        tabs,
        panels;

    function switchTo(index) {
        panels.hide();
        tabs.removeClass('act');

        tabs.eq(index - 1).addClass('act');
        panels.eq(index - 1).show();

        if (index == 1) {
            try {
                easyFinance.widgets.accountsPanel.redraw();
            }
            catch (e) {}
        }
    }

    function save(index) {
        $.cookie('activelisting', index, {
            expire: 100,
            path: '/',
            domain: false,
            secure: '1'
        });
    }

    function load() {
        switchTo( parseInt($.cookie('activelisting')) || 1 );
    }

    function onTab(evt) {
        var idx = $(this).prevAll('li').length + 1
        switchTo(idx);
        save(idx);
    }

    function init() {
        container = $('.js-leftpaneltabs');
        tabs = container.find('.js-leftpaneltabs-tabs li');
        panels = container.find('.js-leftpaneltabs-panel');

        tabs.bind('click', onTab);

        load();
    }

    $(init);
})()