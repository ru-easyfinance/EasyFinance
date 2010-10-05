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
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->user  = Core::getInstance()->user;
        $this->tpl->assign('name_page', 'accounts/accounts');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
        $this->tpl->assign("template", "default");
    }

    /**
     * Добавляет новый счёт пользователя
     * @param $args
     * @return bool
     */
    function add()
    {
        if( _Core_Request::getCurrent()->method == 'POST' ) {
            $params = $_POST;
            $account = Account::load($params);
            $accs = $account->create($this->user, $params);
            if (!$accs){
               $this->tpl->assign('error', array('text'=>'Счёт не добавлен'));
            }
            $this->tpl->assign('result' , array('text'=>'Счёт успешно добавлен'
                ,'id'=>$accs
                ));
            $this->tpl->assign( 'name_page', 'info_panel/info_panel' );
        } else {
            $this->tpl->assign( 'name_page', 'account/edit' );
        }
    }

    function edit($args)
    {
        throw new Exception('Look at ticket #1527');
        if( _Core_Request::getCurrent()->method == 'POST' )
        {
            $params = $_POST;
            (isset($params['id']))?(1):($pda=1);
            (isset($params['id']))?(1):($params['id'] = $args[0]);
            $account = Account::load($params);
            if ( !$account->update($this->user, $params) ){
                $this->tpl->assign('error', array('text'=>'Счёт не удалён'));
            }
            $this->tpl->assign('result' , array('text'=>'Счёт успешно изменён'));
            $this->tpl->assign( 'name_page', 'info_panel/info_panel' );
        } else {
            $acm = new Account_Model();
            $acc = $acm->getAccountPdaInformation($args[0]);
            $this->tpl->assign( 'acc', $acc);
            $this->tpl->assign( 'name_page', 'account/edit' );
        }
    }

    /**
     * Удаляет указанный счет
     * @param $args array mixed
     * @return void
     */
    function delete ($args)
    {

        if( (isset($_REQUEST['confirmed']) && $_REQUEST['confirmed']) ) {
            $params = $_REQUEST;

            // Предупреждение перед удалением
            if (count($this->user->getUserAccounts()) == 1) {
                $this->renderJsonError('Перед удалением последнего счета создайте нужные Вам счета');
            }

            $account = Account::getTypeByID($params);
            $er = $account->delete($this->user, $params);
            if (!$er){
                $this->renderJsonError('Счёт не удалён');
            }
            if ($er === 'cel') {
                $this->renderJsonError('Невозможно удалить счёт, к которому привязана фин.цель');
            } else {
                //@XXX Тут как бы хз.. Нужно проверять какую конструкцию поставить.
                //Сильно смущает строка ниже, передающая в шаблонизатор переменную
                $this->tpl->assign('result',array('text'=>'Счёт удален'));
            }

            $this->tpl->assign( 'name_page', 'info_panel/info_panel' );
        } elseif( !isset($_POST['confirmed']) ) {
            $confirm= array (
                'title'     => 'Удаление счёта',
                'message'     => 'Вы действительно хотите удалить выбранный счёт?',
                'yesLink'    => '/accounts/delete/?id=' . $args[0] . '&confirmed=1',
                'noLink'     => $_SERVER['HTTP_REFERER'],
            );

            // Сохраняем в сессии адрес куда идти если согласится
            $_SESSION['redirect'] = $_SERVER['HTTP_REFERER'];

            $this->tpl->assign('confirm', $confirm);
            $this->tpl->assign('name_page', 'confirm');

        // Видимо передумали удалять и наша логика не сработала - редиректим на инфо
        } else {
            _Core_Router::redirect( '/info' );
        }
    }

    /**
     * Функция которая отсылает список счетов
     */
    public function accountslist()
    {
        $accountModel = new Account_Model;
        $accounts = $accountModel->loadAllWithStat($this->user->getId());

        //@TODO Привести к нормальному возврату данных, т.е. через присвоение переменной.
        //В деструкторе ещё используется, поэтому ой
        // $this->tpl->assign('accounts', array('result'=>array('data'=> $acc)));
        die (json_encode(array('result' => array('data' => $accounts))));
    }
}
