<?php

class Article_Collection extends _Core_Abstract_Collection
{
	public static function loadRange( $start, $count )
	{
		$container = new self();
		
		$modelArray = Article_Model::loadRange( $start, $count );
		
		foreach ( $modelArray as $model )
		{
			$container->container[ (int)$model->id ] = new Article( $model );
		}
		
		return $container;
	}
	
	public function getArray()
	{
		$articlesArray = array();
		
		foreach ( $this->container as $articleId => $article )
		{
			$articlesArray[ $articleId ] = $article->getArray();
		}
		
		return $articlesArray;
	}
}
