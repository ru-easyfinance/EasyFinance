<?php

class _User
{
	const TYPE_COMMON=0;
	const TYPE_PRO=2;
	const TYPE_EXPERT=1;
	
	private $model;
	
	function __construct( _User_Model $model )
	{
		$this->model = $model;
	}
	
	public static function load( $id )
	{
		$model = _User_Model::load( (int)$id );
		
		return new _User( $model );
	}
	
	public static function loadByLogin( $login )
	{
		$model = _User_Model::loadByLogin( $login );
		
		return new _User( $model );
	}
	
	public function getId()
	{
		return (int)$this->model->id;
	}
	
	public function getName()
	{
		return $this->model->name;
	}
	
	public function getMail()
	{
		return $this->model->mail;
	}
	
	public function getType()
	{
		return $this->model->type;
	}
	
	public function setPass( $value, $encoded = false )
	{
		$this->model->user_pass = $encoded?$value:sha1($value);
	}
}
