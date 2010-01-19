<?php

class Article
{
	protected $model = null;
	
	public function __construct( Article_Model $model )
	{
		$this->model = $model;
	}
	
	public static function load( $id )
	{
		$article = new Article(
			Article_Model::load( $id )
		);
		
		return $article;
	}
	
	public static function create( $title, $announce, $body, $mainImage = null, array $bodyImages = null )
	{
		$article = new Article(
			Article_Model::create( $title, $announce, $body )
		);
		
		return $article;
	}
	
	public function getId()
	{
		return $this->model->id;
	}
	
	public function getTitle()
	{
		return $this->model->title;
	}
	
	public function setTitle()
	{
		
	}
	
	public function getDate()
	{
		return $this->model->date;
	}
	
	public function getAnnounce()
	{
		return $this->model->announce;
	}
	
	public function setAnnounce()
	{
		
	}
	
	public function getBody()
	{
		return $this->model->body;
	}
	
	public function getAuthorName()
	{
		return $this->model->authorName;
	}
	
	public function getAuthorUrl()
	{
		return $this->model->authorUrl;
	}
	
	public function getArray()
	{
		return array(
			'id' 		=> $this->getId(),
			'date'		=> Helper_Date::getFromString( $this->getDate() ),
			'authorName'	=> $this->getAuthorName(),
			'authorUrl'	=> $this->getAuthorUrl(),
			'title' 		=> $this->getTitle(),
			'announce'	=> $this->getAnnounce(),
			'body'		=> $this->getBody(),
		);
	}
}
