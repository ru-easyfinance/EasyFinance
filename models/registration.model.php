<?php
/**
 * Класс модели для регистрации пользователей
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Registration_Model extends Template_Model {

    /**
     * Активирует пользователя на портале
     * @param $reg_id string Временный ключ для регистрации MD5
     * @return bool
     */
    function activate ($reg_id) {
        $sql = "SELECT user_id, reg_id FROM registration WHERE reg_id = ?;";
        $row = $this->db->selectRow($sql, $reg_id);
        if (!empty($row)) {
            $user_id = $row['user_id'];
            $this->db->query("DELETE FROM registration WHERE reg_id = ?", $reg_id);

            $sql = "UPDATE users SET user_active = '1', user_new = '0' WHERE user_id = ?";
            $this->db->query($sql, $user_id);

            $tpl->assign('good_activation', 'Вы успешно активированы на сайте!');
            return true;
        } else {
            trigger_error('Ключ не верен, или он устарел!', E_USER_WARNING);
            return false;
        }
    }

    /**
     *
     * @return unknown_type
     */
    function new_user () {
        // Проверяем валидность заполненных данных
        $error_text = array();
        $register['name'] = htmlspecialchars(@$_POST['register']['name']);
        if (!empty($_POST['register']['pass']) && !empty($_POST['register']['pass_r'])) {
            if (@$_POST['register']['pass'] == @$_POST['register']['pass_r']) {
                $pass = md5(@$_POST['register']['pass']);
            } else {
                $error_text['pass'] = "Введённые пароли не совпадают!";
            }
        } else {
            $error_text['pass'] = "Введите пароль!";
        }

        if ($this->validate_login(@$_POST['register']['login'])) {
            $register['login'] = @$_POST['register']['login'];
        } else {
            $error_text['login'] = "Неверно введен логин! <i>Логин может содержать только латинские буквы и цифры!</i>";
            $register['login'] = htmlspecialchars(@$_POST['register']['login']);
        }

        if (validate_email(@$_POST['register']['mail'])) {
            $register['mail'] = @$_POST['register']['mail'];
        }else{
            $error_text['mail'] = "Неверно введен e-mail!";
            $register['mail'] = html(@$_POST['register']['mail']);
        }
        if (!@$this->db->query("SELECT user_id FROM users WHERE user_login=?", $register['login'])) {
            $error_text['login'] = "Пользователь с таким логином уже существует!";
        }

        // Если нет ошибок, создаём пользователя
        if (empty($error_text)) {
            $tpl = $this->tpl;
            //Добавляем его в таблицу не подтверждённых пользователей
            $user_id = md5($_SERVER['REMOTE_ADDR'].";".date("Y-m-d h-i-s").";");
            $reg_id = md5($register['mail'].";".date("Y-m-d h-i-s").";");
            $sql = "INSERT INTO registration (user_id, `date`, reg_id) VALUES (?, CURDATE(), ?);";
            $this->db->query($sql, $user_id, $reg_id);

            //Добавляем в таблицу пользователей
            $sql = "INSERT INTO users (user_id, user_name, user_login, user_pass, user_mail,
                user_created, user_active, user_new) VALUES (?,?,?,?,?,CURDATE(),?,?)";
            $this->db->query($sql, $user_id, $register['name'], $register['login'], $pass, $register['mail'], 0, 1);
            $tpl->assign('good_text', 'На указанную вами почту было отправлено письмо с кодом для подтверждения регистрации!');

            $reg_href = URL_ROOT."/registration/activation/".$reg_id;
            $body = "<html><head><title>
                Подтверждение регистрации на сайте домашней бухгалтерии Home-Money.ru
                </title></head>
                <body><p>Здравствуйте, {$register['name']}!</p>
                <p>Ваш e-mail был указан при регистрации в системе.<br/>
                Чтобы завершить регистрацию и активировать свою учетную запись, перейдите по ссылке:</p>
                <p><a href={$reg_href}>{$reg_href}</a></p>

                <p>Для входа в систему используйте:<br>
                Логин: {$register['login']}<br/>
                Пароль: {$_POST['register']['pass']}</p>

                <p>C уважением,<br/>Администрация системы <a href=".URL_ROOT."/>Home-money.ru</a>
                </body>
                </html>";

            $subject = "Подтверждение регистрации на сайте домашней бухгалтерии Home-Money.ru";
            $message = "<html><head><title>From home-money.ru</title></head>
                        <body>
                            <a href=".URL_ROOT."/index.php?modules=reg&id=".$reg.">".URL_ROOT."/index.php?modules=reg&id=$reg</a>
                        </body>
                        </html>";
            $headers = "Content-type: text/html; charset=utf-8\n";
            $headers .= "From: info@home-money.ru\n";
            //TODO
            mail($register['mail'], $subject, $body, $headers);
        }
        $tpl->assign('register', $register);
    }

    /**
     * Проверяет корректность логина
     * @param $login string
     * @return bool
     */
    function validate_login($login = '')
    {
        //TODO Добавить проверку длины пароля
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