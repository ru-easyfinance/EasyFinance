<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Контроллер перенаправляющий на внешние ссылки
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */

class Redirect_Controller
{
    function index() {}

    function __init(){}

    /**
     * Редирект на бесплатную книгу по учёту финансов
     */
    function book () {
        //header('Location: http://fir.nes.ru/ru/calendar/PublishingImages/Fingramota%20Web%20Version%20.pdf');
    header('Location: ' . URL_ROOT_MAIN . '/upload/files/FingramotaWebVersion.pdf');
        exit();
    }

    /**
     * Редирект на анкету
     */
    function anketa_amt()
    {
        header('Location: ' .
            URL_ROOT . 'upload/files/Anketa%20AMT%20Bank%20-%20EasyFinance.ru.doc'
        );
        exit();
    }
}
