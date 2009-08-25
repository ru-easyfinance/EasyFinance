<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля периодических транзакций
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @category periodic
 * @copyright http://home-money.ru/
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
    public function __construct()
    {
        $this->model = new Periodic_Model();
        $this->tpl = Core::getInstance()->tpl;
        $this->tpl->assign('name_page', 'periodic/periodic');
        //$this->tpl->assign('js','jquery/jquery.calculator.min.js');
//        $this->tpl->assign('js','jquery/jquery.calculator-ru.js');
//        $this->tpl->assign('js','periodic.js');
//        $this->tpl->assign('css','jquery/jquery.calculator.css');
    }
    
    /**
     * Индексная страница
     * @param array $args mixed
     * @return void
     */
    public function index($args)
    {
		//$this->tpl->assign('periodic', $this->model->getAllPeriodic());
    }

    /**
     * Добавляет регулярную транзакцию
     * @param array $args mixed
     * @return int Ид добавленной транзакции
     */
    public function add($args)
    {
        $this->tpl->assign("page_title","periodic add");
        $categories_select = get_three_select($_SESSION['user_category']); //@FIXME Поправить
        $this->tpl->assign('categories_select', $categories_select, 0, 0); //@FIXME Поправить

        $arr = array(
            array('id'=>1, 'name'=>'Первый фейковый счёт'),
            array('id'=>6824, 'name'=>'Второй поддельный счёт'),
        );
        $this->tpl->assign('bills_select', $arr);
        //$this->tpl->assign('bills_select', $_SESSION['user_account']); //@FIXME Добавить получение счетов
        
        if (!empty($_POST['periodic'])) {
            $this->model->add();
        }
    }

    /**
     * Редактирует периодическую транзакцию
     * @param int $id Ид транзакции которую нужно отредактировать
     * @return void
     */
    public function edit($id)
    {
        $this->tpl->assign("page_title","periodic edit");
		$this->tpl->assign('bills_select', $_SESSION['user_account']);
        $id = (int)$id;    
        if (id > 0) {
			$getPeriodic = $this->model->getSelectPeriodic($id);
			$getPeriodic['money'] = abs($getPeriodic['money']);
			$this->tpl->assign('periodic', $getPeriodic);
			$categories_select = get_three_select($_SESSION['user_category'], 0, $getPeriodic['cat_id']);
			$this->tpl->assign('categories_select', $categories_select, 0, 0);
		}
		if (!empty($_POST['periodic'])) {
            $this->model->edit();
		}
    }
}