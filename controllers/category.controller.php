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
        // Формируем данные для PDA
        $types = array_flip( Category::getTypesArray() );

        if( array_key_exists( 0, $args ) && array_key_exists( $args[0], $types ) )
        {
            $categorysType = $types[ $args[0] ];
        }
        else
        {
            $categorysType = Category::TYPE_WASTE;
        }

        $this->tpl->assign( 'categorysType', $categorysType );

        $this->tpl->assign("sys_categories", $this->model->system_categories);

        // Операция
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('category', get_tree_select());
//        $targets = new Targets_Model();
//        $this->tpl->assign('targetList', $targets->getLastList(0, 100));
    }

    /**
     * Создаёт новую категорию
     * @param $args array mixed
     * @return void
     */
    function add( array $args=array())
    {
        $category = array(
            'system' => 0,
            'parent' => 0,
        );

        $types = array_flip( Category::getTypesArray() );

        if( array_key_exists( 0, $args ) && array_key_exists( $args[0], $types ) )
        {
            $category['type'] = $types[ $args[0] ];
        }
        else
        {
            $category['type'] = Category::TYPE_WASTE;
        }

        if( $this->request->method == 'POST' )
        {
            $errors = array();

            $category = array(
                'name'      => trim($this->request->post['name']),
                'parent'     => (int)$this->request->post['parent'],
                'system'     => (int)$this->request->post['system'],
                'type'        => isset($this->request->post['type'])?$this->request->post['type']:$category['type'],
            );

            if( !$category['name'] )
            {
                $errors[] = 'Не указано название!';
            }

            if( !$category['system'] )
            {
                $errors[] = 'Не указана системная категория!';
            }

            if( !array_key_exists( $category['type'],  Category::getTypesArray() ) )
            {
                $errors[] = 'Некорректный тип категории!';
            }

            if( !sizeof($errors) )
            {
                $category['id'] = $this->model->add($category['name'], $category['parent'], $category['system'], $category['type']);

                $this->tpl->assign( 'result', array('text'=>"Категория успешно добавлена.",'id'=>$category['id']) );
            }
            else
            {
                $this->tpl->assign( 'error', array( 'text' => implode(" \n", $errors) ) );
            }
        }

        $this->tpl->assign( 'category', $category );
        $this->tpl->assign( 'name_page', 'category/edit' );
    }

    /**
     * Редактирует категорию
     * @param $args array mixed
     * @return void
     */
    function edit( array $args=array() )
    {
        $errors = array();

        $categorys = Core::getInstance()->user->getUserCategory();
        $category = null;

        if( array_key_exists( 0, $args ) && array_key_exists( $args[0], $categorys) )
        {
            $category = array(
                'id'         => (int)$args[0],
                'name'     => $categorys[ $args[0] ]['cat_name'],
                'parent'     => $categorys[ $args[0] ]['cat_parent'],
                'system'     => $categorys[ $args[0] ]['system_category_id'],
                'type'         => $categorys[ $args[0] ]['type'],
            );
        }

        if( $this->request->method == 'POST' )
        {
            $category = array(
                'id'         => isset($this->request->post['id'])?$this->request->post['id']:$category['id'],
                'name'      => $this->request->post['name'],
                'parent'     => (int)$this->request->post['parent'],
                'system'     => (int)$this->request->post['system'],
                'type'         => (int)$this->request->post['type'],
            );

            if( !strlen($category['name']) )
            {
                $errors[] = 'Название не может быть пустым!';
            }

            if( !$category['parent'] && !$category['system'] )
            {
                $errors[] = 'Вы должны указать системную или родительскую категорию.';
            }

            if( !sizeof($errors) )
            {
                $this->model->edit(
                $category['id'], $category['name'], $category['parent'], $category['system'], $category['type']);


                $this->tpl->assign( 'result', array('text'=>"Категория успешно изменена.") );
            }
            else
            {
                $this->tpl->assign( 'error', array( 'text' => implode(" \n", $errors) ) );
            }
        }

        $this->tpl->assign( 'category', $category );
        $this->tpl->assign( 'name_page', 'category/edit' );
    }

    /**
     * Удаляет указанную категорию
     * @param $args array mixed
     * @return void
     */
    function del ($args)
    {
        $catId     = 0;

        if(array_key_exists(0 ,$args) && is_numeric($args[0]) && $args[0]) {
            $catId = (int)$args[0];
        } elseif(isset($this->request->post['id']) && $this->request->post['id']) {
            $catId = (int)$this->request->post['id'];
        }

        // Проверяем, есть ли по категории операции
        if (isset($this->request->post['confirm']) &&
            $this->request->post['confirm'] === 'false' &&
            $this->model->getCountOperationByCategory(Core::getInstance()->user, $catId) > 0) {
                die(json_encode(
                        array(
                            "confirm" => array(
                                "text" => "Эта категория содержит операции. " .
                                    "При удалении категории все операции по ней будут удалены!" .
                                    "\n\nВы действительно хотите удалить категорию?",
                                "id" => $catId
                            )
                        )
                ));
        }

        // Если удаление подтверждено....
        if(isset($this->request->get['confirmed']) && $this->request->get['confirmed']) {
            // Отмечаем операции неподтверждёнными
            $operation = new Operation_Model();
            $operation->deleteOperationsByCategory(Core::getInstance()->user, $catId);

            // Удаляем категорию (делаем невидимой)
            if($this->model->del($catId)) {
                $this->tpl->assign('result', array('text' => "Категория успешно удалена.", 'id' => $catId));
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
                'title'         => 'Удаление категории',
                'message'     => 'Вы действительно хотите удалить категорию?',
                'yesLink'    => '/category/del/' . $catId . '?confirmed=1',
                'noLink'     => $_SERVER['HTTP_REFERER'],
            );

            if ($this->model->getCountOperationByCategory(Core::getInstance()->user, $catId) > 0) {
                $confirm['message'] = "<b>Эта категория содержит операции.</b><br/>" .
                    "При удалении категории все операции по ней будут удалены!<br/><br/>" .
                    "Вы действительно хотите удалить категорию?";
            }

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
     *
     * @param array $args
     */
    function getCategory ($args) {
        //@TODO Переключить на новый шаблонизатор, когда клиент будет готов
        //$this->tpl->assign('category', $this->model->getCategory());
        return die(json_encode($this->model->getCategory()));
    }
}