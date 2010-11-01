var citipage = (function(selector) {
    var container,
        tabs,
        frm,
        phone_field;

    function initFormControls() {

    }

    function onFrmSubmit(evt) {
        frm.find('input[type="submit"]').attr('disabled', 'disabled'); utils.log(frm.find('input[type="submit"]'))
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
            var phone = frm[0].mobile_number.value.replace('+7 ', '').replace(/[^\d]/ig, '');
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
            utils.defaultOnSuccess(data);
            frm.find('input[type="submit"]').removeAttr('disabled');
            phone_field.attr('name', 'mobile_number');
        }
        function onError(data) {
            notifyUser('Произошла непредвиденная ошибка. Попробуйте еще раз через несколько минут.')
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
    }

    $(init);

})('.js-widget-citipage');