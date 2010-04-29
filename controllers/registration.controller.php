<?php if ( !defined ( 'INDEX' ) ) trigger_error ( "Index required!", E_USER_WARNING );

    /**
     * Класс контроллера для модуля welcome
     * @category registration
     * @copyright http://easyfinance.ru/
     * @version SVN $Id$
     */
    class Registration_Controller extends _Core_Controller
    {

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
            // @TEST регистрации, #1231
            /*
            $json 	= array (
                'result' => array (
                    'text' => "Регистрация прошла успешно!"
                    )
            );
            die (json_encode ($json) );
            */
            $login = htmlspecialchars($_POST['login']);
            $pass = sha1($_POST['password']);

            $answer = $this->model->new_user();

            if (count($this->model->getErrors()) == 0) {



                $user = Core::getInstance()->user;

                if(!$user->initUser($login, $pass))
                {
                    die('Некорректный логин или пароль!');
                }

                $login_Model = new Login_Model();

                $login_Model->login($login, $pass);
                $answer = array (
                    'result' => array (
                        'text' => 'Спасибо, вы зарегистрированы!',
                        'redirect' => "https://".URL_ROOT_MAIN."info/"
                    )
                );
            }
            die(json_encode($answer));
        }
    }
