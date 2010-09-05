(function($) {
    
    /* 
        Глобальное уведомление.
        Обертка над конкретным способом глобального уведомления.
        Инстанцируется при конструрирвании класса easyFinance.
    */
    $.globalNotifier = function() {
        // Инициализация jGrowl должна проходить здесь
        $.jGrowl.defaults.live = 1500;
        $.jGrowl.defaults.position = 'center';
        $.jGrowl.defaults.closerTemplate = '<div>[ закрыть все сообщения ]</div>';
    }
    
    // Типы глобальных уведомлений
    $.globalNotifier.notificationType = {'success': 0, 'error': 1, 'message': 2, 'warning': 3};
    
    var globalNotifier = $.globalNotifier.prototype;
    
    /* 
        Показать уведомление
        type:               $.globalNotifier.notificationType
        message:            string
        [optional]params:   arrray 
                            {
                                timeout: time (milliseconds) / string,
                                ...
                            }
    */
    globalNotifier.showNotification = function(type, message, params) {
        var eNotificationType = $.globalNotifier.notificationType;
        var notificationParams = {};
        
        // Выбор темы для jGrow в зависимости от типа уведомления
        switch(type) {
            case eNotificationType.success:
                notificationParams.theme = 'success';
                break;
            case eNotificationType.message: 
                notificationParams.theme = 'notification';
                break;
            case eNotificationType.error:
                notificationParams.theme = 'error';
                break;
        }
        
        // Установка timeout, в случае если timeout задан
        if(params != undefined && params.timeout != undefined) {
            notificationParams.life = params.timeout;
        }
        
        // Показывает jGrowl
        $.jGrowl(message, notificationParams);
    }
})(jQuery);