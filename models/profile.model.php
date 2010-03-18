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
     * Массив с ошибками пользователя
     * @var array
     */
    private $errors = array ();

    /**
     * Конструктор
     * @return void
     */
    public function __construct( User $user = null )
    {
        $this->db      = Core::getInstance()->db;

        if ( ! $user ) {

            $this->user_id = Core::getInstance()->user->getId();

        } else {

            $this->user_id = $user->getId();
            
        }
    }

    /**
     * Генерация служебной почты
     * @param User $user
     * @param string $mail
     * @return bool
     */
    public function createServiceMail ( User $user, $mail )
    {

        $sql = "UPDATE users u SET user_service_mail = ? WHERE id = ?";

        // @TODO Дёргать АПИ у аутсорсеров для физического создания ящика

        return (bool) $this->db->query( $sql, $mail, $user->getId() );

    }

    /**
     * Удаляем служебную почту по просьбе пользователя
     * @param User $user
     * @return bool
     */
    public function deleteServiceMail ( User $user )
    {

        $sql = "UPDATE users u SET user_service_mail = '' WHERE id = ?";

        // @TODO Дёргать АПИ у аутсорсеров для физического удаления ящика
        
        return (bool) $this->db->query( $sql, $user->getId() );

    }

    /**
     * Проверяет уникальность служебной почты
     * @param string $mail
     * @return bool
     */
    public function checkServiceEmailIsUnique ( $mail )
    {
        $sql = "SELECT user_service_mail FROM users WHERE user_service_mail=? LIMIT 1;";

        if ( $this->db->selectCell ( $sql, $mail ) ) {

            return false;

        } else {

            return true;
            
        }
    }
    
    private function ident($pass){
        $sql = (sha1($pass) == $_SESSION['user']['user_pass']) && ($pass) && (strlen($pass) > 3) ;
        return $sql;
    }

    private function save($table, $set, $ident = 1){
        $set_str = "";
        foreach($set as $key => $val){
            $set_str .=", `$key`='$val'";
        }
        $set_str .=' ';
        $set_str = substr($set_str, 1);
//        if (!$ident) {
//            return 'nopass';
//        }
        $sql = "UPDATE $table SET $set_str WHERE id=?";
        return $this->db->query($sql,$this->user_id);
    }

    public function subscribe($spamer){
        $sql = "UPDATE users SET getNotify=? WHERE id=? ;";
        return $this->db->query($sql, $spamer, $this->user_id);
    }

    public function mainsettings($mod, $prop = ''){
        $ret = array();
        switch($mod){
            case 'save':
                $mail = $this->subscribe($prop['getNotify']);
                $ident = $this->ident($prop['user_pass']);

                if ($ident){
                    $prop['user_pass'] = $prop['newpass'] ?
                                    sha1($prop['newpass']) :
                                    sha1($prop['user_pass']);
                }else{
                    unset ($prop['user_pass']);
                }
                unset($prop['getNotify']);
                unset($prop['newpass']);

                if ( $prop['guide'] != 1 ) {
                    setCookie("guide","",0,COOKIE_PATH, COOKIE_DOMEN, false);
                }else{
                    setCookie("guide", "uyjsdhf",0,COOKIE_PATH, COOKIE_DOMEN, false); //записываем в кук нужно ли выводить всплывающие подсказки
                }
                unset($prop['guide']);
                
                $ret['profile'] = $this->save('users', $prop, $ident);

//                if ( $prop['help'] == 1 ){
//                     setCookie("help","",0,COOKIE_PATH, COOKIE_DOMEN, false);
//                }else{
//                    setCookie("help", "uyjsdhf",0,COOKIE_PATH, COOKIE_DOMEN, false); //записываем в кук нужно ли выводить всплывающие подсказки
//                }

                
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