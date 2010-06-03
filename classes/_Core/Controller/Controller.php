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
	 * Ссылка на текущий запрос
	 *
	 * @var _Core_Request
	 */
	protected $request = null;

	/**
	 * Конструктор. Содержит инициализацию общих для
	 * всех контроллеров свойств и обьектов.
	 *
	 */
	public function __construct( $template, _Core_Request $request )
	{
		// Шаблонизатор
		$this->tpl   = $template;

		// ОБьект запроса
		$this->request = $request;

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
     * Выводит в браузер JSON
     * @param string $text
     */
    public function renderJsonSuccess($text)
    {
        die(
            json_encode(
                array(
                    'result'=>array(
                        'text' => $text
                        )
                    )
                )
            );
    }

    /**
     * Выводит в браузер Json с ошибкой
     * @param string $error
     */
    public function renderJsonError($error)
    {
        die(
            json_encode(
                array(
                    'error'=>array(
                        'text' => $error
                        )
                    )
                )
            );
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

        // Подготавливаем счета
        $accounts = array();
        try
        {
                $accountCollection = new Account_Collection();
                $accou = $accountCollection->load($user->getId());

                $account = $accou['result']['data'];

        }
        catch ( Exception $e)
        {
            $accou = 0;
        }


        foreach ($account as $k=>$v)
       	{
                $accounts[$k] = $v;
                if (isset($v['binding'])) {
                    $binding = $v['id'];
                }
        }

        //Подготавливаем Часто используемые счета
        $oftenAccounts = array();
        $oftenAccount = array();
        foreach ($accounts as $k=>$v){
            $op = new Operation_Model();
            $oftenAccounts[$k] = $op->getNumOfOperetionOnAccount($k);
        }
        arsort($oftenAccounts);
        $num = 0;
        foreach ($oftenAccounts as $k=>$v){
            if ( $num < 3){
                $oftenAccount[$k] = $oftenAccounts[$k];
                $num++;
            }
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


        try {
            $info = new Info_Model();
            $infoa = $info->get_data();
        } catch ( Exception $e) {
            $infoa = array();
        }

        try {
            $category = new Category_Model();
            $cats = $category->getCategory();
            $cats['recent'] = get_recent_category(10, 0);
        } catch ( Exception $e ) {
            $cats = null;
        }


        /**
         * Валюты
         */
        $ex = sfConfig::get('ex');
        $userDefaultCurrency = $user->getUserProps('user_currency_default');
        $userCurrencies = array();

        // Все валюты пользователя по курсу к базовой
        foreach ($user->getUserCurrency() as $currencyItem) {
            $userCurrencies[$currencyItem['id']] = array(
                'cost' => number_format($ex->getRate($currencyItem['id'], $userDefaultCurrency), 4, '.', ''),
                'name' => $currencyItem['charCode'],
                'text' => $currencyItem['abbr'],
            );
        }
        $userCurrencies['default'] = (int)$userDefaultCurrency; //валюта по умолчанию


        /**
         * Res
         */
        $res = array_merge($res, array(
            'getNotify' => @$_SESSION['user']['getNotify'], //@FIXME
            'profile'   => array(
                'integration'=>array(
                    'email' => Core::getInstance()->user->getUserProps('user_service_mail'),
                    'account' => isset($binding)?$binding:'',
                )
            ),
            'tags' => $user->getUserTags(),
            'cloud' => Core::getInstance()->user->getUserTags(true),
            'calendar' => array(
                'overdue'   => Core::getInstance()->user->getUserEvents('overdue'),
                'calendar'  => Core::getInstance()->user->getUserEvents('calendar'),
                'future'    => Core::getInstance()->user->getUserEvents('reminder'),
            ),
            'accounts' => $accounts,
            'accountsRecent' => $oftenAccount,
            //'events' => Core::getInstance()->user->getUserEvents(),
            //'targets' => $targ,
            'user_targets' => $targ['user_targets'],
            'popup_targets' => $targ['pop_targets'],
            'currency' => $userCurrencies,
            'flash' => array(
                'title' => '',
                'value' => isset($infoa[0][0])?$infoa[0][0]:0,
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
            ));

            if( Core::getInstance()->user->getId() > 0 )
            {
            	$res['user'] = array(
            		'name' => Core::getInstance()->user->getName(),
            	);
            }

        $this->tpl->assign('res', $res );
    }
}
