<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для журанала операций
 * @category operation
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Operation_Controller extends Template_Controller
{
    /**
     * Модель класса журнала операций
     * @var Money
     */
    private $model = null;

    /**
     * Ссылка на класс Смарти
     * @var Smarty
     */
    private $tpl = null;

    /**
     * Ссылка на экземпляр класса User
     * @var User
     */
    private $user = null;
    
    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->model = new Operation_Model();
        $this->user = Core::getInstance()->user;
        $this->tpl = Core::getInstance()->tpl;
        $this->tpl->assign('name_page', 'operations/operation');

        $targets = new Targets_Model();
        $this->tpl->assign('targetList',     $targets->getLastList(0, 100));
        $this->tpl->assign('accounts',       $this->user->getUserAccounts());
        $this->tpl->assign('currentAccount', $currentAccount);
        $this->tpl->assign('dateFrom',       date('01.m.Y'));
        $this->tpl->assign('dateTo',         date(date('t').'.m.Y'));
        $this->tpl->assign('category',       get_tree_select());
        $this->tpl->assign('cat_filtr',      get_tree_select(@$_GET['cat_filtr']));

        // Добавляем js и css файлы в начало
        $this->tpl->append('css','jquery/jquery.calculator.css');
        $this->tpl->append('css','jquery/south-street/ui.all.css');
        $this->tpl->append('css','jquery/south-street/ui.datepicker.css');
        $this->tpl->append('js','jquery/ui.core.js');
        $this->tpl->append('js','jquery/ui.datepicker.js');
        $this->tpl->append('js','jquery/jquery.calculator.min.js');
        $this->tpl->append('js','jquery/jquery.calculator-ru.js');
        $this->tpl->append('js','jquery/tag.js');
        $this->tpl->append('js','operation.js');
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
        $array = array('account', 'amount', 'category', 'date', 'comment', 'tags', 'type', 'convert');
        $array = $this->model->checkData($array);
        if (count($this->model->errorData) > 0) {
            // Если есть ошибки, то возвращаем их пользователю в виде массива
            die(json_encode($this->model->errorData));
        }
        $array['drain'] = 1;
        switch ($array['type']) {
            case 0: //Расход
                $array['amount'] = abs($array['amount']) * -1;
                die($this->model->add($array['amount'], $array['date'], $array['category'],
                    $array['drain'], $array['comment'], $array['account'], $array['tags']));
            case 1: // Доход
                $array['drain'] = 0;
                die($this->model->add($array['amount'], $array['date'], $array['category'],
                    $array['drain'], $array['comment'], $array['account'], $array['tags']));
            case 2: // Перевод со счёта
                $array['category'] = -1;
                die($this->model->addTransfer($array['amount'], $array['convert'], $array['date'],
                    $array['account'],$array['toAccount'],$array['comment'],$array['tags']));
            case 3: //
                break;
            case 4: // Перевод на финансовую цель
                $target = new Targets_Model();
                $target->addTargetOperation($array['account'], $array['target'], $array['amount'], 
                    $array['comment'], $array['date'], $array['close']);
                //@FIXME Сделать автоматическое получение нового списка операций, при удачном добавлении
                //die(json_encode($target->getLastList(0, 100)));
                die('[]');
        }

    }

    /**
     * Редактирует событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function edit($args)
    {
        $array = array('id','account', 'amount', 'category', 'date', 'comment', 'tags', 'type', 'convert');
        $array = $this->model->checkData($array);
        if (count($this->model->errorData) > 0) {
            // Если есть ошибки, то возвращаем их пользователю в виде массива
            die(json_encode($this->model->errorData));
        }
        $array['drain'] = 1;
        switch ($array['type']) {
            case 0: //Расход
                $array['amount'] = abs($array['amount']) * -1;
                if ($this->model->edit($array['id'],$array['amount'], $array['date'], $array['category'],
                    $array['drain'], $array['comment'], $array['account'], $array['tags'])) {
                        die('[]');
                    }
            case 1: // Доход
                $array['drain'] = 0;
                if($this->model->edit($array['id'],$array['amount'], $array['date'], $array['category'],
                    $array['drain'], $array['comment'], $array['account'], $array['tags'])) {
                        die('[]');
                    }
            case 2: // Перевод со счёта
                $array['category'] = -1;
                if($this->model->editTransfer($array['id'], $array['amount'], $array['convert'], $array['date'],
                    $array['account'],$array['toAccount'],$array['comment'],$array['tags'])) {
                        die('[]');
                    }
            case 3: // ПРОПУСК
                break;
            case 4: // Перевод на финансовую цель см. в модуле фин.цели
                break;
        }
    }

    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del($args)
    {
        $id = abs((int)$_POST['id']);
        die($this->model->deleteOperation($id));
    }

    /**
     * Получить список
     */
    function listOperations($args)
    {
        $dateFrom = formatRussianDate2MysqlDate(@$_GET['dateFrom']);
        $dateTo = formatRussianDate2MysqlDate(@$_GET['dateTo']);
        $category = (int)@$_GET['category'];
        $account     = (int)@$_GET['account'];
        $array = array();
        $list = $this->model->getOperationList($dateFrom, $dateTo, $category, $account);
        //@TODO Похоже, что тут надо что-то дописать в массиве
        foreach ($list as $val) {
            $array[$val['id']] = $val;
        }
        die(json_encode($array));
    }

    /**
     * Возвращает валюту пользователя
     * @param array $args
     * @return array
     */
    function get_currency($args) {
        die(json_encode($this->model->getCurrency()));
    }
}