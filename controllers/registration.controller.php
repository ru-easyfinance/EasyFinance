<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля welcome
 * @category registration
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Registration_Controller extends Template_Controller {

    /**
     * Модель регистрации
     * @var <Registration_Model>
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct() {
        $this->model = new Registration_Model();
        $tpl = Core::getInstance()->tpl;
        $tpl->assign('name_page', 'registration');
        $tpl->assign('register', array());
    }

    /**
     * Страница регистрации без параметров
     * @return void
     */
    function index() {

    }

    /**
     * Активизируем пользователя
     * @param $args array mixed
     * @return void
     */
    function activate ($args) {
        if (is_array($args)) {
            $reg_id = $args[0];
            $this->model->activate($reg_id);
        } else {
            return false;
        }

    }

    /**
     * Создаём нового пользователя
     * @param $args array mixed
     * @return void
     */
    function new_user ($args) {
        $this->model->new_user();
    }
}
