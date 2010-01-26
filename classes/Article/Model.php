<?php

/**
 * Данные статьи
 *
 */
class Article_Model extends _Core_Abstract_Model
{
	protected static $fieldsDeclare = array(
		'id'		=> null,
		'date' 		=> null,
		'userId' 	=> null,
		'authorName' => null,
		'authorUrl' 	=> null,
		'title' 		=> null,
		'description' 	=> null,
		'keywords'	=> null,
		'announce'	=> null,
		'body'		=> null,
	);
	
	public static function load( $articleId )
	{
		if( !is_int( $articleId ) )
		{
			throw new Article_Exception( _Core_Exception::typeErrorMessage( $articleId, 'Article id', 'integer' ) );
		}
		
		$sql = 'select ' . implode(', ', array_keys( self::$fieldsDeclare ) ) .
			' from articles
			where id=?';
		
		$row = Core::getInstance()->db->selectRow($sql, $articleId);
		
		if( !is_array( $row ) )
		{
			throw new Article_Exception('Article with id ' . $articleId . ' does not exist!');
		}
		
		return new Article_Model($row);
	}
	
	public static function loadTotalNum()
	{
		$sql = 'select count(id) from articles WHERE status=1';
		
		$num = Core::getInstance()->db->selectCell( $sql );
		
		return $num;
	}
	
	public static function loadRange( $start, $count, $orderBy = 'date', $asc = false )
	{
		if( !is_int( $start ) )
		{
			throw new Article_Exception( _Core_Exception::typeErrorMessage( $start, 'Start number', 'integer' ) );
		}
		
		if( !is_int( $count ) )
		{
			throw new Article_Exception( _Core_Exception::typeErrorMessage( $count, 'Count', 'integer' ) );
		}
		
		if( !array_key_exists( $orderBy, self::$fieldsDeclare ) )
		{
			throw new Article_Exception('Field "' . $orderBy . '" specified for sorting does not exist!');
		}
		
		$sql = 'select ' . implode(', ', array_keys( self::$fieldsDeclare ) ) .
			' from articles WHERE status=1
			order by `' . $orderBy . '` ' . ($asc?'asc':'desc')
			. ' limit ' . $start . ', ' . $count ;
		
		$rows = Core::getInstance()->db->select( $sql, $start, $count );
		
		$modelsArray = array();
		
		foreach ( $rows as $row )
		{
			$model = new Article_Model( $row );
			
			$modelsArray[] = $model;
		}
		
		return $modelsArray;
	}
	
	public static function loadAll()
	{
		
	}
	
	public function loadByOwner( _User $user )
	{
		$user->getId();
	}
	
	public static function create()
	{
		
	}
	
	public function save()
	{
		
	}
	
	public function delete()
	{
		
	}
}
