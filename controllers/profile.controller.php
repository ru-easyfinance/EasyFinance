<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля личной страницы(профиля)
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
 */

class Profile_Controller extends _Core_Controller_UserCommon
{
    /**
     * Ссылка на класс модель
     * @var Profile_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->tpl = Core::getInstance()->tpl;
        $this->model = new Profile_Model();
        $this->tpl->assign('name_page', 'profile/profile');

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
        //$prop['user_name'] = htmlspecialchars($_POST['name']);
        $prop['user_mail'] = htmlspecialchars($_POST['mail']);
        $prop['newpass'] = htmlspecialchars($_POST['newpass']);
        $prop['getNotify'] = ((bool)$_POST['getNotify'])? 1: 0;
//        $prop['help'] = $_POST['help'];
        $prop['guide'] = $_POST['guide'];
        die($this->model->mainsettings('save',$prop));
    }

    function load_currency(){
        die($this->model->currency('load'));
    }

    function save_currency(){
        $prop = array();
        $prop['user_currency_list'] = serialize(explode(',',$_POST['currency']));//arr
        $prop['user_currency_default'] = (int)$_POST['currency_default'];
        
        die($this->model->currency('save', $prop));
    }
    
    function cook(){
        die($this->model->cook());
    }
}
?>
