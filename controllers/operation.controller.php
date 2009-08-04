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
     * @var <Money>
     */
    private $model = null;

    /**
     * Ссылка на класс Смарти
     * @var <Smarty>
     */
    private $tpl = null;

    /**
     * Ссылка на экземпляр класса User
     * @var <User>
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
        $this->tpl->assign('name_page', 'operation');

        $this->tpl->assign('accounts', $this->user->getUserAccounts());
        $this->tpl->assign('currentAccount', $currentAccount);
        $this->tpl->assign('dateFrom', date('01.m.Y'));
        $this->tpl->assign('dateTo', date(date('t').'.m.Y'));
        $this->tpl->assign('category', get_tree_select());
        $this->tpl->assign('cat_filtr', get_tree_select(@$_GET['cat_filtr']));
/*
        $parent_category[0]['cat_name'] = "";
        for($i=0; $i < count($_SESSION['user_category']); $i++) {
            if ($_SESSION['user_category'][$i]['cat_parent']==0) {
                $parent_category[$_SESSION['user_category'][$i]['cat_id']]['parent_name'] = $_SESSION['user_category'][$i]['cat_name'];
            }
        }
 */
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
//        $often = $cat->getOftenCategories($_SESSION['user']['user_id']);
//        $list = $cat->loadUserTree($_SESSION['user']['user_id']);
//        $tpl->assign('often', $often);
//        $tpl->assign('list', $list);
    }

    /**
     * Добавляет новое событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return <void>
     */
    function add($args)
    {
        $array = array('account', 'amount', 'category', 'date', 'comment', 'tags', 'type');
        $array = $this->model->checkData($array);
        if (count($this->model->errorData) > 0) {
            // Если есть ошибки, то возвращаем их пользователю в виде массива
            die(json_encode($this->model->errorData));
        }
        $array['drain'] = 1;
        switch ($array['type']) {
            case 0: //Расход
                $array['amount'] = abs($array['amount']) * -1;
                break;
            case 1: // Доход
                $array['drain'] = 0;
                break;
            case 2: // Перевод со счёта
                break;
            case 3: //
                break;
            case 4: // Перевод на финансовую цель
                break;
        }
        // The first two headers prevent the browser from caching the response (a problem with IE
        //and GET requests) and the third sets the correct MIME type for JSON.
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 22 May 1983 00:00:00 GMT');
        header('Content-type: application/json');
        die($this->model->add($array['amount'], $array['date'], $array['category'], $array['drain'], $array['comment'], $array['account'], $array['tags']));
    }

    /**
     * Редактирует событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return <void>
     */
    function edit($args)
    {
        
    }

    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return <void>
     */
    function del($args)
    {
        
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
        $list = $this->model->getOperationList($dateFrom, $dateTo, $category, $account);
        //@TODO Похоже, что тут надо что-то дописать в массиве
        foreach ($list as $val) {
            $array[] = $val;
        }
        die(json_encode($array));
    }
}