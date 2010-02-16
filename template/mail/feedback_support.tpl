<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
    // Тема
    $supportSubject = "Полный отзыв #{$this->numberFeedback}: {$this->title}";

    // От кого письмо
    //$supportFrom = $this->email;
    $supportFrom = array('support@easyfinance.ru' => 'EasyFinance.ru');

    // Кому письмо
    $supportTo   = array('support@easyfinance.ru' => 'EasyFinance.ru');

    // Текст письма
    $supportBody    = $this->message . "\n\n"
        . var_export($this->params, true);
?>