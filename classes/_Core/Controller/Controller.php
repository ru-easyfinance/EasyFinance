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
	protected function includeSeoText()
	{
		if(file_exists('admin/seo.php'))
		{
			include ('admin/seo.php');
		}
		$this->tpl->assign('seotext', $texts);
	}
        
	public function __construct()
	{
		// Шаблонизатор
		$this->tpl   = Core::getInstance()->tpl;

                $this->__init();
                if (!Core::getInstance()->user->getId())
                    {
                        $this->includeSeoText();
                    }
		
	}
	
	abstract protected function __init();

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
        }
        
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
        $currency = array();
        foreach ($user->getUserCurrency() as $k => $v) {
            $currency[$k] = array(
                'cost' => $v['value'],
                'name' => $v['charCode'],
                'text' => $v['abbr'],
                'progress' => ''
            );
        }

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

