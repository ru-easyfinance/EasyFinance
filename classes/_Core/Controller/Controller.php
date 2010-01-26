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
	public function __construct( $template )
	{
		// Шаблонизатор
		$this->tpl   = $template;
		
		// Вызов псевдоконструктора.
		$this->__init();
		
		//Ежели неавторизован пользователь ...
		if (!Core::getInstance()->user->getId())
		{
			//..показываем ему сео говнотексты
			$this->includeSeoText();
		}
		
		
		// Определяем информацию о пользователе
		if (Core::getInstance()->user->getId())
		{
			$uar = array(
				'user_id'   => Core::getInstance()->user->getId(),
				'user_name' => $_SESSION['user']['user_name'],
				'user_type' => $_SESSION['user']['user_type']
			);
			
			$this->tpl->assign('user_info', $uar);
		}
		
		$this->loadJS();
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
		$texts = array();
		
		if(file_exists( DIR_SHARED . 'seo.php'))
		{
			include ( DIR_SHARED . 'seo.php');
		}
		
		$this->tpl->assign('seotext', $texts);
	}
	
	/**
	 * Динамическое подключение js файлов
	 * в зависимости от модуля
	 *
	 */
	private function loadJS ()
	{
		$module = '';
		$jsArr = array();
		
		if( sizeof(Core::getInstance()->url) )
		{
			$module = strtolower( Core::getInstance()->url[0] );
		}
		
		if( array_key_exists( $module, Core::getInstance()->js ) )
		{
			$jsArr = Core::getInstance()->js[$module];
		}
		
		foreach ($jsArr as $jsFile)
		{
			$this->tpl->append('js', $jsFile.'.js');
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
			$this->tpl->assign('res', $res);
			return false;
		}

		$this->tpl->assign('account', Core::getInstance()->user->getUserAccounts());
		
        // Подготавливаем счета
        $accounts = array();
        try
        {
                $acc = new Account_Collection();
                $accou = $acc->load($user->getId());

                $account = $accou['result']['data'];
            
        } 
        catch ( Exception $e)
        {
            $accou = 0;
        }
        
        
        foreach ($account as $k=>$v)
       	{
                $accounts[$k] = $v;
        }
        
        // Подготавливаем фин.цели
        $targets = array();
        try 
        {
            $targ = $user->getUserTargets();
        }
        catch ( Exception $e)
        {
            $targ = 0;
        }
        
        //валюты
        $currency = array();
        
        try
        {
            $get = $user->getCur();
        }
        catch ( Exception $e)
        {
            $get = 0;
        }
        
        $cur_user_string = $get['li'];
        $cur_user_array = unserialize($cur_user_string);
        $curdef = $get['def'];
        
        //валюта которую подтягиваем из базы
        $curfrom = $curdef;
        
        // Если валюта не бел.р., гривна, тенге
        if ( !in_array( $curdef, array(4,6,9) ) )
        {
        		// 
		$curfrom = 1;
        }
        
        try
        {
            $curr = $user->getCurrencyByDefault($cur_user_array, $curfrom);
        }
        catch ( Exception $e)
        {
            $curr = 0;
        }
        
        //делитель в курсе
        $delimeter = 1;
        
        if ( !in_array( $curdef, array(4,6,9) ) )
        {
		foreach ($curr as $k => $v)
		{
			if ( $v['id'] == $curdef )
			{
				$delimeter = $v['value'];
			}
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
            //'targets' => $targ,
            'user_targets' => $targ['user_targets'],
            'popup_targets' => $targ['pop_targets'],
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
            
            if( Core::getInstance()->user->getId() > 0 )
            {
            	$res['user'] = array(
            		'name' => Core::getInstance()->user->getName(),
            	);
            }
        
        $this->tpl->assign('res', $res );
    }
}

