<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления фидбэком
 * @copyright http://easyfinance.ru/
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
        $sql = (int)(sha1($pass) == $_SESSION['user']['user_pass']) ;
        return $sql;
    }

    private function save($table, $set, $ident = 1){
        $set_str = "";
        foreach($set as $key => $val){
            $set_str .=", `$key`='$val'";
        }
        $set_str .=' ';
        $set_str = substr($set_str, 1);
        if (!$ident) {
            return 'nopass';
        }
        $sql = "UPDATE $table SET $set_str WHERE id=? AND $ident;";
        return $this->db->query($sql,$this->user_id);
    }

    public function mainsettings($mod, $prop = ''){
        $ret = array();
        /*if ( $prop['help'] == 1 ){
            setCookie("help","",0,"/");      
        }else{
            setCookie("help", "124",0,"/"); //записываем в кук нужно ли выводить всплывающие подсказки
        }*/
        switch($mod){
            case 'save':
                $ident = $this->ident($prop['newpass']);
                $prop['user_pass'] = $prop['newpass'] ?
                                    sha1($prop['newpass']) :
                                    sha1($prop['user_pass']);
                unset($prop['newpass']);
                $ret['profile'] = $this->save('users', $prop, $ident);

//                if ( $prop['help'] == 1 ){
//                     setCookie("help","",0,COOKIE_PATH, COOKIE_DOMEN, false);
//                }else{
//                    setCookie("help", "uyjsdhf",0,COOKIE_PATH, COOKIE_DOMEN, false); //записываем в кук нужно ли выводить всплывающие подсказки
//                }

                if ( $prop['guide'] != 1 ){
                     setCookie("guide","",0,COOKIE_PATH, COOKIE_DOMEN, false);
                }else{
                    setCookie("guide", "uyjsdhf",0,COOKIE_PATH, COOKIE_DOMEN, false); //записываем в кук нужно ли выводить всплывающие подсказки
                }
                break;
            case 'load':
                $ret['profile']['login']=$_SESSION['user']['user_login'];
                $ret['profile']['name']=$_SESSION['user']['user_name'];
                $ret['profile']['mail']=$_SESSION['user']['user_mail'];
                break;
        }
        return json_encode($ret);
    }

    public function cook(){
        setCookie("guide","",0,COOKIE_PATH, COOKIE_DOMEN, false);
    }

    public function currency($mod, $prop = '')
    {
        $ret = array();
        switch($mod) {
            case 'save':
                $ret['profile'] = $this->save('users', $prop);
                Core::getInstance()->user->initUserCurrency($prop['user_currency_list'], $prop['user_currency_default']);
                Core::getInstance()->user->save();
                break;
            case 'load':
                $ret['profile']['currency']=$_SESSION['user_currency'];
                $ret['currency'] = array();
                foreach (Core::getInstance()->currency as $key => $val) {
                    $ret['currency'][$key]=$val;
                }
                break;
        }
        return json_encode($ret);
    }
}
?>