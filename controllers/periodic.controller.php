<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля периодических транзакций
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @category periodic
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Periodic_Controller extends Template_Controller
{
    /**
     * Модель класса календарь
     * @var Periodic_Model
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
        $this->model = new Periodic_Model();
        $this->tpl = Core::getInstance()->tpl;

        // Операция
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        $this->tpl->assign('targetList', $targets->getLastList(0, 100));


        $this->tpl->assign('name_page', 'periodic/periodic');
        
        



    }
    
    /**
     * Индексная страница
     * @param array $args mixed
     * @return void
     */
    function index($args)
    {
        $this->tpl->assign('category',get_tree_select());
        $this->tpl->assign('account',Core::getInstance()->user->getUserAccounts());
        
    }
    
    /**
     * Возвращает весь список регулярных транзакций в формате json
     * @param array $args mixed
     * @param mixed $args 
     */
    function getList($args)
    {
        die(json_encode($this->model->getList()));
    }
    
    /**
     * Добавляет регулярную транзакцию
     * @param array $args mixed
     * @return int Ид добавленной транзакции
     */
    function add($args)
    {
        $array = $this->model->checkData();
        if (count($this->model->error) == 0) {
            die(json_encode($this->model->add($array['account'],$array['amount'],$array['category'],
                $array['comment'],$array['counts'],$array['date'],$array['infinity'],$array['repeat'],
                $array['title'],$array['drain'])));
        } else {
            die(json_encode($this->model->error));
        }
    }

    /**
     * Редактирует периодическую транзакцию
     * @param array $args mixed
     * @return void
     */
    function edit()
    {
        $array = $this->model->checkData();
        $array['id'] = (int)@$_POST['id'];
        if ($array['id'] == 0) {
            $this->model->error['id'] = "Не указан id транзакции";
        }
        
        if (count($this->model->error) == 0) {
            die(json_encode($this->model->edit($array['id'], $array['account'],$array['amount'],
                $array['category'],$array['comment'],$array['counts'],$array['date'],
                $array['infinity'],$array['repeat'],$array['title'],$array['drain'])));
        } else {
            die(json_encode($this->model->error));
        }
    }

    /**
     * Удаляет периодическую транзакцию
     * @param array $args mixed
     * @return void
     */
    function del()
    {
        $this->model->error = array();
        $array['id'] = (int)@$_POST['id'];
        if ($array['id'] == 0) {
            $this->model->error['id'] = "Не указан id транзакции";
        }
        if (count($this->model->error) == 0) {
            die(json_encode($this->model->del($array['id'])));
        } else {
            die(json_encode($this->model->error));
        }
    }
}