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
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
    }

    /**
     * Отправляет сообщение об ошибке или замечание и предложение администрации сервиса
     * @param string $msg
     * @param array $param
     * @return bool
     */
    public function add_message($msg, $param)
    {
        $user_id = (int)Core::getInstance()->user->getId();
        if (!empty($msg)) {
            $sql = "INSERT INTO feedback_message SET uid=?, user_settings=?, messages=?, user_name=?, `new`='1', rating='0'";
                    $this->db->query($sql, $user_id, serialize($param), $msg, $user_id);

            $numberError = mysql_insert_id();

            $subject = 'Отзыв на сайте easyfinance.ru #' . $numberError;
            $body = htmlspecialchars($msg) . "\n\n<pre>" . var_export($param, true) . '</pre>';
            $responseBody = "<p>Добрый день, уважаемый пользователь!</p>"
                . "<p>Ваша заявка под номером #{$numberError} передана службе поддержки, очень скоро мы свяжемся с вами.</p>"
                . "<p>Спасибо за отзыв, с вами мы сможем сделать лучше, проще и надёжнее!</p>";
            
            // Добавляем ссылку на отправку, если пользователь определён и отсылаем ему автоматическое письмо
            if (isset($_SESSION['user']['user_mail'])) {
                $body .=  '<br/>Письмо от пользователя ' . @$_SESSION['user']['user_name']
                    . ' <a href="mailto:' . $_SESSION['user']['user_mail']
                    . '">' . $_SESSION['user']['user_mail'] . '</a>';

                $response = Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom(array('support@easyfinance.ru' => 'EasyFinance.ru'))
                    ->setTo($_SESSION['user']['user_mail'])
                    ->setBody($responseBody, 'text/html');
                Core::getInstance()->mailer->send($response);
            } else {
                $body .= '<br/>' . $param['email'] . '<br/>Письмо от Анонимного пользователя';
            }

            $message = Swift_Message::newInstance()
                // Заголовок
                ->setSubject($subject)
                // Указываем "От кого"
                ->setFrom(array('support@easyfinance.ru' => 'EasyFinance.ru'))
                // Говорим "Кому"
                ->setTo(array(
                    'max.kamashev@easyfinance.ru'   =>'Maxim Kamashev',
                    'bashokov.ae@easyfinance.ru'    => 'Artur Bashokov'))
                // Устанавливаем "Тело"
                ->setBody($body, 'text/html');

            // Отсылаем письмо
            return Core::getInstance()->mailer->send($message);
        } else {
            return false;
        }
    }

    /**
     * Возвращала раньше рейтинг тестировщиков. Ныне не треба
     * @deprecated
     * @return <type>
     */
    public function get_rlist()
    {
        $sql = "SELECT user_name, SUM(rating) FROM feedback_message GROUP BY uid ORDER BY SUM(rating) DESC";
        $ret = $this->db->select($sql);
        $ret[0]['uid'] = (int)Core::getInstance()->user->getId();
        return $ret;
    }
}
?>
