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

        if (count($_POST) > 0) {

            // Хак для неавторизированных пользователей
            if (!session_id()) {
                session_start();
            }

            $model = new Promo_Tks_Model($_POST);

            //@TODO Переписать вывод сообщений в новом формате JSON
            if ($model->save()) {
                die(json_encode(array('result'=>'Всё нормально')));
            } else {
                die(json_encode(array('error'=>'Ошибка при сохранении')));
            }

        }
    }

}
