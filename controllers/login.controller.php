<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля login
 * @category login
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Login_Controller extends _Core_Controller
{
    /**
     * Ссылка на класс модели пользователя
     * @var Login_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->model = new Login_Model();
        Core::getInstance()->tpl->assign('name_page', 'login');
        
    }

    /**
     * Индексная страница
     * @param array $args  mixed
     * @return void
     */
    function index($args)
    {
        $user = Core::getInstance()->user;
        if ($user->getId()) {
            if ($_SERVER['HTTP_HOST'].'/' == 'iframe.' . URL_ROOT_MAIN)
                header("Location: http://iframe." . URL_ROOT_MAIN . "info/");
            else
                header("Location: /info/");
            exit;
        } else {
            $this->model->auth_user();
        }
    }

    /**
     * Авторизация с сайта азбука-финансов
     */
    function azbuka ()
    {
        
    }
}