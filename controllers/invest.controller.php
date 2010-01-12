<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля инвестиционных активов пользователя
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
 */

class Invest_Controller extends _Core_Controller_UserCommon
{

    /**
     * Ссылка на класс User
     * @var User
     */
    private $user = null;

    /**
     * Ссылка на класс модель
     * @var Invest_Model
     */
    private $model = null;	

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->user  = Core::getInstance()->user;
        $this->model = new Invest_Model();
        $this->tpl->assign('name_page', 'invest/portfolio');
    }
    
    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
        $cur=Core::getInstance()->user->getUserCurrency();
        $cur_k=array_shift($cur);
        $this->tpl->assign("page_title", "account all");
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('type_accounts', $this->model->getTypeAccounts());
        $this->tpl->assign("template", "default");
        $this->tpl->assign("cur", json_encode($cur_k['abbr']));

        // Операция
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        //$this->tpl->assign('targetList', $targets->getLastList(0, 100));
    }
    
    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
        if ($_SESSION['account'] == "reload") { //@FIXME Переписать эту конструкцию
            $this->user->initUserAccount($this->user->getId());
            $this->user->save();
            unset($_SESSION['account']);
        }
        $cur=Core::getInstance()->user->getUserCurrency();
        $cur_k=array_shift($cur);
        $this->tpl->assign("page_title", "account all");
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('type_accounts', $this->model->getTypeAccounts());
        $this->tpl->assign("template", "default");
        $this->tpl->assign("cur", json_encode($cur_k['abbr']));

        // Операция
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        //$this->tpl->assign('targetList', $targets->getLastList(0, 100));


    }
	
    /**
     * Выбирает параметры счета при его создании
     * @param $args
     * @return array
     */
    function changeType()
    {
        $this->tpl->assign("page_title","account add");
        $id = (int)$_POST['id']; //@TODO переписать на GET, там где нам нужно только получить данные, в соответствии с идеологией REST
        $accid = (int)$_POST['accid'];
        $this->model->newEmptyBill($id);
        $this->tpl->assign("fields", $this->model->formatFields($accid));
        $this->tpl->assign("type_id", $id);
        $c_arr=Core::getInstance()->user->getUserCurrency();
        $arr = array();
        $i=0;
        foreach ($c_arr as $key=>$val) {
            if (is_array($val)) {
                $arr[$i]['name']=$val['abbr'];
                $arr[$i]['key']=$key;
                $i++;
            }
        }
         //die(print_r($arr));
        $this->tpl->assign("currency", $arr);
        $this->tpl->assign("accountcurrency", $this->model->GetAccountCurrencyById($accid));

        die($this->tpl->fetch("accounts/accounts.fields.html"));
    }

   /* function newacclogic(){
        die ( json_encode($this->model->newaccmlogic()) );
    }
*/
    /**
     * Добавляет новый счёт пользователя
     * @param $args
     * @return bool
     */
    function add()
    {
        $this->tpl->assign("page_title","account add");
        $this->tpl->assign('currency', Core::getInstance()->user->getUserCurrency());
        
        //$this->accountslist();
        die (json_encode($this->model->add($_POST)));
    }
	
	/**
     * Удаляет указанный счет
     * @param $args array mixed
     * @return void
     */
    function del ($args)
    {
        $id = $_POST['id'];
        $del = $this->model->deleteAccount($id);
        if ((string)$del=='cel'){
            $this->tpl->assign("error", "Невозможно удалить накопительный счёт!");
            die (json_encode( array ('error' => array('text'=>"Невозможно удалить накопительный счёт!"))));
        }
        if (!$del) {
            $this->tpl->assign("error", "Счет не удален");
        }
        die (json_encode( array ('result' => array('text'=>"Счёт удален"))));
    }

    /**
     * Функция которая отсылает список счетов 
     */
    public function accountslist()
    {
        die(json_encode($this->model->accounts_list()));
    }

    public function get_fields()
    {
        $id = (int)$_POST['id'];
        $aid = (int)$_POST['aid'];
        //die('a'.strval($id).'a');
        $this->model->get_fields($id, $aid);
    }

    function edit()
    {
        //die ('123');
        $id = $_POST['id'];
        $this->model->deleteAccount($id);
        $this->model->add($_POST);
        die ($id);
    }

    function correct()
    {
        $this->tpl->assign('currency', Core::getInstance()->user->getUserCurrency());//
        $qString = urldecode($_POST['qString']);
        $aid=$_POST['aid'];
        //$tid=$_POST['tid'];
        $qString = explode("&", $qString);
        $this->model->correct($qString,$aid/*,$tid*/);
    }
    //количество счётов пользователя. 0 - счетов нету.
    function countacc(){
        die(json_encode($this->model->countacc()));
    }
}