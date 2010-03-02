<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для регистрации пользователей
 * @author Max Kamashev "ukko" <max.kamashev@gmail.com>
 * @author korogen
 * @copyright http://easyfinance.ru/
 * @category registration
 * @version SVN $Id$
 */
class Registration_Model
{
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
     * Создаём нового пользователя
     * @return void
     */
    function new_user () {
        require_once SYS_DIR_LIBS . "external/Swift/swift_required.php";

        $db = Core::getInstance()->db;
        
        // Проверяем валидность заполненных данных
        $error_text = array();
        $register['name'] = htmlspecialchars(@$_POST['name']);
        if (!empty($_POST['password']) && !empty($_POST['confirm_password'])) {
            if (@$_POST['password'] == @$_POST['confirm_password']) {
                $pass = SHA1(@$_POST['password']);
            } else {
                $error_text['pass'] = "Введённые пароли не совпадают!";
            }
        } else {
            $error_text['pass'] = "Введите пароль!";
        }

        if ($this->validate_login(@$_POST['login'])) {
            $register['login'] = @$_POST['login'];
        } else {
            $error_text['login'] = "Неверно введен логин! <i>Логин может содержать только латинские буквы и цифры!</i>";
            $register['login'] = htmlspecialchars(@$_POST['login']);
        }

        if (validate_email(@$_POST['mail'])) {
            $register['mail'] = @$_POST['mail'];
        }else{
            $error_text['mail'] = "Неверно введен e-mail!";
            $register['mail'] = htmlspecialchars(@$_POST['mail']);
        }

        $cell = $db->selectCell("SELECT id FROM users WHERE user_login=?", $register['login']);
        if (!empty($cell)) {
            $error_text['login'] = "Пользователь с таким логином уже существует!";
        }

        $cell = $db->selectCell("SELECT id FROM users WHERE user_mail=?", $register['mail']);
        if (!empty($cell))
        {
            $error_text['login'] = "Пользователь с данным адресом электронной почты уже зарегистрирован!";
        }
	
        // Если нет ошибок, создаём пользователя
        if (empty($error_text))
        {
        		//Если определился реферер
        		if( isset($_COOKIE['referer_url']) && isset($_SESSION['referer_url']) )
        		{
        			preg_match('/[0-9A-z-\.]+\.[A-z]{2,4}/i', $_SESSION['referer_url'], $matches);
        			$referrer = strtolower( $matches[0] );
        			
        			//Проверяем нет ли уже такого реферера
        			$sql = 'select id from `referrers` where host = ?';
        			$referrerId = $db->selectCell( $sql, $referrer );
        			
        			// Если нет - добавляем его в табличку 
        			if( empty($referrerId) )
        			{
        				$sql = 'insert into `referrers` (`id`, `host`,`title`) values (null, ?,?)';
        				$referrerId = $db->query( $sql, $referrer, $referrer);
        			}
        		}
        		else
        		{
        			$referrerId = null;
        		}
        	
            //Добавляем в таблицу пользователей
            $sql = "INSERT INTO users (user_name, user_login, user_pass, user_mail,
                user_created, user_active, user_new, referrerId) VALUES (?, ?, ?, ?, CURDATE(), 1, 0, ?)";
            $db->query($sql, $register['name'], $register['login'], $pass, $register['mail'], $referrerId);

            //Добавляем его в таблицу не подтверждённых пользователей
            $user_id = mysql_insert_id();

            $body = "<html><head><title>
                Вы зарегистрированы в системе управления личными финансами EasyFinance.ru
                </title></head>
                <body><p>Здравствуйте, {$register['name']}!</p>
                <p>Ваш e-mail был указан при регистрации в системе.<br/>

                <p>Для входа в систему используйте:<br>
                Логин: {$register['login']}<br/>
                Пароль: {$_POST['password']}</p>

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
                ->setTo(array($register['mail']=>$register['login']))
                // Устанавливаем "Тело"
                ->setBody($body, 'text/html');
            // Отсылаем письмо
            $result = Core::getInstance()->mailer->send($message);
            return array (
                        'result' => array (
                            'text' => 'Спасибо, вы зарегистрированы!\nТеперь вы можете авторизироваться',
                            'redirect' => "https://".URL_ROOT_MAIN."login"
                        )
                    );
        } else {
            return array (
                'error' => array (
                    'text' => "Обнаружены следующие ошибки:\n" . implode ( ',\n ', $error_text )
                )
            );
        }
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
