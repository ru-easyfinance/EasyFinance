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
        $this->model = new Profile_Model();
        $this->tpl->assign('name_page', 'profile/profile');
    }

    function index() 
    { }

    /**
     * отдаёт логин имя мыло
     * return void;
     */
    function load_main_settings(){
        die($this->model->mainsettings('load'));
    }

    function save_main_settings(){
        $prop = array();
        $prop['user_pass']   = $_POST['pass'];
        //$prop['user_name'] = htmlspecialchars($_POST['name']);
        $prop['user_mail']   = $_POST['mail'];
        $prop['newpass']     = $_POST['newpass'];
        $prop['getNotify']   = ((bool)$_POST['getNotify'])? 1: 0;
        $prop['guide']       = $_POST['guide'];
        die($this->model->mainsettings('save',$prop));
    }

    function load_currency(){
        die($this->model->currency('load'));
    }

    function save_currency(){
        $prop = array();
        $prop['user_currency_list'] = serialize(explode(',',$_POST['currency']));
        $prop['user_currency_default'] = (int)$_POST['currency_default'];
        
        die($this->model->currency('save', $prop));
    }
    
    function cook(){
        die($this->model->cook());
    }

    /**
     * Генерируем служебную почту (если она не была сгенерирована)
     */
    function create_service_mail()
    {
        $mail = _Core_Request::getCurrent()->post['mail'];
        
        $user = Core::getInstance()->user;

        if ( Helper_Mail::validateEmail ( $mail ) ) {

            if ( $this->model->checkServiceEmailIsUnique ( $mail ) ) {

                if ( $this->model->createServiceMail ( $user, $mail ) ) {

                    $this->tpl->assign('result', array('text'=>'Email успешно создан'));
                    
                }

            } else {

                $this->tpl->assign('error', array('text'=>"Придумайте другой email.\nЭтот email уже занят"));

            }

        } else {

            $this->tpl->assign('error', array('text'=>'Используйте только английские буквы и цифры'));

        }
        
    }

    /**
     * Удаляет у пользователя служебную почту
     */
    function delete_service_mail()
    {

        $user = Core::getInstance()->user;

        if ( $this->model->deleteServiceMail( $user ) ) {

            $this->tpl->assign('result', array('text'=>'Ящик успешно удалён'));

        } else {

            $this->tpl->assign('error', array('text'=>'Ошибка при удалении ящика'));

        }
    }

    /**
     * Проверяет почту на уникальность
     */
    function service_mail_is_unique()
    {
        $mail = _Core_Request::getCurrent()->post['mail'];

        if ( $this->model->checkServiceEmailIsUnique ( $mail ) ) {

            $this->tpl->assign('result', array('text'=>'Имя ящика уникально'));

        } else {

            $this->tpl->assign('error', array('text'=>'Имя ящика не уникально'));

        }
    }
}

