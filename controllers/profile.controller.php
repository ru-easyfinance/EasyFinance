<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля личной страницы(профиля)
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Profile_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модель
     * @var Profile_Model
     */
    private $model = null;

    /**
     * Ссылка на класс Смарти
     * @var Smarty
     */
    private $tpl = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {

        $this->tpl = Core::getInstance()->tpl;
        $this->model = new Profile_Model();
        //$this->tpl->assign('name_page', 'profile/profile');
    }

    function index()
    {

    }
    /**
     * отдаёт логин имя мыло
     * return void;
     */
    function load_main_settings(){
        die($this->model->mainsettings('load'));
    }

    function save_main_settings(){
        $prop = array();
        $prop['user_pass'] = htmlspecialchars($_POST['pass']);
        $prop['user_name'] = htmlspecialchars($_POST['name']);
        $prop['user_mail'] = htmlspecialchars($_POST['mail']);
        $prop['newpass'] = htmlspecialchars($_POST['newpass']);
        die($this->model->mainsettings('save',$prop));
    }

    function load_currency(){
        die($this->model->currency('load'));
    }

    function save_currency(){
        $prop = array();
        $prop['user_currency_list'] = serialize($_POST['currency']);//arr
        $prop['user_currency_default'] = (int)$_POST['currency_default'];
        Core::getInstance()->user->initUserCurrency();
        Core::getInstance()->user->save();
        die($this->model->mainsettings('save',$prop));
    }
}
?>
