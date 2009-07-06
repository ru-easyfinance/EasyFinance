$(function () {
     $.calculator.regional['ru'] = {
    decimalChar: ',',
    buttonText: '...', buttonStatus: 'Калькулятор',
    closeText: 'Закрыть', closeStatus: 'Закрыть калькулятор',
    useText: 'Установить', useStatus: 'Установить число',
    eraseText: 'Очистить', eraseStatus: 'Очистить поле',
    backspaceText: '<-', backspaceStatus: 'Стереть последнюю цифру',
    clearErrorText: 'CE', clearErrorStatus: '',
    clearText: 'C', clearStatus: '',
    isRTL: false
    };

    $.calculator.setDefaults($.calculator.regional['ru']);

    $('#pos_oc').calculator({showOn: 'button', buttonImageOnly: true, buttonImage: '/js/calculator/calculator.png'});
});