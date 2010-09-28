(function($) {
    /* 
        Обработчик результата действия
        actionInitiator:    $.actionInitiator
    */
    function _actionInitiatorResult (actionInitiator) {
        this.actionInitiator = actionInitiator;
    }
    var _actionInitiatorResultPrototype = _actionInitiatorResult.prototype;
    // Вывести сообщение о прогрессе действия
    _actionInitiatorResultPrototype.progressMessage = function(message) {
        var resultType = $.actionResult.type;
        this.actionInitiator.showNotification(resultType.progressMessage, message);
    }
    // Удалить сообщение о прогрессе действия
    _actionInitiatorResultPrototype.clearProgressMessage = function(message) {
        this.actionInitiator.clearNotification();
    }
    // Вызывается в момент успешного завершения действия
    _actionInitiatorResultPrototype.success = function(message) {
        var resultType = $.actionResult.type;
        this.actionInitiator._actionComplete(resultType.success, message);
    }
    // Вызывается в момент ошибочного завершения действия
    _actionInitiatorResultPrototype.error = function(message) {
        var resultType = $.actionResult.type;
        this.actionInitiator._actionComplete(resultType.error, message);
    }
    // Вызывается в момент завершения действия
    _actionInitiatorResultPrototype.done = function() {
        this.actionInitiator._actionComplete();
    }
    $.actionResult = function (){};
    // Типы завершения действия
    $.actionResult.type = {'success': 0, 'error': 1, 'progressMessage': 2};
    
    /*
        Инициатор действия
        node:                   jQuery object or string (node selector)
        notificationParams:     array
                                {
                                    notificationPlace: $.actionInitiator.notificationPlace, default commonNotificationPlace
                                    lockInitiator: bool, default true
                                    showAnimation: bool, default true
                                    animationClass: string (classname), default 'idle-animation'
                                    align: $.actionInitiator.align, default: none
                                    animationPosition: $.actionInitiator.animationPosition, default: right
                                    notificationLifetime: time (milliseconds), default: 1500
                                }
        actionHandlerCallback:  function (_actionInitiatorResult)
                                returns anything
    */
    $.actionInitiator = function(node, notificationParams, actionHandlerCallback) {
    
        var eNotificationPlace = $.actionInitiator.notificationPlace;
        var eInitiatorAlign = $.actionInitiator.align;
        var eAnimationPosition = $.actionInitiator.animationPosition;
        
        // По умолчанию уведомление показывается рядом с инициатором действия
        var notificationPlace = notificationParams.notificationPlace != undefined ? notificationParams.notificationPlace : eNotificationPlace.commonNotificationPlace;
        
        // По умолчанию инициатор действия блокируется на время actionCallback
        var lockActionInitiator = notificationParams.lockInitiator != undefined ? notificationParams.lockInitiator : true;
        
        // По умолчанию показывается анимация на время выполнения actionCallback
        var showIdleAnimation = notificationParams.showAnimation != undefined ? notificationParams.showAnimation : true;
        
        // Класс для нода с анимацией ожидания
        var idleAnimationClass = notificationParams.animationClass != undefined ? notificationParams.animationClass : 'idle-animation';
        
        // Выравнивание инициатора
        var align = notificationParams.align != undefined ? notificationParams.align : eInitiatorAlign.none;
        
        // Положение уведомления и анимации ожидания
        var animationPosition = notificationParams.animationPosition != undefined ? notificationParams.animationPosition : eAnimationPosition.right;
        
        // Время жизни уведомления
        var notificationLifetime = notificationParams.notificationLifetime != undefined ? notificationParams.notificationLifetime : 1500;
        
        // Изменение нода с инициатором событий
        var node = $(node);
        var nodeId = node.attr('id');
        
        
        var extendedNodeHtml = '<td>' + node.outerHTML() + '</td>';
        
        
        // Если анимация ожидания отображается - добавляется html с анимацией
        if(showIdleAnimation) {
            switch(animationPosition) {
                case eAnimationPosition.left:
                    extendedNodeHtml = '<td><div class="' + idleAnimationClass + '">&nbsp;</div></td>' + extendedNodeHtml;
                    break;
                case eAnimationPosition.right:
                    extendedNodeHtml += '<td><div class="' + idleAnimationClass + '">&nbsp;</div></td>';
                    break;
            }
        }
        
        // Добавление нода с текстом уведомления, если node не указан в конструкторе
        // (вариант к которому в дальнейшем нужно прийти при стандартизации кода форм!)
        if(notificationPlace == eNotificationPlace.nearTheInitiator) {
            if(notificationParams.notificationTextNode == undefined) {
                switch(animationPosition) {
                    case eAnimationPosition.left:
                        extendedNodeHtml = '<td><div class="notification-text-node">&nbsp;</div></td>' + extendedNodeHtml;
                        break;
                    case eAnimationPosition.right:
                        extendedNodeHtml += '<td><div class="notification-text-node">&nbsp;</div></td>';
                        break;
                }
            }
        }
        
        // Выбор выравнивания для блока с инициатором<, анимацией ожидания и уведомлением>
        var alignClass = '';
        switch(align) {
            case eInitiatorAlign.none:
                break;
            case eInitiatorAlign.left:
                alignClass = 'fl_l';
                break;
            case eInitiatorAlign.right:
                alignClass = 'fl_r';
                break;
        }
        
        extendedNodeHtml = '<table id="' + nodeId + '-wrapper" class="action-initiator-wrapper ' + alignClass + '"><tr>' + extendedNodeHtml + '</tr></table><br class="clr" />';
        
        // Добавление нода с анимацией
        node.replaceWith(extendedNodeHtml);
        node = $('#' + nodeId + '-wrapper')
        
        // Сохранение параметров уведомления
        this.showIdleAnimation = showIdleAnimation;
        this.lockActionInitiator = lockActionInitiator;
        this.notificationPlace = notificationPlace;
        this.notificationLifetime = notificationLifetime;
        this.enabled = true;
        
        // Сохранение ссылок на узлы
        this.actionInitiatorNode = node.find('#' + nodeId);
        if(showIdleAnimation) {
            this.animationNode = node.find('div.' + idleAnimationClass).hide();
        }
        
        // Сохранение нода с текстом уведомления
        if(notificationPlace == eNotificationPlace.nearTheInitiator) {
            if(notificationParams.notificationTextNode == undefined) {
                this.notificationTextNode = node.find('.notification-text-node');
            } else {
                this.notificationTextNode = $(notificationParams.notificationTextNode);
            }
        }
        this.clearNotification();
        
        // Добавление обработчика к инициатору события
        var currentInitiator = this;
        this.actionInitiatorNode.click(function (_event) {
            // Проверка, что инициатор не заблокирован
            if(currentInitiator.enabled) {
                currentInitiator._showIndleAnimation();
                currentInitiator._lockInitiator();
                actionHandlerCallback(new _actionInitiatorResult(currentInitiator));
                _event.preventDefault();
                return false;
            }
        });
    }
     
    // Конструирование ActionInitiator из jQuery объекта
    $.fn.actionInitiator = function(notificationParams, actionHandlerCallback){
        return new $.actionInitiator(this, notificationParams, actionHandlerCallback);
    };
    
    var actionInitiator = $.actionInitiator.prototype;
    
    /* 
        Показать уведомление
        type:               $.actionResult.type
        notificationText:   string
    */
    actionInitiator.showNotification = function(type, notificationText) {
        // Очистка уведомлений
        this._clearNotification();
        
        var eNotificationPlace = $.actionInitiator.notificationPlace;
        switch(this.notificationPlace) {
            // Если уведомление отображается рядом с инициатором
            case eNotificationPlace.nearTheInitiator:
                this._showInplaceNotification(type, notificationText);
                break;
            // Если уведомление отображается в общем списке уведомленй
            case eNotificationPlace.commonNotificationPlace:
                var eActionResultType = $.actionResult.type;
                var eGlobalNotifierNotificationType = $.globalNotifier.notificationType;
                
                var notificationParams = 
                {
                    'timeout': this.notificationLifetime
                }
                var notificationType = eGlobalNotifierNotificationType.message;
                // Уведомление через глобальный объект - easyFinance.notifier
                switch(type) {
                    case eActionResultType.success:
                        notificationType = eGlobalNotifierNotificationType.success;
                        break;
                    case eActionResultType.error:
                        notificationType = eGlobalNotifierNotificationType.error;
                        break;
                    case eActionResultType.progressMessage:
                        notificationType = eGlobalNotifierNotificationType.message;
                        break;
                }
                easyFinance.notifier.showNotification(notificationType, notificationText, notificationParams);
                break;
        }
    }
    // Удалить уведомление
    actionInitiator.clearNotification = function() {
        this._clearNotification(true);
    }
    // Удалить уведомление
    actionInitiator._clearNotification = function(hideNode) {
        if(hideNode == undefined) {
            hideNode = false;
        }
        var eNotificationPlace = $.actionInitiator.notificationPlace;
        switch(this.notificationPlace) {
            case eNotificationPlace.nearTheInitiator:
                this._hideInplaceNotification(hideNode);
                break;
            case eNotificationPlace.commonNotificationPlace:
                // Удаление уведомлений через глобальный объект - easyFinance.notifier ?
                // easyFinance.notifier.clearNotifications();
                break;
        }
    }
    // Показать уведомление, отображаемое рядом с инициатором
    actionInitiator._showInplaceNotification = function (type, notificationText) {
        var notificationNode = this.notificationTextNode;
        // Изменение класса нода с уведомлением
        var eActionResultType = $.actionResult.type;
        switch(type) {
            case eActionResultType.success:
                notificationNode.addClass('notification-node-success');
                break;
            case eActionResultType.error:
                notificationNode.addClass('notification-node-error');
                break;
            case eActionResultType.progressMessage:
                notificationNode.addClass('notification-node-message');
                break;
        }
        // Изменение текста нода с уведомлением
        var currentInitiator = this;
        notificationNode.fadeIn
        (
            'slow',
            function() { 
                timeout = function() {
                    currentInitiator.clearNotification();
                    currentInitiator._notificationTimeout = null;
                }
                currentInitiator._notificationTimeout = setTimeout(timeout, currentInitiator.notificationLifetime); 
            }
        );
        notificationNode.text(notificationText);
    }
    // Скрыть уведомление, отображаемое рядом с инициатором
    actionInitiator._hideInplaceNotification = function (hideNode) {
        var notificationNode = this.notificationTextNode;
        // Удаление классов
        notificationNode
        .removeClass('notification-node-success')
        .removeClass('notification-node-message')
        .removeClass('notification-node-error');
        
        if(hideNode) {
            notificationNode.fadeOut('slow');
        }   
        
        // Изменение текста нода с уведомлением
        notificationNode.text('');
        
        // Очистка timeout
        if(this._notificationTimeout) {
            clearTimeout(this._notificationTimeout);
            this._notificationTimeout = null;
        }
    }
    // Показать анимацию ожидания 
    actionInitiator._showIndleAnimation = function() {
        if(this.showIdleAnimation) {
            this.animationNode.fadeIn('fast');
        }
    }
    // Скрыть анимацию ожидания 
    actionInitiator._hideIndleAnimation = function() {
        if(this.showIdleAnimation) {
            this.animationNode.fadeOut('fast');
        }
    }
    // Заблокировать инициатор действия
    actionInitiator._lockInitiator = function() {
        if(this.lockActionInitiator && this.enabled) {
            var eTagName = $.tagName;
            switch(this.actionInitiatorNode.tagName()) {
                case eTagName.input:
                case eTagName.button:
                    this.actionInitiatorNode.attr('disabled', 'disabled');
                    break;
            }
            // Добавление класса
            this.actionInitiatorNode.addClass('action-initiator-disabled');
            this.enabled = false;
        }
    }
    // Разблокировать инициатор действия
    actionInitiator._unlockInitiator = function() {
        if(this.lockActionInitiator && !this.enabled) {
            var eTagName = $.tagName;
            switch(this.actionInitiatorNode.tagName()) {
                case eTagName.input:
                case eTagName.button:
                    this.actionInitiatorNode.attr('disabled', '');
                    break;
            }
            // Удаление класса
            this.actionInitiatorNode.removeClass('action-initiator-disabled');
            this.enabled = true;
        }
    }
    // Завершить действие
    actionInitiator._actionComplete = function(resultType, message) {
        this._hideIndleAnimation();
        this._unlockInitiator();
        if(resultType != undefined && message != undefined) {
            this.showNotification(resultType, message);
        }
    }
    // Перечисление мест, где могут располагаться уведомления
    $.actionInitiator.notificationPlace = {'nearTheInitiator': 1, 'commonNotificationPlace': 2};
    
    // Расположение idle animation относительно инициатора
    $.actionInitiator.animationPosition = {'left': 0, 'right': 1};
    
    // Выравнивание блока из инициатора <idle animation, уведомления>
    $.actionInitiator.align = {'none': 0, 'left': 1, 'right': 2};
    
})(jQuery);