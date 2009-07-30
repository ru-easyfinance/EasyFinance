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
        $this->tpl->assign('dateTo', date('d.m.Y'));
        $this->tpl->assign('categories', get_tree_select(1)); //@FIXME
       
        $parent_category[0]['cat_name'] = "";
        for($i=0; $i < count($_SESSION['user_category']); $i++) {
            if ($_SESSION['user_category'][$i]['cat_parent']==0) {
                $parent_category[$_SESSION['user_category'][$i]['cat_id']]['parent_name'] = $_SESSION['user_category'][$i]['cat_name'];
            }
        }
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
        foreach ($list as $val) {
            $array[] = $val;
        }
        die(json_encode($array));
    }
}