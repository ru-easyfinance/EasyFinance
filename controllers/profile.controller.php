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


    /**
     * @deprecated - вообще пора внедрять новый алгоритм валют
     */
    function load_currency(){
        die($this->model->currency('load'));
    }


    /**
     * @deprecated - отказываемся от пользовательских валют
     */
    function save_currency(){
        $prop = array();
        $prop['user_currency_list'] = serialize(explode(',',$_POST['currency']));
        $prop['user_currency_default'] = (int)$_POST['currency_default'];

        die($this->model->currency('save', $prop));
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
