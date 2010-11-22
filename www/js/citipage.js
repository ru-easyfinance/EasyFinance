var citipage = (function(selector) {
    var container,
        tabs,
        frm,
        phone_field,
        notice_container;

    function initFormControls() {

    }

    function renderNotice() {
        var fieldsToCopy = 'name patronymic mobile_code mobile_phone'.split(' ');
        for (var i = 0, l = fieldsToCopy.length; i < l; i++) {
            notice_container.find('.js-user' + fieldsToCopy[i]).text( frm[0][fieldsToCopy[i]].value );
        }

        notice_container.removeClass('hidden');
    }

    function onFrmSubmit(evt) {
        frm.find('input[type="submit"]').attr('disabled', 'disabled');
        notice_container.addClass('hidden');

        evt.preventDefault();

        // запоминаем событие в Google Analytics
        try {_gaq.push(['_trackEvent', 'Анкета', 'Заполнена', 'Citi CashBack']);} catch(err) {  };

        var elements = frm.find('input:not([name=""]):not([type="submit"]):not([type="reset"]):not([type="button"]), select:not([name=""]), textarea:not([name=""])');

        // превращаем поле даты в ожидаемый сервером формат
        if (frm[0].birthday.value) {
            var bday = frm[0].birthday.value.split('.');
            frm[0]['birthday[day]'].value = bday[0];
            frm[0]['birthday[month]'].value = bday[1];
            frm[0]['birthday[year]'].value = bday[2];
        }

        // превращаем поле с телефоном в ожидаемый сервером формат
        if (frm[0].mobile_number.value) {
            var phone = frm[0].mobile_number.value.replace(/\s+/, '').replace('+7', '').replace(/[^\d]/ig, '');
            frm[0].mobile_code.value = phone.substr(0, 3);
            frm[0].mobile_phone.value = phone.substr(3, 7);
            phone_field = $(frm[0].mobile_number)
            phone_field.attr('name', '');
        }

        // примитивная валидация (все поля в этой форме обязательны)
        if (elements.filter('[value=""]').length) {
            utils.notifyUser('Не заполнены обязательные поля', 'error');
            frm.find('input[type="submit"]').removeAttr('disabled');
            return false;
        }

        utils.notifyUser('Отправляем анкету&hellip;', 'process');


        function onOk(data) {
            if (!data.error) {
                utils.defaultOnSuccess(data);
                frm.find('input[type="submit"]').removeAttr('disabled');
                phone_field.attr('name', 'mobile_number');

                renderNotice();
            }
            else {
                onError(data)
            }
        }
        function onError(data) {

            utils.notifyUser(data.error.text)
            frm.find('input[type="submit"]').removeAttr('disabled');
            phone_field.attr('name', 'mobile_number');
        }

        utils.ajaxForm(frm, onOk, onError);

        return false;
    }

    function init() {
        container = $(selector || '.js-widget-citipage');

        utils.initControls(container);

        frm = container.find('form').eq(0);
        frm.bind('submit', onFrmSubmit);

        container.find('.js-toform').bind('click', function() {
            container.find('.js-control-tabs').tabs('select', 1);
        });

        notice_container = container.find('.js-form-notice');
    }

    $(init);

})('.js-widget-citipage');