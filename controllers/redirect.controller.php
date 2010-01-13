<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Контроллер перенаправляющий на внешние ссылки
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */

class Redirect_Controller
{
	function index() {}

    /**
     * Редирект на бесплатную книгу по учёту финансов
     */
    function book () {
        //header('Location: http://fir.nes.ru/ru/calendar/PublishingImages/Fingramota%20Web%20Version%20.pdf');
	header('Location: https://easyfinance.ru/upload/files/FingramotaWebVersion.pdf');
        exit();
    }
}
