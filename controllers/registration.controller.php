<?php if ( !defined ( 'INDEX' ) ) trigger_error ( "Index required!", E_USER_WARNING );

    /**
     * Класс контроллера для модуля welcome
     * @category registration
     * @copyright http://easyfinance.ru/
     * @version SVN $Id$
     */
    class Registration_Controller extends _Core_Controller
    {

        private $_error = array();

        /**
         * Модель регистрации
         * @var Registration_Model
         */
        private $model = null;

        /**
         * Конструктор класса
         * @return void
         */
        function __init ()
        {
            $this->model = new Registration_Model();

            $this->tpl->assign ( 'name_page', 'registration' );

            if ( !session_id () ) {
                session_start ();
            }
        }

        /**
         * Страница регистрации без параметров
         * @return void
         */
        function index ()
        {
        }

        /**
         * Активизируем пользователя
         * @param $args array mixed
         * @return void
         */
        function activate ( $args )
        {
            if ( is_array ( $args ) ) {
                $reg_id = $args[0];
                $this->model->activate ( $reg_id );
            }
            else {
                return false;
            }
        }

        /**
         * Создаём нового пользователя
         * @param $args array mixed
         * @return void
         */
        function new_user ()
        {
            $login            = $_POST['login'];
            $password         = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $name             = $_POST['name'];
            $mail             = $_POST['mail'];

            // Проверяем валидность заполненных данных
            if (!empty($password) && !empty($confirm_password)) {
                if ($password === $confirm_password) {
                    $sha1_password = SHA1($password);
                } else {
                    $this->_error['pass'] = "Введённые пароли не совпадают!";
                }
            } else {
                $this->_error['pass'] = "Введите пароль!";
            }

            if (!$this->validate_login($login)) {
                $this->_error['login'] = "Неверно введен логин! <i>Логин может содержать только латинские буквы и цифры!</i>";
            }
            $login = htmlspecialchars($login);

            if (!Helper_Mail::validateEmail($mail)) {
                $this->_error['mail'] = "Неверно введен e-mail!";
            }
            $mail = htmlspecialchars($mail);

            if (!$this->model->exist_user($login, $mail)) {
                $answer = $this->model->new_user($name, $login, $password, $confirm_password, $mail);
            }

            $errors = array_merge($this->model->getErrors(), $this->_error);
            if (count($errors) == 0) {

                $user = Core::getInstance()->user;

                if(!$user->initUser($login, $password))
                {
                    die(json_encode(array('error' => array ('text' => 'Некорректный логин или пароль!'))));
                }

                $login_Model = new Login_Model();

                $login_Model->login($login, $password);

                $answer = array (
                    'result' => array (
                        'text' => 'Спасибо, вы зарегистрированы!',
                        'redirect' => "https://".URL_ROOT_MAIN."info/"
                    )
                );
            }
            die(json_encode($answer));
        }

    /**
     * Проверяет корректность логина
     * @param $login string
     * @return bool
     */
    function validate_login($login = '')
    {
        if ($login != '') {
            if(preg_match("/^[a-zA-Z0-9_]+$/", $login)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
