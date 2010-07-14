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
     * Ссылка на класс User
     * @var User
     */
    private $_user = null;

    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->_user = Core::getInstance()->user;
        $this->model = new Profile_Model();
        $this->tpl->assign('name_page', 'profile/profile');
    }

    function index()
    {
        $this->addToRes('profile', array(
            'integration' => array(
                'email'   => str_replace('@mail.easyfinance.ru', '', $this->_user->getUserProps('user_service_mail')),
            ),
        ));
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
        $prop['user_pass']   = $_POST['pass'];
        //$prop['user_name'] = htmlspecialchars($_POST['name']);
        $prop['user_mail']   = $_POST['mail'];
        $prop['newpass']     = $_POST['newpass'];
        $prop['getNotify']   = ((bool)$_POST['getNotify'])? 1: 0;
        $prop['guide']       = $_POST['guide'];

        if (!empty($_POST['mailIntegration'])) {
            $mail = $_POST['mailIntegration'] . "@mail.easyfinance.ru";
            if (Helper_Mail::validateEmail($mail)) {
                if ($this->model->checkServiceEmailIsUnique($mail)) {
                    $prop['user_service_mail'] = $mail;
                } else {
                    $this->renderJsonError("Такой адрес уже существует.\nПожалуйста, выберите другой адрес.");
                }
            } else {
                $this->renderJsonError("В названии ящика есть недопустимые символы.\n".
                    "Постарайтесь задать почту латинскими буквами, цифрами, без пробелов или других непонятных знаков.");
            }
        } else {
            $prop['user_service_mail'] = '';
        }

        $this->model->mainsettings('save',$prop);
        $this->renderJsonSuccess("Данные успешно сохранены");
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

    function save_reminders(){
        // заглушка для #1492

        $ret = array(
            "result" => array(
                "text" => "Настройки напоминаний сохранены"
            ),
            "reminders" => array(
                "mailAvailable" => true, // подключена ли услуга
                "mailDefaultEnabled" => true, // уведомление для новых событий
                "mailHour" => "23",
                "mailMinutes" => "45",

                "smsAvailable" => true, // подключена ли услуга
                "smsDefaultEnabled" => true, // уведомление для новых событий
                "smsHour" => "10",
                "smsMinutes" => "15"
            )
        );

        die(json_encode($ret));
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
