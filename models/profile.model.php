<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления фидбэком
 * @copyright http://home-money.ru/
 * SVN $Id: $
 */
class Profile_Model
{
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Информация о пользователе
     * @var Array
     */
    private $user_id = NULL;

    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user_id = Core::getInstance()->user->getId();
    }

    private function ident($pass){
        $sql = "SELECT COUNT(*) FROM users WHERE user_pass=SHA1('$pass') AND id='".$this->user_id."'" ;
        return $sql;
    }

    private function save($table, $set, $ident = 1){
        $set_str = " ";
        foreach($set as $key => $val){
            $set_str .=", `$key`='$val'";
        }
        $set_str = substr($set_str, 1);
        $sql = "UPDATE $table SET $set_str WHERE id=? AND $ident;";
        return $this->db->query($sql,$this->user_id);
    }

    public function mainsettings($mod,$prop){
        $ret = array();
        switch($mod){
            case 'save':
                $ident = $this->ident($prop['user_pass']);
                $prop['user_pass'] = $prop['newpass'] ?
                                    $prop['newpass'] :
                                    $prop['user_pass'];
                unset($prop['newpass']);
                $ret['profile'] = $this->save('users', $prop, $ident);
                break;
            case 'load':
                $ret['profile']['login']=$_SESSION['user']['user_login'];
                $ret['profile']['name']=$_SESSION['user']['user_name'];
                $ret['profile']['mail']=$_SESSION['user']['user_mail'];
                $ret['profile']['currency']=$_SESSION['user_currency'];
                $sql = "SELECT * FROM currency";
                $ret['currency'] = $this->db->select($sql);
                break;
        }
        return json_encode($ret);
    }


    public function currency($mod,$prop){
        $ret = array();
        switch($mod){
            case 'save':
                $ret['profile'] = $this->save('users', $prop);
                break;
            case 'load':
                $ret['profile']['currency']=$_SESSION['user_currency'];
                $sql = "SELECT * FROM currency";
                $ret['currency'] = $this->db->select($sql);
                break;
        }
        return json_encode($ret);
    }
}
?>