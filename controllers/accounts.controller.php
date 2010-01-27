<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля счетов пользователя
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */

class Accounts_Controller extends _Core_Controller_UserCommon
{

    /**
     * Ссылка на класс User
     * @var User
     */
    private $user = null;

    /**
     * Ссылка на класс модель
     * @var Accounts_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->user  = Core::getInstance()->user;
        $this->model = new Accounts_Model();
        $this->tpl->assign('name_page', 'accounts/accounts');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
        if ( isset($_SESSION['account']) && $_SESSION['account'] == "reload")
        { //@FIXME Переписать эту конструкцию
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
        //$this->tpl->assign("page_title","account add");
        //$this->tpl->assign('currency', Core::getInstance()->user->getUserCurrency());

        //$this->accountslist();
        $user = Core::getInstance()->user->getId();
        $accountCollection = new Account_Collection();
        $params = $_POST;
        $account = Account::load($params);
        $accs = $account->create($user, $params);
        if (!$accs)
            $this->tpl->assign("error", "Счет не добавлен");
        die (json_encode(array('result'=>array('text'=>'Счёт успешно добавлен'
            ,'id'=>$accs
            ))));
    }

    function edit()
    {
        $user = Core::getInstance()->user->getId();
        $accountCollection = new Account_Collection();
        $params = $_POST;
        $account = Account::load($params);
        if (!$account->update($user, $params))
            $this->tpl->assign("error", "Счет не изменён");
        die (json_encode(array('result'=>array('text'=>'Счёт успешно изменён'))));
    }

	/**
     * Удаляет указанный счет
     * @param $args array mixed
     * @return void
     */
    function delete ($args)
    {
        $user = Core::getInstance()->user->getId();
        $accountCollection = new Account_Collection();
        $params = $_POST;

        $account = Account::getTypeByID($params);
        //$account = Account::load($params);
        $er = $account->delete($user, $params);
        if (!$er)
            $this->tpl->assign("error", "Счет не удалён");
        if ($er == 'cel')
            die (json_encode(array('error'=>array('text'=>'Невозможно удалить счёт, к которому привязана фин.цель'))));
        die (json_encode(array('result'=>array('text'=>'Счёт удален'))));
    }

    /**
     * Функция которая отсылает список счетов
     */
    public function accountslist()
    {
        $user = Core::getInstance()->user->getId();
        $accountCollection = new Account_Collection();
        //$accountCollection->load( Core::getInstance()->user );

        /*$params = array (
            'id'         => '12',
            'name'       => 'Метааллл',
            'comment'    => 'не в деревянных же держать',
            'yearPercent'=> '13',
            'bank'       => 'Raffaisen',
            'currency'   => '1',
            'type'       => '10',
            'typeMetal'  => 'Золото',
            'dateGet'    => '17.12.2009',
            'dateOff'    => '21.12.2009',
            'dateOpen'   => '16.12.2009',
            'typePayment'=> '2',
            'support'    => 'юзайте сапорт'
        );*/


        //$params = $_POST;
        //$account = Account::load($params);
        //$account->delete($user, $params);
        //$account->create($user, $params);
        //$account->update($user, $params);
        //$accountCollection->add($account);//*/
        $acc = $accountCollection->load($user);
        //$acc_id = $params['id'];
        //$accountCollection->loadOneAcc($acc_id);

        die ( json_encode (  ( $acc ) ) );
    }

   /*function edit()
    {
        //die ('123');
        $id = $_POST['id'];
        $this->model->deleteAccount($id);
        $this->model->add($_POST);
        die ($id);
    }*/

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