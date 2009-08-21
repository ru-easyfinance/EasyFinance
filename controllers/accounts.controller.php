<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля счетов пользователя
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */

class Accounts_Controller extends Template_Controller
{

    /**
     * Ссылка на класс User
     * @var <User>
     */
    private $user = null;

    /**
     * Ссылка на класс Smarty
     * @var <Smarty>
     */
    private $tpl = null;

    /**
     * Ссылка на класс модель
     * @var <Accounts_Model>
     */
    private $model = null;	

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
	$this->user  = Core::getInstance()->user;
        $this->tpl   = Core::getInstance()->tpl;
        $this->model = new Accounts_Model();
        $this->tpl->assign('name_page', 'accounts/accounts');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        if ($_SESSION['account'] == "reload") { //@FIXME Переписать эту конструкцию
            $this->user->initUserAccount($this->user->getId());
            $this->user->save();
            unset($_SESSION['account']);
        }
        $this->tpl->assign("page_title", "account all");
	$this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
	$this->tpl->assign('type_accounts', $this->model->getTypeAccounts());
	$this->tpl->assign("template", "default");
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
	$this->model->newEmptyBill($id);
	$this->tpl->assign("fields", $this->model->formatFields());
	$this->tpl->assign("type_id", $id);
         $c_arr=Core::getInstance()->user->getUserCurrency();
         $arr = array();
         $i=0;
         foreach ($c_arr as $key=>$val)
         {
             if (is_array($val))
             {
                $arr[$i]['name']=$val['abbr'];
                $arr[$i]['key']=$key;
                $i++;
             }
         }
         //die(print_r($arr));
        $this->tpl->assign("currency", $arr);

        die($this->tpl->fetch("accounts/accounts.fields.html"));
    }

    /**
     * Добавляет новый счёт пользователя
     * @param $args
     * @return bool
     */
    function add($args)
    {
        $this->tpl->assign("page_title","account add");
        $this->tpl->assign('currency', Core::getInstance()->user->getUserCurrency());
        $this->model->add($_POST);
	$this->accountslist();
        die ();
    }
	
	/**
     * Удаляет указанный счет
     * @param $args array mixed
     * @return void
     */
    function del ($args)
    {
        $id = $_POST['id'];
        if (!$this->model->deleteAccount($id)) {
            $this->tpl->assign("error", "Счет не удален");
        }
        die ();
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
    
    function correct()
    {
        $this->tpl->assign('currency', Core::getInstance()->user->getUserCurrency());
	$qString = urldecode($_POST['qString']);
        $aid=$_POST['aid'];
        $tid=$_POST['tid'];
	$qString = explode("&", $qString);
        $this->model->correct($qString,$aid,$tid);
        die ();
    }

}