<?php

class Mail_MessageModel extends _Core_Abstract_Model
{
	/**
	 * Храним ид овнера для всяческих операций.
	 *
	 * @var integer
	 */
	private $ownerId = null;
	
	protected function __construct( array $row, User $owner )
	{
		$this->ownerId = $owner->getId();
		
		$this->fields = $row;
	}
	
	/**
	 * Загрузка всех сообщений связанных с пользователем
	 *
	 * @param integer $userId
	 * 
	 * @return array Массив моделей сообщений
	 */
	public static function loadAll( User $user )
	{		
		$modelsArray = array();
		
		//$cache = _Core_Cache::getInstance();
		$cacheId = 'mailUser' . $user->getId();
		
		// Проверка наличия в кеше идентификаторов сообщений пользователя
		//$messageIds = $cache->get( $cacheId );
		// Если есть - запрашиваем их все из кеша
		//if ( $messageIds && is_array($messageIds) )
		//{
		//	$modelsArray = $cache->getMulti( $messageIds );
		//}
		
		$sql = 'select m.*, trash
			from messages_state ms
			left join messages as m on m.id = ms.message_id
			where user_id = ?';
		
		$rows = Core::getInstance()->db->select($sql, $user->getId() );
		
		foreach ( $rows as $row )
		{
			$model = new Mail_MessageModel( $row, $user );
			
			$modelsArray[] = $model;
		}
		
		// Cохранение моделей в кеш
		//$cache->set( $cacheId, $modelsArray );
		
		return $modelsArray;
	}
	
	/**
	 * Загрузка сообщения с id  = $messageId
	 *
	 * @param int $messageId
	 * @return Mail_MessageModel
	 * @example Mail_MessageModel::load( $id );
	 */
	public static function load( $messageId, User $owner )
	{
		if( !is_int( $messageId ) )
		{
			throw new Certificate_Exception( _Core_Exception::typeErrorMessage( $messageId, 'Message id', 'integer' ) );
		}
		
		$sql = 'select m.*, trash
			from messages_state ms
			left join messages as m on m.id = ms.message_id
			where ms.user_id=? and ms.message_id=?';
		
		$row = Core::getInstance()->db->selectRow($sql, $owner->getId(), $messageId);
		
		if( !is_array($row) )
		{
			throw new Mail_Exception('Message with id ' . $messageId . ' do not exists!');
		}
		
		$model = new Mail_MessageModel( $row, $owner );
		
		return $model; 
	}
	
	/**
	 * Создание нового сообщения
	 *
	 * @param User $sender Обьект отправителя
	 * @param _User $reciever Обьект получателя
	 * @param string $subject Тема сообщения
	 * @param string $body Тело сообщения
	 * @param boolean $draft Указатель черновика
	 */
	public static function create( User $sender, _User $receiver, $subject, $body, $draft = false)
	{
		$sql = 'insert into messages 
			(id, sender_id, receiver_id, subject, date, body, draft)
			values
			(null, ?, ?, ?, null, ?, ?)';
		
		$messageId = Core::getInstance()->db->query( $sql, $sender->getId(), $receiver->getId(), $subject, $body, (int)$draft );
		
		$row = array(
			'id' 		=> (int)$messageId,
			'sender_id' 	=> $sender->getId(),
			'receiver_id' 	=> $sender->getId(),
			'subject'	=> $subject,
			'body' 		=> $body,
			'draft'		=> $draft?true:false,
			'trash'		=> false
		);
		
		$model = new Mail_MessageModel( $row, $sender );
		
		// С названием конечно запарочка вышла
		$model->addReceiver( $sender );
		
		return $model;
	}
	
	public function addReceiver( $user )
	{
		$sql = 'insert into messages_state (message_id, user_id, trash)
			values (?,?,0)';
		
		Core::getInstance()->db->query( $sql, $this->id, $user->getId() );
		
		return $this;
	}
	
	public function save()
	{
		// Выходим если не было модификаций модели
		//if( !$this->durty ) return true;
		
		// Сохранение в кеш
		
		// Сохранение в бд
		$sql = 'update messages
			set subject = ?, body = ?, readed = ?, draft = ? where id=?';
		
		Core::getInstance()->db->query( $sql, $this->subject, $this->body, (int)$this->readed, $this->draft, $this->id );
		
		// Ухх извраат
		
		$sql = 'update messages_state set trash = ? where message_id=? and user_id=?';
		
		Core::getInstance()->db->query( $sql, $this->trash, $this->id, $this->ownerId );
	}
	
	public function delete()
	{
		//do nothing yet
	}
}
