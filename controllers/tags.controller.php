<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера тегов
 * @category tags
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
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
        $tag = trim(htmlspecialchars(@$_POST['tag']));
        die(json_encode($this->model->add($tag)));
    }

    /**
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function edit($args)
    {
        $tag = trim(htmlspecialchars(@$_POST['tag']));
        $old_tag = htmlspecialchars(@$_POST['old_tag']);
        die(json_encode($this->model->edit($tag, $old_tag)));
    }
    
    /**
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del($args)
    {
        $tag = trim(htmlspecialchars(@$_POST['tag']));
        die(json_encode($this->model->del($tag)));
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