<?php if (!defined('INDEX')) trigger_error("Index required!", E_USER_WARNING);
/**
 * Класс модели для управления фидбэком
 * @copyright http://easyfinance.ru/
 */
class Feedback
{
    /**
     * Текст сообщения от пользователя
     * @var string
     */
    private $_message = null;

    /**
     * Заголовок для сообщения
     * @var string
     */
    private $_title = null;

    /**
     * Массив с параметрами
     * @var array
     */
    private $_params = null;

    /**
     * Почта пользователя
     * @var string
     */
    private $_email = null;

    /**
     * Ссылка на класс-мейлера
     * @var Swift_Mailer
     */
    private $_mailer = null;

    /**
     * Конструктор
     * @param string $message
     * @param string $title
     * @param array $params
     * @return void
     */
    function __construct($message, $title, $params)
    {

        $this->_message = $message;

        $this->_title = $title;

        $this->_params = $params;

        if (isset($this->_params['email'])  && !empty ($this->_params['email'])) {
            $this->_email = $this->_params['email'];
        } else {
            $this->_params['email'] = $_SESSION['user']['user_mail'];
            $this->_email = $this->_params['email'];
        }

        $mailTransport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername(FEEDBACK_MAIL_MAIL)
            ->setPassword(FEEDBACK_MAIL_PASS);

        $this->_mailer = Swift_Mailer::newInstance($mailTransport);
    }

    /**
     * Отправляет сообщение об ошибке или замечание и предложение администрации сервиса
     * @return bool
     */
    public function add_message( )
    {
        if (!empty($this->_message)) {
            // Отправляем письмо в саппорт c полным содержанием
            if (! $this->sendFeedback()) {
                throw new Exception('Cant send feedback email');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Письмо в службу поддержки с полным содержанием и дампом данных
     * @return bool
     */
    private function sendFeedback()
    {
        // Указываем кому высылать будем письмо
        if (isset($_SESSION['user']['user_name'])) {
            if (!empty($_SESSION['user']['user_name'])) {
                $feedbackReplyTo = $_SESSION['user']['user_name'];
            } else {
                $feedbackReplyTo = $_SESSION['user']['user_login'];
            }
        } else {
            //@TODO В идеале можно искать пользователя по этому почтовому ящику в базе
            $feedbackReplyTo = 'Аноним';
        }

        $feedbackReplyTo = array($this->_email => $feedbackReplyTo);

        $feedbackHtmlPart = "<html>".
            "<head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/></head>".
            "<body><pre>".var_export($this->_params, true)."</pre></body>";

        $feedback = Swift_Message::newInstance()
            // Заголовок
            ->setSubject($this->_title)
            // Указываем "От кого"
            ->setFrom(array(FEEDBACK_FROM_MAIL => FEEDBACK_FROM_NAME))
            // Говорим "Кому"
            ->setTo(array(FEEDBACK_TO_MAIL => FEEDBACK_TO_NAME))
            // Указываем от кого пришло письмо
            ->setReplyTo($feedbackReplyTo)
            // Тело письма, его будет видеть пользователь в ответе (и оно будет отображено в хелпдеске)
            ->setBody($this->_message, 'text/plain')
            // Скрытая часть письма с тех. параметрами (т.к. хелпдеск не высылает пользователю эту часть)
            ->addPart($feedbackHtmlPart, 'text/html');

        // Отсылаем письмо
        return $this->_mailer->send($feedback);
    }
}
