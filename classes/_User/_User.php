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
	
	public function getId()
	{
		return (int)$this->model->id;
	}
	
	public function getName()
	{
		return $this->model->name;
	}
	
	public function getType()
	{
		return $this->model->type;
	}
}
