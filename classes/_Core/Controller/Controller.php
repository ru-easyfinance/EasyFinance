<?php
/**
 * Абстрактный контроллер. Должен наследоватся 
 * напрямую контроллерами не требующими авторизации
 *
 * @copyright easyfinance.ru
 * @author Andrew Tereshko aka mamonth
 * @package _Core
 */
abstract class _Core_Controller
{
	/**
	 * Ссылка на класс Смарти
	 * @var Smarty
	 * @todo Оторвать смарти, заменить на Native
	 */
	protected $tpl = null;
	
	/**
	 * Конструктор. Содержит инициализацию общих для 
	 * всех контроллеров свойств и обьектов.
	 *
	 */
	public function __construct()
	{
		// Шаблонизатор
		$this->tpl   = Core::getInstance()->tpl;
		
		$this->__init();
		
		//Ежели неавторизован пользователь ...
		if (!Core::getInstance()->user->getId())
		{
			//..показываем ему сео говнотексты
			$this->includeSeoText();
			
			//.. записываем реферера, если он конечно уже не был записан
			if( !isset($_SESSION['referrer_url']) )
			{
				$_SESSION['referrer_url'] = $_SERVER['HTTP_REFERER'];
			}
		}
	}
	
	/**
	 * Метод для инициализации контроллера.
	 * (во избежание переписывания конструктора)
	 *
	 */
	abstract protected function __init();
	
	/**
	 * Подключение сео говнотекстов.
	 *
	 */
	protected function includeSeoText()
	{
		if(file_exists('admin/seo.php'))
		{
			include ('admin/seo.php');
		}
		$this->tpl->assign('seotext', $texts);
	}
	
    /**
     * Если нам были переданы ошибочные данные, генерируем 404 страницу
     * @param $method
     * @param $args
     * @return void
     */
    public function __call($method, $args)
    {
        //@XXX Делаем хак для XDEBUG
        if (substr($method, 0, 7) != '?XDEBUG') {
           // error_404();
        }
    }
    
    /**
     * 
     */
    private function loadJS ()
    {
        $mdl = strtolower(Core::getInstance()->url[0]);
//        if (DEBUG) {
            $sfx='.js';
//        } else {
//            $sfx='.min.js';
//        }
        $js = Core::getInstance()->js[$mdl];
        if(!is_array($js)){$js = array();}
        foreach ($js as $v) {
            Core::getInstance()->tpl->append('js', $v.$sfx);
        }
    }

	/**
	 * При завершении работы, контроллера
	 */
	function __destruct()
	{
		if( !session_id() )
		{
			session_start();
		}
		
		// Применение модификаций\удалений моделей (после внедрения TemplateEngine_Json - удалить)
		_Core_ObjectWatcher::getInstance()->performOperations();
		
		// Подгрузка js файлов
		$this->loadJS();
		
		$user = Core::getInstance()->user;
		
		$res = array(
			'errors' => Core::getInstance()->errors //@TODO Удалить потом
		);
		
		if( isset($_SESSION['resultMessage']) )
		{
			if( isset($_SESSION['messageSend']) )
			{
				$res['result'] = array( 'text' => $_SESSION['resultMessage'] );
				unset( $_SESSION['resultMessage'], $_SESSION['messageSend']);
			}
			else
			{
				$_SESSION['messageSend'] = true;
			}
		}
		
		if( isset($_SESSION['errorMessage']) )
		{
			if( isset($_SESSION['errorMessage']) )
			{
				$res['result'] = array( 'text' => $_SESSION['errorMessage'] );
				unset( $_SESSION['errorMessage'], $_SESSION['messageSend']);
			}
			else
			{
				$_SESSION['messageSend'] = true;
			}
		}
		
		if ( is_null($user->getId()) )
		{
			Core::getInstance()->tpl->assign('res', json_encode($res));
			return false;
		}

        Core::getInstance()->tpl->assign('account', Core::getInstance()->user->getUserAccounts());
        // Подготавливаем счета
        $accounts = array();
        try {
                /*$acc = new Accounts_Model;
                $accou = $acc->accounts_list();*/

                $acc = new Account_Collection();
                $accou = $acc->load($user->getId());

                $account = $accou['result']['data'];
                //die(print_r($account));
            
        } catch ( Exception $e) {
            $accou = 0;
        }
        foreach ($account as $k=>$v){
                $accounts[$k] = $v;
        }

        /*$accounts = array();
        
        //$account = $accou['result'];
        foreach ($accou as $k=>$v){
            foreach ($v as $k1=>$v1){
                $accounts[$k][$k1] = $v1;
            }
        }*/
        
        /*
        foreach ($user->getUserAccounts() as $v) {
            $accounts[$v['account_id']] =array(
                'id'            => $v['account_id'],
                'type'          => $v['account_type_id'],
                'cur'           => $v['account_currency_name'],
                'name'          => stripslashes($v['account_name']),
                'descr'         => stripslashes($v['account_description']),
                'def_cur'       => Core::getInstance()->currency[$v['account_currency_id']]['value'] * $v['total_sum'],
                'cur_id'        => $v['account_currency_id'],
                'total_balance' => $v['total_sum']
            );
        }*/
        
        // Подготавливаем фин.цели
        $targets = array();
        foreach ($user->getUserTargets() as $key => $var) {
            if ($key == 'user_targets') {
                foreach ($var as $v) {
                    $targets['user_targets'][$v['id']] = array(
                        'title'        => $v['title'],
                        'date_end'     => $v['end'],
                        'amount_done'  => $v['amount_done'],
                        'percent_done' => $v['percent_done'],
                        'money'        => $v['amount'],
                        'account'      => $v['account'],
                        'amount_done'  => $v['amount_done'],
                        'category'     => $v['category'],
                        'close'        => $v['close'],
                        'done'         => $v['done']
                    );
                }
            } elseif ($key == 'pop_targets') {
                foreach ($var as $v) {
                    $targets['pop_targets'][] = array(
                        'title'        => $v['title']
                    );
                }
            }
        }
        //валюты
        $currency = array();
        try {
            $get = $user->getCur();
        } catch ( Exception $e) {
            $get = 0;
        }
        $cur_user_string = $get['li'];
        $cur_user_array = unserialize($cur_user_string);
        $curdef = $get['def'];
        $curfrom = $curdef;//валюта которую подтягиваем из базы
        //die ( print_r ($curdef) );
        //die ( $curfrom );
        if ($curdef != 4)
            if ($curdef != 6)
                if ($curdef !=9)
                    $curfrom = 1;
        /*$currency[1] = array(
            'cost'=>1,
            'name'=>'RUB',
            'text'=>'руб.'
            );*/
        try {
            $curr = $user->getCurrencyByDefault($cur_user_array, $curfrom);
        } catch ( Exception $e) {
            $curr = 0;
        }
        $delimeter = 1; //делитель в курсе
        if ($curdef != 4)
            if ($curdef != 6)
                if ($curdef !=9){
                    foreach ($curr as $k => $v){
                        if ( $v['id'] == $curdef )
                            $delimeter = $v['value'];
                    }
                }
        foreach ($curr as $k => $v) {

            $currency[$v['id']] = array(
                'cost' => round( ( $v['value'] / $delimeter ) , 4) ,
                'name' => $v['charCode'],
                'text' => $v['abbr'],
                'progress' => ''
            );
        }//*/
        $currency['defa'] = (int)$get['def'];//валюта по умолчанию


        try {
            $info = new Info_Model();
            $infoa = $info->get_data();
        } catch ( Exception $e) {
            $infoa = 0;
        }
        try {
            $category = new Category_Model();
            $cats = $category->getCategory();
            $cats['recent'] = get_recent_category(10, 0);
        } catch ( Exception $e ) {
            $cats = null;
        }
	
	$res += array(
            'tags' => $user->getUserTags(),
            'cloud' => Core::getInstance()->user->getUserTags(true),
            'accounts' => $accounts,
            'events' => Core::getInstance()->user->getUserEvents(),
            'user_targets' => $targets['user_targets'],
            'popup_targets' => $targets['pop_targets'],
            'currency' => $currency,
            'flash' => array(
                'title' => '',
                'value' => $infoa[0][0],
            ),
            'targets_category'=>array(
                '1' => 'Квартира',
                '2' => 'Автомобиль',
                '3' => 'Отпуск',
                '4' => 'Финансовая подушка',
                '6' => 'Свадьба',
                '7' => 'Бытовая техника',
                '8' => 'Компьютер',
                '5' => 'Прочее'
            ),
            'errors'=>Core::getInstance()->errors,
            'budget'=>Core::getInstance()->user->getUserBudget(),
            'category' => $cats,
            'informers' => $infoa
            );
        
        Core::getInstance()->tpl->assign('res', json_encode($res));
    }
}

