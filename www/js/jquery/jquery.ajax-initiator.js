(function($) {
    /*
        Инициатор ajax запросов
        node:                   actionInitiator() param - node
        requestType:            $.ajaxInitiator.requestType
        url:                    string
        dataType:               string (jQuery $.post/$.get param named dataType)
        notificationParams:     actionInitiator() param - notificationParams +
                                {
                                    idleMessage: string - сообщение на время ожидания ответа
                                    defaultErrorMessage: string - сообщение по умолчанию на случай логической ошибки при запросе
                                    defaultSuccessMessage: string - сообщение по умолчанию на случай успешного завершения запроса
                                }
        callbacks:              array
                                {
                                    prepareData:        function () 
                                                        returns array
                                                        {
                                                            code: $.ajaxInitiator.result - код результата, default success
                                                            data: mixed - данные отправляемые запросом, если не произошло ошибки
                                                            errorText: string - сообщение об ошибке, если она произошла
                                                            [optional] url: string - перекрывает соответствующий параметр конструктора
                                                            [optional] dataType: string - перекрывает соответствующий параметр конструктора
                                                            [optional] requestType: $.ajaxInitiator.requestType - перекрывает соответствующий параметр конструктора
                                                            [optional] idleMessage: string - перекрывает соответствующий параметр конструктора
                                                        }
                                                        
                                    [optional] processSuccess:     function (data) 
                                                        returns string with message or nothing
                                                        
                                    [optional] processError:       function (data) 
                                                        returns string with message or nothing
                                }
    */
    $.ajaxInitiator = function(node, requestType, url, dataType, notificationParams, callbacks) {
        var eRequestType = $.ajaxInitiator.requestType;

        var prepareData = callbacks.prepareData;
        var processSuccess = callbacks.processSuccess != undefined ? callbacks.processSuccess : null;
        var processError = callbacks.processError != undefined ? callbacks.processError : null;
        var responseIdleMessage = notificationParams.idleMessage != undefined ? notificationParams.idleMessage : null;
        var defaultSuccessMessage = notificationParams.defaultSuccessMessage != undefined ? notificationParams.defaultSuccessMessage : null;
        var defaultErrorMessage = notificationParams.defaultErrorMessage != undefined ? notificationParams.defaultErrorMessage : null;
        
        actionHandler = function (actionResult) {
            var ePrepareResult = $.ajaxInitiator.result;
            var result = prepareData();
            
            // Если при подготовке данных произошла ошибка
            var resultCode = result.code != undefined ? result.code : ePrepareResult.success;
            
            switch(resultCode) {
                case ePrepareResult.error:
                    // Действие прерывается с ошибкой
                    actionResult.error(result.errorText);
                    return;
            }
            
            // Переопределение параметров запроса в зависимости от результата подготовки данных
            url = result.url != undefined ? result.url : url;
            dataType = result.dataType != undefined ? result.dataType : dataType;
            requestType = result.requestType != undefined ? result.requestType : requestType;
            responseIdleMessage = result.idleMessage != undefined ? result.idleMessage : responseIdleMessage;
            data = result.data;
            
             // Отображение сообщения о начале запроса к серверу
            if(responseIdleMessage) {
                actionResult.progressMessage(responseIdleMessage);
            }
            
            // Выбор типа запроса
            var requestFunction = $.get;
            switch(requestType) {
                case eRequestType.post:
                    requestFunction = $.post;
                    break;
                case eRequestType.get:
                    requestFunction = $.get;
                    break;
            }
            
            // Инициация запроса
            requestFunction
            (
                url,
                data,
                function (data, textStatus) {
                    // Если код ответа сервера - 200 (OK)
                    if(textStatus == 'success') {
                        if (data.error) {
                            // Вызов обработчика неудачного запроса
                            if(processError) {
                                errorText = processError(data);
                                if(errorText) {
                                    actionResult.error(errorText);
                                    return;
                                }
                            }
                            // Возврат текста ошибки инициатору 
                            if (data.error.text) {
                                actionResult.error(data.error.text);
                            } else {
                                if(defaultErrorMessage) {
                                    actionResult.error(defaultErrorMessage);
                                } else {
                                    actionResult.done();
                                }
                            }
                        } else if (data.result) {
                            // Вызов обработчика успешного запроса
                            if(processSuccess) {
                                successText = processSuccess(data);
                                if(successText) {
                                    actionResult.success(successText);
                                    return;
                                }
                            }
                            // Возврат текста инициатору 
                            if (data.result.text) {
                                actionResult.success(data.result.text);
                            } else {
                                if(defaultSuccessMessage) {
                                    actionResult.success(defaultSuccessMessage);
                                } else {
                                    actionResult.done();
                                }
                            }
                        } else {
                            actionResult.done();
                            // Возможно следует выводить ошибку о том, что не получен результат
                            // actionResult.error('');
                        }
                    } else {
                        // В остальных случаях - запрос не удался
                        if($.ajaxInitiator.requestErrorText) {
                            actionResult.error($.ajaxInitiator.requestErrorText);
                        }
                    }
                },
                dataType
            );
        }
        
        /*
            Возможно не следует принудительно перекрывать следующие настройки.
            {
        */
        notificationParams.lockInitiator = true;
        notificationParams.showAnimation = true;
        notificationParams.animationClass = 'idle-animation';
        /*  
            } 
        */
        return new $.actionInitiator(node, notificationParams, actionHandler);
    }
    // Конструирование ajaxInitiator из jQuery объекта
    $.fn.ajaxInitiator = function(requestType, url, dataType, notificationParams, callbacks){
        return new $.ajaxInitiator(this, requestType, url, dataType, notificationParams, callbacks);
    };
    
    // Установить в null если не нужно показывать ошибки запросов
    $.ajaxInitiator.requestErrorText = 'Ошибка отправки запроса';
    
    // Результаты подготовки данных перед ajax запросом
    $.ajaxInitiator.result = {'success': 0, 'error': 1};
    
    // Тип запроса
    $.ajaxInitiator.requestType = {'post': 0, 'get': 1};
    
})(jQuery);