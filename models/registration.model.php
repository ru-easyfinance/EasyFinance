<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для регистрации пользователей
 * @author Max Kamashev "ukko" <max.kamashev@gmail.com>
 * @author korogen
 * @copyright http://home-money.ru/
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
            header('Location: /#activate');exit;
            return true;
        } else {
            //trigger_error('Ключ не верен, или он устарел!', E_USER_WARNING);
            return false;
        }
    }

    /**
     * Создаём нового пользователя
     * @return unknown_type
     */
    function new_user () {
        $db = Core::getInstance()->db;
        $tpl = Core::getInstance()->tpl;
        
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

        // Если нет ошибок, создаём пользователя
        if (empty($error_text)) {
            
            //Добавляем в таблицу пользователей
            $sql = "INSERT INTO users (user_name, user_login, user_pass, user_mail,
                user_created, user_active, user_new) VALUES (?, ?, ?, ?, CURDATE(), 0, 1)";
            $db->query($sql, $register['name'], $register['login'], $pass, $register['mail']);

            //Добавляем его в таблицу не подтверждённых пользователей
            $user_id = mysql_insert_id();
            $reg_id  = SHA1($register['mail'].";".date("Y-m-d h-i-s").";");
            $sql     = "INSERT INTO registration (user_id, `date`, reg_id) VALUES (?, NOW(), ?);";
            $db->query($sql, $user_id, $reg_id);

            //$tpl->assign('good_text', 'На указанную вами почту было отправлено письмо с кодом для подтверждения регистрации!');

            $reg_href = 'https://'.URL_ROOT."registration/activate/".$reg_id;
            $body = "<html><head><title>
                Подтверждение регистрации на сайте домашней бухгалтерии Home-Money.ru
                </title></head>
                <body><p>Здравствуйте, {$register['name']}!</p>
                <p>Ваш e-mail был указан при регистрации в системе.<br/>
                Чтобы завершить регистрацию и активировать свою учетную запись, перейдите по ссылке:</p>
                <p><a href={$reg_href}>{$reg_href}</a></p>

                <p>Для входа в систему используйте:<br>
                Логин: {$register['login']}<br/>
                Пароль: {$_POST['password']}</p>

                <p>C уважением,<br/>Администрация системы <a href='https://".URL_ROOT."' />EasyFinance.ru</a>
                </body>
                </html>";

            $subject = "Подтверждение регистрации на сайте домашней бухгалтерии EasyFinance.ru";
            $headers = "Content-type: text/html; charset=utf-8\n";
            $headers .= "From: info@easyfinance.ru\n";
            //TODO
            //echo 2;
            mail($register['mail'], $subject, $body, $headers);
            header('Location: /registration/#send');exit;
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
