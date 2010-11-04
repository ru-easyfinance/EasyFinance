var utils = (function() {
    // нормализует ноду, переданную как ноду, или как объект jQuery, в ноду
    function normalizeNode(node) {
        return ('jquery' in node) ? node[0] : node;
    }

    // нормализует строку к виду телефона: +7 (495) 123-45-67
    function toPhone(str) {
        var result = str.replace(/[^0-9]/gi, '');

        if (result.length == 7) {
            return toPhone7(result)
        }
        else if (result.length == 10) {
            return toPhone10(result)
        }
        else if (result.length > 10) {
            var code = result.substr(0, result.length - 10);
            result = result.substr(code.length);
            return '+' + code + ' ' + toPhone10(result)
        }
        else {
            return result;
        }

    }
    function toPhone7(str) {
        return [ str.substr(0, 3), str.substr(3, 2), str.substr(5, 2) ].join('-')
    }
    function toPhone10(str) {
        return '(' + str.substr(0, 3) + ') ' + toPhone7(str.substr(3))
    }


    /**
     * простой шаблонизатор с плейсхолдерами в формате {%varname%}
     * @param {String} tpl_str -- шаблон
     * @param {Dict} values_dict -- словарь со значениями
     */
    function templator(tpl_str, values_dict) {
        var result = tpl_str;
        for (var key in values_dict) {
            result = result.split('{%' + key + '%}').join(values_dict[key])
        }
        return result
    }


    // кроссбраузерное безопасное логгирование
    function log() {
        if (typeof console != 'undefined') {
            if ('apply' in console.log) { // лол, IE8 не  считает console.log полноценной функцией и не умеет console.log.apply
                console.log.apply(console, arguments);
            }
            else {
                console.log(arguments[0])
            }
        }
        else if (!!window.opera) { // для старых опер; в 10.62 (как минимум) в DragonFly работает консоль
             window.opera.postError.apply(window.opera, arguments)
        }
    }


    // вытаскивает параметры из ondblclick
    function getParams(node) {
        var elem = normalizeNode(node);
        return (elem.ondblclick) ? elem.ondblclick() : {}
    }

    var getType = {
        widget: function (node) {
            return normalizeNode(node).className.match(/js-widget-(\w*)/)[1];
        },
        control: function (node) { // would return 'datepicker' for 'js-control js-control-datepicker';
            return normalizeNode(node).className.match(/js-control-(\w*)/)[1];
        },
        command: function(node) {
            return normalizeNode(node).className.match(/js-cmd-(\w*)/)[1];
        }
    }

    /**
     * хелпер извещения юзера -- высокоуровневая обертка над jGrowl
     * @param {String} msg -- текст сообщения
     * @param {String} lvl -- 'error' || 'ok' || 'process', по дефолту 'error'
     * @param {Dict} options -- любые опции для jGrowl, см. http://stanlemon.net/projects/jgrowl.html#options
     *
     */
    function notifyUser(msg, lvl, options) {
        var levels = {
            error: 'red',
            ok: 'green',
            process: 'gray'
        }
        var opts = {
            theme: levels[lvl] || 'error'
        }
        if (arguments.length == 3) {
            $.extend(opts, options)
        }
        $.jGrowl(msg, opts);
    }

    function defaultOnSuccess(data, textStatus, XMLHttpRequest) {
        if (data.error) {
            if (data.error.text) {
                notifyUser(data.error.text);
            }
        }
        else if (data.result) {
            if (data.result.text) {
                notifyUser(data.result.text, 'ok');
            }
        }
    }

    /**
     *  хелпер ajax-запросов
     *  @param {String} url -- URI запроса, единственный обязательный параметр
     *  @param {Dict, String} data -- данные для отправки
     *  @param {Function} onSuccess -- хэндлер успешного (HTPP 2XX) ответа
     *  @param {Function} onError -- хэндлер ошибки (HTPP 4XX, HTPP 5XX)
     *  @param {Object} сtx -- объект, в контексте которого будут выполняться хэндлеры
     *  @param {String} type -- тип HTTP запроса
     *  @param {String} dataType -- тип ожидаемых данных, по умолчанию 'json'
     *  @param {Dict} additional_options -- словарь с любыми ajax-параметрами, см. http://api.jquery.com/jQuery.ajax/
     *
     */

    function ajax (url, data, onSuccess, onError, ctx, type, dataType, additional_options) {
        // конструируем обработчик ошибки (cработает в случае HTTP 5xx)
        var error = function() {
            if (onError) {
                return function (XMLHttpRequest, textStatus, errorThrown) {
                    if (ctx) {
                        onError.apply(ctx, [textStatus, errorThrown]);
                    }
                    else {
                        onError(textStatus, errorThrown)
                    }
                }
            }
            else {
                return function (XMLHttpRequest, textStatus, errorThrown) {
                    notifyUser('Произошла непредвиденная ошибка. Попробуйте еще раз через несколько минут.');
                }
            }
        }
        
        // конструируем обработчик успешного ответа
        var success = function() {
            if (onSuccess) {
                return function (data, textStatus, XMLHttpRequest) {
                    if (ctx) {
                        onSuccess.apply(ctx, [data]);
                    }
                    else {
                        onSuccess(data)
                    }
                }
            }
            else {
                return defaultOnSuccess;
            }
        }

        // собираем опции воедино
        var options = {
            data: data,
            dataType: dataType || 'json',
            global: false,
            type: type || 'POST',
            url: url,
            error: error(),
            success: success()
        }
        
        // доопределяем дополнительными опциями
        if (additional_options) {
            $.extend(options, additional_options)
        }

        // отсылаем низкоуровневым API
        $.ajax(options);
    }

    /**
     * хелпер ajax'овой отправки формы, обертка над utils.ajax
     * берет url, тип запроса и данные из аттрибутов формы
     * @param {Function} onSuccess -- хэндлер успешного (HTPP 2XX) ответа 
     * @param {Function} onError -- хэндлер ошибки (HTPP 4XX, HTPP 5XX) 
     * @param {Object} сtx -- объект, в контексте которого будут выполняться хэндлеры
     * @param {String} dataType -- тип ожидаемых данных, по умолчанию 'json'
     * @param {Dict} additional_options -- хэш с любыми параметрами, см. http://api.jquery.com/jQuery.ajax/
     */
    function ajaxForm (frm, onSuccess, onError, ctx, dataType, additional_options) {
        ajax(
            frm.attr('action'),
            frm.serialize(frm),
            onSuccess,
            onError,
            ctx,
            frm.attr('method'),
            dataType || 'json',
            additional_options
        )
    }
    /**
     * хелпер ajax'овой отправки файлов, обертка над $.ajaxForm
     * берет url, тип запроса и данные из аттрибутов формы
     * @param {Function} onSuccess -- хэндлер успешного (HTPP 2XX) ответа
     * @param {Function} onError -- хэндлер ошибки (HTPP 4XX, HTPP 5XX)
     * @param {Object} сtx -- объект, в контексте которого будут выполняться хэндлеры
     * @param {String} dataType -- тип ожидаемых данных, по умолчанию 'json'
     * @param {Dict} additional_options -- хэш с любыми параметрами, см. http://api.jquery.com/jQuery.ajax/
     */
    function ajaxUpload (frm, onSuccess, onError, ctx, dataType, additional_options) {
        // конструируем обработчик ошибки (cработает в случае HTTP 5xx)
        var error = function() {
            if (onError) {
                return function (XMLHttpRequest, textStatus, errorThrown) {
                    if (ctx) {
                        onError.apply(ctx, [textStatus, errorThrown]);
                    }
                    else {
                        onError(textStatus, errorThrown)
                    }
                }
            }
            else {
                return function (XMLHttpRequest, textStatus, errorThrown) {
                    notifyUser('Произошла непредвиденная ошибка. Попробуйте еще раз через несколько минут.');
                }
            }
        }

        // конструируем обработчик успешного ответа
        var success = function() {
            if (onSuccess) {
                return function (data, textStatus, XMLHttpRequest) {
                    if (ctx) {
                        onSuccess.apply(ctx, [data]);
                    }
                    else {
                        onSuccess(data)
                    }
                }
            }
            else {
                return defaultOnSuccess;
            }
        }

        var options = {
            url: frm.attr('action'),
            iframe: true,
            dataType: dataType || 'json',
            error: error(),
            success: success()
        };

        if (additional_options) {
            $.extend(options, additional_options)
        }

        frm.ajaxSubmit(options);
    }

    function initControls(container) {
        container.find('.js-control').each(function (index, elem) { // находим все контролы
            var type = utils.getType.control(elem); // тип контрола -- имя jQuery-плагина
            var params = utils.getParams(elem); // параметры передаются в аттрибуте ondblclick

            $(elem)[type](params); // навешиваем плагин на ноду
        });
    }

    function proxy(fn, ctx) {
        return function() {
            fn.apply(ctx, [].slice.call(arguments, 0))
        }
    }

    function getDaysCount(monthDate) {
        var m = monthDate.getMonth();
        var t = new Date(monthDate);
        for (var i = 29; i < 32; i++) {
            t.setDate(i);
            if (m != t.getMonth()){
                return (i-1)
            }
        }
        return (i);
    }

    function getMonthPartElapsed(monthDate) {
        var daysInMonth = getDaysCount(monthDate);

        var now = new Date()

        var currentTimeElapsed;

        if (now.getMonth() == monthDate.getMonth()) {
            currentTimeElapsed = now.getDate() / daysInMonth;
        }
        else {
            currentTimeElapsed = (now > monthDate) ? 1 : 0;
        }

        return currentTimeElapsed
    }

    function getMonthPartRatio(monthDate) {
        var currentTimeElapsed = getMonthPartElapsed(monthDate);

        if (!currentTimeElapsed) {
            currentTimeElapsed = 1 / getDaysCount(monthDate)
        }

        return (1 - currentTimeElapsed) / currentTimeElapsed;
    }

    return {
        // variuos utils
        toPhone: toPhone,
        templator: templator,
        log: log,
        proxy: proxy,

        // controls and widgets
        getParams: getParams,
        getType: getType,
        initControls: initControls,

        // interaction
        notifyUser: notifyUser,

        // ajax helpers
        defaultOnSuccess: defaultOnSuccess,
        ajax: ajax,
        ajaxForm: ajaxForm,
        ajaxUpload: ajaxUpload,

        getDaysCount: getDaysCount,
        getMonthPartElapsed: getMonthPartElapsed,
        getMonthPartRatio: getMonthPartRatio
    }
})();

