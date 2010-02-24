<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
    // Тема
    $subscribersSubject = "Отзыв #{$this->numberFeedback}: {$this->title}";
    

    // От кого
    $subscribersFrom    = array('support@easyfinance.ru' => 'EasyFinance.ru');

    // Тело письма
    $subscribersBody    = $this->message;


    // Массив с ящиками электронной почты, кому обязательно отсылаются все сообщения об ошибках
    if ( ! $fromUser ) {
        $subscribersEmails = array(
            'bashokov.ae@easyfinance.ru',
            'popovmb@gmail.com'
        );
    } else {
        $subscribersEmails = 'support@easyfinance.ru';
    }
    