<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера тегов
 * @category tags
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Tags_Controller extends _Core_Controller_UserCommon
{
    /**
     * Модель класса для управления тегами
     * @var Tags_Model
     */
    private $model = null;
    
    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->model = new Tags_Model();
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {

    }

    /**
     * Добавляет новый тег
     * @return void
     */
    function add ()
    {
        
        $tag = trim( htmlspecialchars( @$_POST['tag'] ) );

        $tags = $this->model->add( $tag );

        if ( $tags ) {

            $this->tpl->assign( 'result', "Добавлен новый тег" );
            $this->tpl->assign('tags', $tags);

        } else {

            $this->tpl->assign( 'error', array( 'text' => implode(",\n", $this->model->getErrors() ) ) );

        }
    }

    /**
     * Редактирует тег
     * @return void
     */
    function edit ()
    {
        $tag = trim( htmlspecialchars( @$_POST['tag'] ) );
        $old_tag = htmlspecialchars( @$_POST['old_tag'] );

        $tags = $this->model->edit( $tag, $old_tag );

        if ( $tags ) {

            $this->tpl->assign( 'result', "Добавлен новый тег" );
            $this->tpl->assign('tags', $tags);

        } else {

            $this->tpl->assign( 'error', array ( 'text' => implode(",\n", $this->model->getErrors() ) ) );

        }
    }
    
    /**
     * Удаляет тег
     * @return void
     */
    function del ( )
    {
        $tag = trim( htmlspecialchars( @$_POST['tag'] ) );
        $tags = $this->model->del( $tag );

        if ( $tags ) {

            $this->tpl->assign( 'result', "Добавлен новый тег" );
            $this->tpl->assign('tags', $tags);

        } else {

            $this->tpl->assign( 'error', array ( 'text' => implode(",\n", $this->model->getErrors() ) ) );

        }

    }

    /**
     * Возвращает массив тегов
     * @return void
     */
    function getTags($args) {

        $tags = $this->model->getTags( false );
        $this->tpl->assign( 'tags', $tags );

    }

    /**
     * Возвращает массив тегов с частотой их
     * @return void
     */
    function getCloudTags($args) {

        $tags = $this->model->getTags( true );
        $this->tpl->assign( 'tags', $tags );
        
    }
}