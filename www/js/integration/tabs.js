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

    // Подготовка callback для инициатора ajax запросов
    prepareBlankAction = function() {
        // запоминаем событие в Google Analytics
        try { _gaq.push(['_trackEvent', 'Анкета', 'Заполнена', 'АМТ - PDF']); } catch(err) {};

        if (tabs && (typeof(tabs) != 'undefined') && (tabs.tabs.length > 0)/* && wzValidateAll()*/) {
            var blankData = {};
            /*
            for (tabNo = 0; tabNo < tabs.tabs.length; tabNo++) {
                form = $('#' + tabs.tabs[tabNo].id + ' form.wz_frm');

                if (form.length > 0) {
                    partData = wzGetFormData(form.get(0));

                    if (partData.length > 1) {
                        blankData = wzMergeObjects(blankData, partData);
                    }
                }
            }

            blankData.saveType = "whole_data";
            blankData.length = 0;
            blankData.step_name = '';

            // Customer wishes crunch
            blankData = wzObjToArray(blankData);
            */
            return {
                'data': blankData,
                'idleMessage': 'Отправляем анкету в банк ...'
            };
        } else {
            return {
                'code': $.ajaxInitiator.result.error,
                'errorText': 'Пожалуйста, заполните все обязательные поля! Пункты с недозаполненными полями отмечены красным в списке слева.'
            };
        }
        return false;
    }

    // Конструирование инициатора ajax запроса
    this.sendBlankInitiator = $('#btnPrintForm').ajaxInitiator
    (
        $.ajaxInitiator.requestType.post, // request type
        '/integration/anketa', // url
        'json', // data type
        { // notification params
            'animationPosition': $.actionInitiator.animationPosition.right,
            'align': $.actionInitiator.align.left,
            'notificationPlace': $.actionInitiator.notificationPlace.nearTheInitiator,
            'notificationLifetime': 7000,
            'notificationTextNode': $('#finish div.notification-text-node'),
        },
        { // callbacks
            'prepareData': prepareBlankAction,
            'processSuccess': function (data) {
                return 'Анкета успешно отправлена в банк';
            },
            'processError': function (data) {
                // передача обработки ошибки инициатору
            }
        }
    );
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
