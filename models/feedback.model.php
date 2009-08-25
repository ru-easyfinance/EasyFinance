<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления фидбэком
 * @copyright http://home-money.ru/
 * SVN $Id: $
 */
class Feedback_Model
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
    private $user = NULL;


    //private $last_msg = NULL;
    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user->getId();
        //$uid = Core::getInstance()->user->getId();
        //$this->user = Core::getInstance()->user->getProfile($uid);
    }

    public function add_message($msg,$param)
    {
        $sql = "INSERT INTO feedback_message SET uid=?, user_settings=?, messages=?, user_name=?, `new`='1', rating='0'";
        $this->db->query($sql, $this->user, serialize($param), $msg, $this->user);
        $mail = "Пришло сообщение от тестировщика : ".$this->user."</br>
                $msg </br> id сообщения : ".mysql_insert_id();
        $html_mail = "  <html>
                            <head>
                                <title> сообщение от тестировщика </title>
                            </head>
                            <body>
                                $mail
                            </body>
                        </html>";

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        $headers .= 'To: support@home-money.ru' . "\r\n";
        $headers .= 'From: <'.$this->user['user_mail'].'>' . "\r\n";

        //mail('support@home-money.ru', $this->user['user_mail'], $html_mail, $headers);
    }

    public function get_rlist()
    {
        $sql = "SELECT user_name, SUM(rating) FROM feedback_message GROUP BY uid";
        $ret = $this->db->select($sql);
        $ret[0]['uid']=$this->user;
        return $ret;
    }
}
?>
