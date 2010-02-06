<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля категорий
 * @category category
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Category_Controller extends _Core_Controller_UserCommon
{
    /**
     * Ссылка на класс модели категории
     * @var Category_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->tpl->assign('name_page', 'category/category');
        $this->model = new Category_Model();
        
    }

	/**
	 * Индексная страница (список категорий)
	 * @param $args array mixed
	 * @return void
	 */
	function index($args)
	{
		$types = array_flip( Category::getTypesArray() );
		
		if( array_key_exists( 0, $args ) && array_key_exists( $args[0], $types ) )
		{
			$categorysType = $types[ $args[0] ];
		}
		else
		{
			$categorysType = -1;
		}
		
		$this->tpl->assign( 'categorysType', $categorysType );
		
		$this->tpl->assign("sys_categories", $this->model->system_categories);
		
		// Операция
		$this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
		$this->tpl->assign('category', get_tree_select());
		$targets = new Targets_Model();
		$this->tpl->assign('targetList', $targets->getLastList(0, 100));
	}

    /**
     * Создаёт новую категорию
     * @param $args array mixed
     * @return void
     */
    function add($args)
    {
        $name   = htmlspecialchars(@$_POST['name']);
        $parent = (int)@$_POST['parent'];
        $system = (int)@$_POST['system'];
        $type   = (int)@$_POST['type'];

        die(json_encode($this->model->add($name, $parent, $system, $type)));
    }

    /**
     * Редактирует категорию
     * @param $args array mixed
     * @return void
     */
    function edit($args)
    {
        $id     = (int)@$_POST['id'];
        $name   = htmlspecialchars(@$_POST['name']);
        $parent = (int)@$_POST['parent'];
        $system = (int)@$_POST['system'];
        $type   = (int)@$_POST['type'];

        die(json_encode($this->model->edit($id, $name, $parent, $system, $type)));
    }

	/**
	 * Удаляет указанную категорию
	 * @param $args array mixed
	 * @return void
	 */
	function del ($args)
	{
		$catId	 = 0;
		
		if( array_key_exists(0 ,$args) && is_numeric($args[0]) && $args[0] )
		{
			$catId = (int)$args[0];
		}
		elseif( isset($this->request->post['id']) && $this->request->post['id'] )
		{
			$catId = (int)$this->request->post['id'];
		}
		
		// Если удаление подтверждено....
		if( isset($this->request->get['confirmed']) && $this->request->get['confirmed'] )
		{
			if( $this->model->del( $catId ) )
			{
				$this->tpl->assign( 'result', array('text'=>"Категория успешно удалена.") );
			}
			// Исключительная ситуация.
			else
			{
				$this->tpl->assign( 'error', array('text'=> "Не удалось удалить категорию." ) );
			}
			
			//возвращаемся
			if( array_key_exists('redirect', $_SESSION) )
			{
				_Core_Router::redirect( $_SESSION['redirect'],true );
				unset( $_SESSION['redirect'] );
			}
		}
		// Если нет  - показываем форму для подтверждения
		elseif( !isset($request->get['confirmed']) )
		{
			$confirm= array (
				'title' 		=> 'Удаление категории',
				'message' 	=> 'Вы действительно хотите удалить категорию?',
				'yesLink'	=> '/category/del/' . $catId . '?confirmed=1',
				'noLink' 	=> $_SERVER['HTTP_REFERER'],
			);
			
			// Сохраняем в сессии адрес куда идти если согласится
			$_SESSION['redirect'] = $_SERVER['HTTP_REFERER'];
			
			$this->tpl->assign('confirm', $confirm);
			$this->tpl->assign('name_page', 'confirm');
		}
		// Видимо передумали удалять и наша логика не сработала - редиректим на инфо
		else
		{
			_Core_Router::redirect( '/info' );
		}
	}

     /**
     * Возвращает список пользовательских и системных категорий в формате JSON
     * @param array $args
     */
    function getCategory ($args) {
        $start = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $finish = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));

        return die(json_encode($this->model->getCategory($start, $finish)));
    }

    /**
     * Возвращает html-строку для категорий. хак вместо show/hide
     * @deprecated
     */
    function cattypechange($args){
        $type=(int)$_POST['type'];
        die(json_encode($this->model->cattype($type))  );
    }
}