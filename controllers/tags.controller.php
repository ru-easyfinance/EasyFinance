<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера тегов
 * @category tags
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Tags_Controller extends Template_Controller
{
    /**
     * Модель класса для управления тегами
     * @var Tags_Model
     */
    private $model = null;

    /**
     * Ссылка на класс Смарти
     * @var Smarty
     */
    private $tpl = null;
    
    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
//        $this->tpl->assign('name_page', 'calendar/calendar');
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
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function add($args)
    {
        die($this->model->add());
    }

    /**
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function edit($args)
    {
        die($this->model->edit());
    }
    
    /**
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del($args)
    {
        die($this->model->del());
    }

    /**
     * Возвращает массив тегов
     * @return void
     */
    function getTags($args) {
        die (json_encode($this->model->getTags(false)));
    }

    /**
     * Возвращает массив тегов с частотой их
     * @return void
     */
    function getCloudTags($args) {
        die (json_encode($this->model->getTags(true)));
    }
}