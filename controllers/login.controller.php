<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля login
 * @category login
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Login_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модели пользователя
     * @var <Login_Model>
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->model = new Login_Model();
        Core::getInstance()->tpl->assign('name_page', 'login');
    }

    /**
     * Индексная страница
     * @param <array> $args  mixed
     * @return void
     */
    function index($args)
    {
        $user = Core::getInstance()->user;

        if ($user->getId()) {
            header("Location: /accounts/");
            exit;
        } else {
            $this->model->auth_user();
        }
    }
}