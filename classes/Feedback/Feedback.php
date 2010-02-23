<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления фидбэком
 * @copyright http://easyfinance.ru/
 * SVN $Id: $
 */
class Feedback
{
    /**
     * Текст сообщения от пользователя
     * @var string
     */
    private $message = null;

    /**
     * Заголовок для сообщения
     * @var string
     */
    private $title = null;

    /**
     * Массив с параметрами
     * @var array
     */
    private $params = null;

    /**
     * Почта пользователя
     * @var string
     */
    private $email = null;

    /**
     * Массив с ошибками
     * @var array
     */
    public $errorData = array();

    /**
     * Номер отзыва
     * @var int
     */
    private $numberFeedback = null;

    /**
    *
    */
    private $mailer = null;

    /**
     * Конструктор
     * @param string $message
     * @param string $title
     * @param array $params
     * @return void
     */
    function __construct($message, $title, $params) {

        $this->message = $message;

        $this->title = $title;

        $this->params = $params;

        if ( isset( $this->params['email'] )  && !empty ( $this->params['email'] ) ) {
            $this->email = $this->params['email'];
        } else {
            // @TODO Переписать когда класс пользователей будет предоставлять инфу о почте пользователя
            $this->params['email'] = $_SESSION['user']['user_mail'];
            $this->email = $this->params['email'];
        }

	$mailTransport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
		->setUsername('feedback@easyfinance.ru')
		->setPassword('MTApJxa4');
	
	$this->mailer = Swift_Mailer::newInstance( $mailTransport ); 
    }

    /**
     * Отправляет сообщение об ошибке или замечание и предложение администрации сервиса
     * @return bool
     */
    public function add_message( )
    {
        $user_id = (int)Core::getInstance()->user->getId();

        if (!empty($this->message)) {

            // Записываем сообщение об ошибке в БД
            $sql = "INSERT INTO feedback_message SET uid=?, user_settings=?, messages=?, user_name=?, `new`='1', rating='0'";
            Core::getInstance()->db->query($sql, $user_id, serialize($this->params), $this->message, $user_id);
            
            // Получаем номер сообщения
            $this->numberFeedback = mysql_insert_id();

            // Отправляем письмо в саппорт c полным содержанием
            if ( ! $this->sendSupport() ) {
                $this->errorData[] = 'Не удалось отправить письмо службе поддержки';
            }

            // Отправляем письма в службу поддержки с кратким содержанием и с почтой пользователя
            // @FIXME Разобраться, почему гугл игнорирует и подставляет при ответе почту саппорта, а не юзера
            if ( ! $this->sendSubscribers( true ) ) {
                //$this->errorData[] = 'Не удалось отправить письмо службе поддержки';
            }

            // Отправляем сообщение об отзыве - пользователю
            if ( ! $this->sendResponse() ) {
                //$this->errorData[] = 'Не удалось отправить письмо службе поддержки';
            }

            // Отправляем письма для заинтересованных лиц
            if ( ! $this->sendSubscribers( false ) ) {
                //$this->errorData[] = 'Не удалось отправить письмо службе поддержки';
            }
            
            return true;
            
        } else {

            return false;
            
        }
    }

    /**
     * Отправляем ответ пользователю
     */
    private function sendResponse ()
    {
        require DIR_TEMPLATES . '/mail/feedback_user.tpl';

        $response = Swift_Message::newInstance()
            ->setSubject( $responseSubject )
            ->setFrom( $responseFrom )
            ->setTo( $this->email )
            ->setBody( $responseBody, 'text/plain');

        return $this->mailer->send($response);
    }

    /**
     * Отправляем письма подписчикам
     * @param string $fromUser = false
     * @return bool
     */
    private function sendSubscribers( $fromUser = false )
    {
        require DIR_TEMPLATES . '/mail/feedback_subscribers.tpl';

        $subscribers = Swift_Message::newInstance()
            ->setSubject( $subscribersSubject )
            ->setFrom( $subscribersFrom )
            ->setTo( $subscribersEmails )
            ->setBody( $subscribersBody, 'text/plain' );

        if ( $fromUser ) {
            $subscribers->setReplyTo( $this->email )->setSender( $this->email );
        }

        return $this->mailer->send( $subscribers );
    }


    /**
     * Письмо в службу поддержки с полным содержанием и дампом данных
     * @return bool
     */
    private function sendSupport()
    {
        require DIR_TEMPLATES . '/mail/feedback_support.tpl';

        $support = Swift_Message::newInstance()
            // Заголовок
            ->setSubject( $supportSubject )
            // Указываем "От кого"
            ->setFrom( $supportFrom )
            // Говорим "Кому"
            ->setTo( $supportTo )
            // Устанавливаем "Тело"
            ->setBody( $supportBody , 'text/plain' );

        // Отсылаем письмо
        return $this->mailer->send( $support );
    }
}
?>
