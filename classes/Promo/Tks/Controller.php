<?php
if(!defined('INDEX')) trigger_error("Index required!", E_USER_WARNING);

/**
 * Класс контроллера для Promo страницы
 * @copyright http://easyfinance.ru/
 */
class Promo_Tks_Controller extends _Core_Controller
{

    function __init()
    {
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
        $this->tpl->assign('name_page', 'promo/tks');
    }


    function anketa()
    {
        $this->tpl->assign('name_page', 'promo/tks-anketa');
        $data = $_POST;
        if (count($data) > 0) {

            // Хак для неавторизированных пользователей
            if (!session_id()) {
                session_start();
            }

            $isAuth = (Core::getInstance()->user->getId()) ? "Авторизирован" : "Не авторизирован";
            Logs::write(new User(), 'tks_anketa', $isAuth);

            if ($this->_sendData()) {
                $this->renderJsonSuccess('OK');
            } else {
                $this->renderJsonError('Error');
            }
        }
    }


    /**
     * Отправляем данные
     */
    function _sendData()
    {
        $body = "Фамилия:\t\t"   . @$_POST['surname']."\n".
            "Имя:\t\t\t\t"       . @$_POST['name']."\n".
            "Отчество:\t\t"      . @$_POST['patronymic']."\n".
            "Телефон:\t\t"       . @$_POST['phone']."\n".
            "Авторизирован:\t\t" . (Core::getInstance()->user->getId()?"Да":"Нет");

        $message = Swift_Message::newInstance()
            // Заголовок
            ->setSubject(date('r'))
            // От кого
            ->setFrom('support@easyfinance.ru')
            // Говорим "Кому"
            ->setTo('outeref@gmail.com')
            // Устанавливаем "Тело"
            ->setBody($body, 'text/plain');

        return Core::getInstance()->mailer->send($message);
    }

}
