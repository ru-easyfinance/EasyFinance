var tabs;

function dTabsClass() {
    this.reCurTab = /#step(\d+)$/i;
    this.activeTab = '';
    this.callbackShow = (typeof(wzValidateTab) == 'function') ? wzValidateTab : function() {};

    this.labels = [];
    this.tabs = [];

    this.init();
}

dTabsClass.prototype.init = function() {
    var i = 0;

    var step;

    this.labels = $('.wz_tab_header');
    this.tabs = $('.wz_tab_content');

    if (this.labels.length != this.tabs.length) {
        //alert('ERROR: tab labels and tabs mismatch!');
        return;
    }

    this.labels.each(function() {
        this.id = 'wz_tab_' + i++;
        this.onclick = function() {tabs.showTab(this.id);}
    });

    i = 0;
    this.tabs.each(function() {
        this.id = 'wz_tab_' + i++ + '_content';
    });

    if (this.reCurTab.test(location.href)) {
        step = RegExp.$1 - 1;
        if ((step >= 0) && (step < this.labels.length)) {
            this.showTab('wz_tab_' + step);
            return;
        }
    }

    this.showTab('wz_tab_0', true);
    this.activeTab = 'wz_tab_0';
}

dTabsClass.prototype.showTab = function(id, skipCallback) {
    skipCallback = (skipCallback === true) ? true : false;

    this.labels.removeClass('wz_active');
    $('#' + id).addClass('wz_active');

    this.tabs.removeClass('wz_active');
    $('#' + id + '_content').addClass('wz_active');

    if (!skipCallback && (typeof(this.callbackShow) == 'function')) {
        this.callbackShow();
    }

    this.activeTab = id;
}

dTabsClass.prototype.proto = function() {
}

$(document).ready(function() {
    tabs = new dTabsClass();
});