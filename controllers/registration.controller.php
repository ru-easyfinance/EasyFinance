<?php if ( !defined ( 'INDEX' ) ) trigger_error ( "Index required!", E_USER_WARNING );

    /**
     * Класс контроллера для модуля welcome
     * @category registration
     * @copyright http://easyfinance.ru/
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
        function new_user()
        {
            $login            = isset($_POST['login'])    ? trim((string)$_POST['login'])    : '';
            $password         = isset($_POST['password']) ? trim((string)$_POST['password']) : '';
            $confirm_password = isset($_POST['confirm_password']) ? trim((string)$_POST['confirm_password']) : '';
            $name             = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
            $mail             = isset($_POST['mail']) ? trim((string)$_POST['mail']) : '';;

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

            // Проверить наличие пользователя
            // TODO: model->exist_user() готовит собщение об ошибке - надо перенести сюда
            $this->model->exist_user($login, $mail);

            // Если нет ошибок создать и авторизовать пользователя
            $errors = array_merge($this->model->getErrors(), $this->_error);
            if (!$errors) {

                // Создать пользователя
                $this->model->new_user($name, $login, $password, $confirm_password, $mail);

                // Отправить ему уведомление с реквизитами
                $this->_send_mail_success($name, $login, $password, $mail);

                $user = Core::getInstance()->user;
                if (!$user->initUser($login, $sha1_password)) {
                    $this->_output(array('error' => array ('text' => 'Некорректный логин или пароль!')));
                }

                // Авторизовать пользователя
                $this->_authenticateUser($login, $sha1_password);

                $answer = array (
                    'result' => array (
                        'text' => 'Спасибо, вы зарегистрированы!',
                        'redirect' => "https://".URL_ROOT_MAIN."info/"
                    )
                );

            } else {
                $answer = array (
                    'error' => array (
                        'text' => implode('<br />', $errors),
                    ),
                );
            }

            $this->_output($answer);
        }


    /**
     * Ответ контроллера
     *
     * @param  array $data
     * @return void
     */
    protected function _output(array $data)
    {
        die(json_encode($data));
    }


    /**
     * Авторизовать пользователя
     */
    protected function _authenticateUser($login, $password)
    {
        $login_Model = new Login_Model();
        $login_Model->login($login, $password);
    }


    /**
     * Отправляем пользователю письмо что он успешно зарегистрировался
     *
     * @return bool
     */
    protected function _send_mail_success($name, $login, $password, $mail)
    {
        $body = "<html><head><title>
            Вы зарегистрированы в системе управления личными финансами EasyFinance.ru
            </title></head>
            <body><p>Здравствуйте, {$name}!</p>
            <p>Ваш e-mail был указан при регистрации в системе.<br/>

            <p>Для входа в систему используйте:<br>
            Логин: {$login}<br/>
            Пароль: {$password}</p>

            <p>C уважением,<br/>Администрация системы <a href='https://".URL_ROOT."' />EasyFinance.ru</a>
            </body>
            </html>";

        $subject = "Вы зарегистрированы в системе управления личными финансами EasyFinance.ru";

        $message = Swift_Message::newInstance()
            // Заголовок
            ->setSubject('Вы зарегистрированы в системе управления личными финансами EasyFinance.ru')
            // Указываем "От кого"
            ->setFrom(array('support@easyfinance.ru' => 'EasyFinance.ru'))
            // Говорим "Кому"
            ->setTo(array($mail => $login))
            // Устанавливаем "Тело"
            ->setBody($body, 'text/html');
        // Отсылаем письмо
        return Core::getInstance()->mailer->send($message);
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
