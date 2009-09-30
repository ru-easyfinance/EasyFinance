<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля календаря
 * @category calendar
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Calendar_Controller extends Template_Controller
{
    /**
     * Модель класса календарь
     * @var Calendar_Model
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
        $this->tpl->assign('name_page', 'calendar/calendar');
        $this->model = new Calendar_Model();

        // Операция
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        $this->tpl->assign('targetList', $targets->getLastList(0, 100));
        $this->tpl->append('css','jquery/jquery.calculator.css');
        $this->tpl->append('css','jquery/south-street/ui.datepicker.css');
        

        // Календарь
        $this->tpl->append('css','jquery/fullcalendar.css');
        $this->tpl->append('css','jquery/south-street/ui.dialog.css');
        $this->tpl->append('css','jquery/south-street/ui.all.css');

        
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
     * Добавляет новое событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function add($args)
    {
        die($this->model->add());
    }

    /**
     * Редактирует событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function edit($args)
    {
        die($this->model->edit());
    }
    
    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del($args)
    {
        die($this->model->del());
    }

    /**
     * Возвращает список событий, в формате JSON
     * @return void
     */
    function events($args) {
        $start = $_GET['start'];
        $end   = $_GET['end'];
        die ($this->model->getEvents($start, $end));
    }
}