<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);

/**
 * Статьи
 * @category articles
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */
class Articles_Controller extends _Core_Controller
{
	/**
	 * Конструктор класса
	 * @return void
	 */
	protected function __init()
	{	
		$this->tpl->assign('no_menu', '1');
	}
	
	function index( $args = array() )
	{
		$articleId = false;
		
		if( is_array($args) && array_key_exists(0,$args) && is_numeric( $args[0] ) )
		{
			$articleId = (int)$args[0];
		}
		
		if( $articleId )
		{
			$article = Article::load( $articleId );
			
			$this->tpl->assign( 'article', $article->getArray() );
		
			$this->tpl->assign('name_page', 'articles/page');
		}
		// Если не передан ид статьи - возвращаем первую страницу
		else
		{
			$this->page();
		}
	}
	
	function page( $args = array() )
	{
		if( !isset($args) || !is_array($args) || !isset($args[0]) )
		{
			$pageNum = 1;
		}
		else
		{
			$pageNum = (int)$args[0];
		}
		
		$itemsPerPage = 20;
		
		$itemsTotal = Article_Model::loadTotalNum();
		
		$pagesTotal = ceil( $itemsTotal / $itemsPerPage );
		
		if( $pagesTotal > 0 && ($pageNum > $pagesTotal || $pageNum < 1) )
		{
			//throw 404
			header('Location: /articles');
		}
		
		$container = Article_Collection::loadRange( ($pageNum - 1) * $itemsPerPage , $itemsPerPage );
		
		$this->tpl->assign( 'articles', $container->getArray() );
		
		// Постраничная навигация
		$pager = Helper_Pager::generateSimple( $pagesTotal, $pageNum, '/articles/page/' );
		
		$this->tpl->assign( 'pager', $pager );
		
		$this->tpl->assign('name_page', 'articles/list');
	}
	
	function __call( $method, $args)
	{
		if( method_exists( $this, $method) )
		{
			call_user_method( $method, $this, $args );
		}
		else
		{
			array_unshift($args, $method);
			call_user_method('index', $this, $args);
		}
	}
}
