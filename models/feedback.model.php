<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления фидбэком
 * @copyright http://easyfinance.ru/
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
        $this->user = (int)Core::getInstance()->user->getId();
        //$uid = Core::getInstance()->user->getId();
        //$this->user = Core::getInstance()->user->getProfile($uid);
    }

    public function add_message($msg,$param)
    {
        if (!empty($msg)) {
            $sql = "INSERT INTO feedback_message SET uid=?, user_settings=?, messages=?, user_name=?, `new`='1', rating='0'";
                    $this->db->query($sql, $this->user, serialize($param), $msg, $this->user);

            $subject = 'Сообщение об ошибке на сайте easyfinance.ru #'.mysql_insert_id();
            $body = stripslashes($msg) . "\n\n" . var_export($param, true);

             $message = Swift_Message::newInstance()
                // Заголовок
                ->setSubject($subject)
                // Указываем "От кого"
                ->setFrom(array('support@easyfinance.ru' => 'EasyFinance.ru'))
                // Говорим "Кому"
                ->setTo(array(
                	'max.kamashev@gmail.com'	=>'Maxim Kamashev',
                	'bashokov.ae@easyfinance.ru' 	=> 'Artur Bashokov'
                ))
                // Устанавливаем "Тело"
                ->setBody($body, 'text/html');
            // Отсылаем письмо
            $result = Core::getInstance()->mailer->send($message);
            
        } else {
            exit;
        }
    }

    public function get_rlist()
    {
        $sql = "SELECT user_name, SUM(rating) FROM feedback_message GROUP BY uid ORDER BY SUM(rating) DESC";
        $ret = $this->db->select($sql);
        $ret[0]['uid']=$this->user;
        return $ret;
    }
}
?>
