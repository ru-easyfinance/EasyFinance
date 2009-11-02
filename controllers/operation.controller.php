<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для журанала операций
 * @category operation
 * @copyright http://easyfinance.ru/
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
        $this->tpl->assign('dateFrom',       date('d.m.Y', time() - 60*60*24*7));
        $this->tpl->assign('dateTo',         date('d.m.Y')); //date(date('t').'.m.Y'));
        $this->tpl->assign('category',       get_tree_select());
        $this->tpl->assign('cat_filtr',      get_tree_select(@$_GET['cat_filtr']));

        // Добавляем js и css файлы в начало


    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {

    }

  /*  function catincome(){
        //die(json_encode($this->model->catincome()));
        die(get_tree_select());
    }
    function catwaste(){
        //die(json_encode($this->model->catwaste()));
        die(get_tree_select());
    }
*/
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
                if($this->model->add($array['amount'], $array['date'], $array['category'],
                    $array['drain'], $array['comment'], $array['account'], $array['tags'])) {
                        die ('[]');
                    }
            case 1: // Доход
                $array['drain'] = 0;
                if($this->model->add($array['amount'], $array['date'], $array['category'],
                    $array['drain'], $array['comment'], $array['account'], $array['tags'])) {
                        die('[]');
                    }
            case 2: // Перевод со счёта
                $array['category'] = -1;
                if($this->model->addTransfer($array['amount'], $array['convert'], $array['date'],
                    $array['account'],$array['toAccount'],$array['comment'],$array['tags'])) {
                        die('[]');
                    }
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
        $array = array('id','account', 'toAccount','amount', 'category', 'date', 'comment', 'tags', 'type', 'convert');
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
                if($this->model->editTransfer($array['id'], $array['amount']/*, $array['convert']*/, $array['date'],
                    $array['account'],$array['toAccount'],$array['comment'],$array['tags'])) {
                        die('[]');
                    }
            case 3: // ПРОПУСК
                break;
            case 4: // Перевод на финансовую цель см. в модуле фин.цели
                $target = new Targets_Model();
                $target->model->editTargetOperation($array['id'],$array['amount'], $array['date'], $array['category'],
                    $array['drain'], $array['comment'], $array['account'], $array['tags']);
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
    function deleteTargetOp($args)
    {
        $id = abs((int)$_POST['id']);
        die($this->model->deleteTargetOperation($id));
    }

    /**
     * Удаляет выбранные события
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del_all($args)
    {
        $id = explode(',', $_POST['id']);
        foreach ($id as $k=>$v)
            $this->model->deleteOperation($v);
        die('[]');
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