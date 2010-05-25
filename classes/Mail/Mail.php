<?php

/**
 * Класс для инкапсуляции работы с почтой. aka MessageCollection
 *
 */
class Mail
{
	protected $inbox = array();
	protected $outbox = array();
	protected $drafts = array();
	protected $trash = array();
	
	protected $messages = array();
	
	protected $user;
	
	public function __construct( oldUser $user )
	{
		$this->user = $user;
	}
	
	/**
	 * Возвращает обьект Mail с загруженными письмами для пользователя $user
	 *
	 * @param oldUser $user
	 * @return Mail
	 * @example $mail = Mail::loadAll( $user );
	 */
	public static function loadAll( oldUser $user )
	{
		$mail = new Mail( $user );
		
		$messageModels = Mail_MessageModel::loadAll( $user );
		
		$userIds = array();
		
		// Предзагрузка пользователей, кешируем.
		foreach ( $messageModels as $model )
		{
			if( $mail->user->getId() != $model->sender_id )
			{
				$userIds[] = $model->sender_id;
			}
			if( $mail->user->getId() != $model->receiver_id )
			{
				$userIds[] = $model->receiver_id;
			}
		}
		
		if( sizeof($userIds) )
		{
			//$modelArray = _User_Model::loadByIds( $userIds );
		}
		
		// Загрузка собственно сообщений
		foreach ( $messageModels as $model )
		{
			$mail->add( new Mail_Message( $model, $mail->user ) );
		}
		
		return $mail;
	}
	
	public function getMessage( $id )
	{
		if( array_key_exists( $id, $this->messages ) )
		{
			return $this->messages[ $id ];
		}
		else
		{
			return Mail_Message::load( $this->user, $id);
		}
	}
	
	/**
	 * Метод для создания письма
	 * 
	 * Реализован в коллекции для инкапсуляции и удобного вызова в будущем:
	 * $user = _User::loadCurrent();
	 * $user->mail->createMessage();
	 *
	 * @param _User $receiver
	 * @param string $subject
	 * @param string $body
	 * @param boolean $draft
	 * @return Mail_Message
	 */
	public function createMessage( _User $receiver, $subject, $body, $draft = false )
	{
		// Проверяем не совпадают ли типы пользователей
		if( $receiver->getType() == $this->user->getType )
		{
			throw new Mail_Exception('Send messages between users with same type is not allowed!');
		}
		
		$model = Mail_MessageModel::create( $this->user, $receiver, $subject, $body, $draft );
		
		$message = new Mail_Message( $model, $this->user );
		
		if( !$draft )
		{
			$message->send();
		}
		
		$this->add( $message );
		
		return $message;
	}
	
	public function getInbox()
	{
		return $this->inbox;
	}
	
	public function getOutbox()
	{
		return $this->outbox;
	}
	
	public function getDrafts()
	{
		return $this->drafts;
	}
	
	public function getTrash()
	{
		return $this->trash;
	}
	
	public function getJsonArray()
	{
		$json = array(
			'inbox' 		=> array(),
			'outbox' 	=> array(),
			'drafts' 		=> array(),
			'trash' 		=> array(),
		);
		
		foreach ( array_keys( $json ) as $folder )
		{
			foreach ( $this->$folder as $message)
			{
				$json[ $folder ][ $message->getId() ] = $message->getJsonArray();
			}
		}
		
		return $json;
	}
	
	private function add( Mail_Message $message )
	{
		// Для удобного доступа к сообщению сохраняем в общий массив с ид
		$this->messages[ $message->getId() ] = $message;
		
		// Сортируем сообщения по типам
		
		
		
		if( $message->getTrash() )
		{
			$this->trash[ $message->getId() ] = $message;
		}
		else 
		{
			$folder = self::getFolder( $message );
			
			$this->{$folder}[ $message->getId() ] = $message;
		}
	}
	
	public static function getFolder( Mail_Message $message )
	{
		$folder = null;
		
		$folders = array( 'drafts', 'outbox', 'inbox' );
		
		if( $message->getDraft() )
		{
			$folder = 'drafts';
		}
		elseif ( $message->getDirection() == Mail_Message::OUTGOING )
		{
			$folder = 'outbox';
		}
		elseif ( $message->getDirection() == Mail_Message::INCOMING )
		{
			$folder = 'inbox';
		}
		
		return $folder;
	}
}
