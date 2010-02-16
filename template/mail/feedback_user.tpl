<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);

    $responseSubject   = "Спасибо за отзыв <{$this->title}> (#{$this->numberFeedback})";
    
    $responseFrom      = array('support@easyfinance.ru' => 'EasyFinance.ru');

    $responseBody = "Спасибо за отзыв, мы ответим Вам в течение 12-х часов\n\n"
        . "Тема отзыва: " . $this->title . "\n\n"
        . $this->message;
