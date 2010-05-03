<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для регистрации пользователей
 * @author Max Kamashev "ukko" <max.kamashev@gmail.com>
 * @author korogen
 * @copyright http://easyfinance.ru/
 * @category registration
 */
class Registration_Model
{
    private $_error = array();

    public function getErrors()
    {
        return $this->_error;
    }

    /**
     * Активирует пользователя на портале
     * @param string $reg_id Временный ключ для регистрации SHA1
     * @return bool
     */
    function activate ($reg_id) {
        $db = Core::getInstance()->db;

        $sql = "SELECT user_id, reg_id FROM registration WHERE reg_id = ?;";
        $row = $db->selectRow($sql, $reg_id);
        if (!empty($row)) {
            $user_id = $row['user_id'];
            $db->query("DELETE FROM registration WHERE reg_id = ?", $reg_id);

            $sql = "UPDATE users SET user_active = '1', user_new = '0' WHERE id = ?";
            $db->query($sql, $user_id);
            header('Location: /registration/#activate');
            exit;
        } else {
            //trigger_error('Ключ не найден, или он устарел!', E_USER_WARNING);
            return false;
        }
    }

    /**
     * Отправляем пользователю письмо что он успешно зарегистрировался
     * @return bool
     */
    function send_mail_success($name, $login, $password, $mail)
    {
        require_once SYS_DIR_LIBS . "external/Swift/swift_required.php";

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

    function exist_user($login, $mail)
    {
        $sql = "SELECT user_mail AS mail, user_login AS login FROM users WHERE user_login=? OR user_mail=?";
        $result = Core::getInstance()->db->select($sql, $login, $mail);
        if (!empty($result)) {
            foreach($result as $value) {
                if ($value['mail'] == $mail) {
                    $this->_error['mail'] = "Пользователь с таким адресом электронной почты уже зарегистрирован!";
                }
                if ($value['login'] == $login) {
                    $this->_error['login'] = "Пользователь с таким логином уже существует!";
                }
            }
            return true;
        }
        return false;
    }

    function get_reffer ()
    {
        //Если определился реферер
        if( isset($_COOKIE['referer_url']) && isset($_SESSION['referer_url']) )
        {
            preg_match('/[0-9A-z-\.]+\.[A-z]{2,4}/i', $_SESSION['referer_url'], $matches);
            $referrer = strtolower( $matches[0] );

            //Проверяем нет ли уже такого реферера
            $sql = 'SELECT id FROM `referrers` WHERE host = ?';
            $referrerId = $db->selectCell( $sql, $referrer );

            // Если нет - добавляем его в табличку
            if( empty($referrerId) ) {
                $sql = 'INSERT INTO `referrers` (`id`, `host`,`title`) VALUES (null, ?,?)';
                return Core::getInstance()->db->query( $sql, $referrer, $referrer);
            }
        } else {
            return null;
        }
    }

    /**
     * Создаём нового пользователя
     * @return void
     */
    function new_user($name, $login, $password, $confirm, $mail)
    {
        // Если нет ошибок, создаём пользователя
        if (empty($this->_error))
        {
            $referrerId = $this->get_reffer();

            //Добавляем в таблицу пользователей
            $sql = "INSERT INTO users (user_name, user_login, user_pass, user_mail,
                user_created, user_active, user_new, referrerId) VALUES (?, ?, ?, ?, CURDATE(), 1, 0, ?)";
            Core::getInstance()->db->query($sql, $name, $login, $password, $mail, $referrerId);

            //Добавляем его в таблицу не подтверждённых пользователей
            $user_id = mysql_insert_id();

            $this->send_mail_success($name, $login, $password, $mail);

            return array (
                'result' => array (
                    'text' => 'Спасибо, вы зарегистрированы!<br>Теперь вы можете войти в систему.',
                    'redirect' => "https://".URL_ROOT_MAIN."login"
                )
            );
        } else {
            return array (
                'error' => array (
                    'text' => "Обнаружены следующие ошибки:\n" . implode ( ',\n ', $this->_error )
                )
            );
        }
    }
}
