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
     */
    function new_user($name, $login, $password, $confirm, $mail)
    {
        $sql = "INSERT INTO users (user_name, user_login, user_pass, user_mail,
            user_created, user_active, user_new, referrerId) VALUES (?, ?, ?, ?, CURDATE(), 1, 0, ?)";
        Core::getInstance()->db->query($sql, $name, $login, sha1($password), $mail, $this->get_reffer());
    }

}
