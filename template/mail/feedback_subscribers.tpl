<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
    // Тема
    $subscribersSubject = "Отзыв #{$this->numberFeedback}: {$this->title}";
    

    // От кого
    if ( ! $fromUser ) {
        $subscribersFrom    = array('support@easyfinance.ru' => 'EasyFinance.ru');
    } else {
        $subscribersFrom    = $this->email;
    }

    // Тело письма
    $subscribersBody    = $this->message;


    // Массив с ящиками электронной почты, кому обязательно отсылаются все сообщения об ошибках
    if ( ! $fromUser ) {
        $subscribersEmails = array(
            'max.kamashev@easyfinance.ru'   =>'Maxim Kamashev',
            'bashokov.ae@easyfinance.ru'    => 'Artur Bashokov',
            'popovmb@gmail.com'             => 'Popov MB',
            'support@easyfinance.ru'        => 'Support',
        );
    } else {
        $subscribersEmails = 'support@easyfinance.ru';
    }
?>