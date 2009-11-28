<?php
/**
 * Модель сертификата эксперта
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Certificate_Model
{
	/**
	 * Массив полей модели
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Указатель модифицированности модели (на будущее)
	 *
	 * @var boolean
	 */
	private $durty	= false;
	
	/**
	 * Загрузка сертификатов эксперта по id
	 *
	 * @param integer $userId
	 * @return array
	 */
	public static function loadByUserId( $userId )
	{
		if( !is_int( $userId ) )
		{
			throw new Certificate_Exception( _Core_Exception::typeErrorMessage( $userId, 'User id', 'integer' ) );
		}
		
		$modelsArray = array();
		
		$sql = 'select * from `certificates` where `cert_user_id` = ?';
		
		$rows = Core::getInstance()->db->select($sql, $userId);
		
		foreach ( $rows as $row )
		{
			$model = new Certificate_Model();
			
			$model->fields = $row;
			
			$modelsArray[] = $model;
		}
		
		return $modelsArray;
	}
	
	/**
	 * Создаёт новую модель сертификата
	 *
	 * @param integer $userId
	 * @param string $details
	 * @param string $img
	 * @param string $img_thumb
	 * @return Certificate_Model
	 */
	public static function create( $userId, $details, $img, $img_thumb)
	{
		$sql = 'insert into `certificates` (`cert_id`, `cert_user_id`, `cert_img`, `cert_img_thumb`, `cert_details`, `cert_status`)
		values ( null, ?, ?, ?, ?,0)';
		
		$id = Core::getInstance()->db->query($sql, $userId, $img, $img_thumb, $details);
		
		$model = new self();
		
		$model->fields = array(
			'cert_id' 		=> (int)$id,
			'cert_user_id' 		=> $userId,
			'cert_img' 		=> $img,
			'cert_img_thumb' 	=> $img_thumb,
			'cert_details' 		=> $details,
			'cert_status' 		=> 0
		);
		
		return $model;
	}
	
	/**
	 * Удаление сертификата по Id
	 *
	 * @param integer $certId
	 */
	public static function delete( $certId )
	{
		if( !is_int( $certId ) )
		{
			throw new Certificate_Exception( _Core_Exception::typeErrorMessage( $certId, 'Certificate id', 'integer' ) );
		}
		
		$sql = 'delete from `certificates` where `cert_id` = ?';
		
		Core::getInstance()->db->query($sql, $certId);
	}
	
	public function __get( $variable )
	{
		if( array_key_exists( $variable, $this->fields) )
		{
			return $this->fields[ $variable ];
		}
	}
}