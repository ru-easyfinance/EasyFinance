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

            $data['user_id'] = Core::getInstance()->user->getId();
            $model = new Promo_Tks_Model($data);

            //@TODO Переписать вывод сообщений в новом формате JSON
            if ($model->save()) {
                die(json_encode(array('result'=>'Всё нормально')));
            } else {
                die(json_encode(array('error'=>'Ошибка при сохранении')));
            }

        }
    }

}
