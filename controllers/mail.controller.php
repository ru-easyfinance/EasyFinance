<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля почты
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Mail_Controller extends _Core_Controller_User
{
	/**
	 * Инициализация контроллера
	 */
	protected function __init(){}

	/**
	 * Главная
	 */
	public function index()
	{
		$this->tpl->assign('name_page', 'mail/mail');
	}
	
	/**
	 * отдаёт список писем во всех папках
	 */
	public function listall()
	{		
		$mail = Mail::loadAll( Core::getInstance()->user );
		
		$json = $mail->getJsonArray();
		
		exit( json_encode( $json ) );
	}

	/**
	 * отдаёт информацию о письме и помечает его как прочитанное
	 */
	public function get()
	{
		$messageId = (int)$_POST['id'];
		
		$json = array();
		
		if( $messageId )
		{
			try
			{
				$mail = new Mail( Core::getInstance()->user );
				
				$mail->getMessage( $messageId )->setReaded( true );
				
				$json['result'] = array();
			}
			catch (Mail_Exception $e)
			{
				$json['error'] = array('message'=>'Не удалось загрузить сообщение !');
			}
		}
		else
		{
			$json['error'] = array('message'=>'Запрошенного сообщения не существует !');
		}
		
		exit( json_encode($json) );
	}
	
	/**
	 * создаёт письмо
	 */
	public function send()
	{
		$mail 		= new Mail( Core::getInstance()->user );
		
		$receiver 	= _User::load( (int)$_POST['receiverId'] );
		
		$message 	= $mail->createMessage( $receiver, $_POST['subject'], $_POST['body'] );
		
		$json = array(
			'result' => array(
				'text' => 'Письмо успешно отправлено.',
				'outbox' => $message->getJsonArray() 
			)
		);
		
		exit( json_encode($json) );
	}
	
	public function save_draft()
	{
		$mail		= new Mail( Core::getInstance()->user );
		
		if( array_key_exists( 'id', $_POST ) && $_POST['id'] > 0 )
		{
			$message	= $mail->getMessage( $_POST['id'] );
			
			if( $message->getDraft() )
			{
				$message->setBody( $_POST['body'] );
				
				$json = $message->getJsonArray();
			}
			else
			{
				exit(json_encode(array('error'=>array('Некорректный запрос!'))));
			}
		}
		else
		{
			$receiver	= _User::load( (int)$_POST['receiverId'] );
			
			$message 	= $mail->createMessage( $receiver, $_POST['subject'], $_POST['body'], true );
		}
		
		$json = array(
			'result' => array(
				'text'	=> 'Черновик успешно сохранён.',
				'drafts' 	=> $message->getJsonArray()
			) 
		);
		
		exit( json_encode($json));
	}
	
	public function trash()
	{
		$json = array();
		
		$idArray = explode( ',', $_POST['ids'] );
		
		$mail = new Mail( Core::getInstance()->user );
		
		foreach ( $idArray as $messageId )
		{
			$mail->getMessage( $messageId )->setTrash( true );
		}
		
		$json['result'] = array( 'text' => 'Сообщение перемещено в корзину.' );
		
		exit( json_encode($json) );
	}
	
	public function restore()
	{
		$json = array(  );
	
		$idArray = explode( ',', $_POST['ids'] );
		
		$mail = new Mail( Core::getInstance()->user );
		
		foreach ( $idArray as $messageId )
		{
			$mail->getMessage( $messageId )->setTrash( false );
		}
		
		$json['result'] = array( 'text' => 'Сообщение успешно восстановлено.' );
		
		exit( json_encode($json) );
	}
}
