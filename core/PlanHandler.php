<?php
/**
 * Обработчик бизнес-задачи планирования бюджета
 *
 * @author   Роман Коростов, Москва, Россия, 2008
 * @package  home-money
 * @version  1.0
 */
 
//require_once(SYS_DIR_LIBS.'/Plan/plan.php');

/**
 * Класс контролера
 * 
 * @package  home-money
 * @access   protected
 *
 */ 
class PlanHandler {
	
  /**
   * Контейнер счетов
   *
   * @var      object Account
   * @access   private
   */
  private $_account;
  
  /**
   * Контейнер транзакций
   *
   * @var      object Money
   * @access   private
   */
  private $_money;
  
  /**
   * Контейнер категорий
   *
   * @var      object Category
   * @access   private
   */
  private $_category;
  
  private $_sys_currency;
  
  /**
   * Контейнер категорий использующиеся в плане
   *
   * @var      array
   * @access   public
   */
  public $plan_category;
  
  /**
   * Общая сумма по доходам
   *
   * @var      integer
   * @access   public
   */
  public $total_income=0;
  
  /**
   * Общая сумма по расходам
   *
   * @var      integer
   * @access   public
   */
  public $total_outcome=0;
  
   /**
   * Производит инициализацию объектов
   *
   * @param array $conf Хэш с конфигурационными параметрами
   * Ключи:
   * db : object DB Объект управления базой данных
   *
   * @return object ExportHandler
   * @throws Exception
   * @access public
   */
   public function __construct($conf=array()) {
  	if (!isset($conf['account'])) throw new Exception('Не указан объект Account');
  	if (!isset($conf['money'])) throw new Exception('Не указан объект Money');
  	if (!isset($conf['category'])) throw new Exception('Не указан объект Category');
  	if (!is_a($conf['account'],'Account')) throw new Exception('Объект Account неверного типа');
  	if (!is_a($conf['money'],'Money')) throw new Exception('Объект Money неверного типа');
  	if (!is_a($conf['category'],'Category')) throw new Exception('Объект Category неверного типа');
  	
  	$this->_account = $conf['account'];
  	$this->_money = $conf['money'];
  	$this->_category = $conf['category'];
	$this->_sys_currency = $conf['sys_currency'];
  } // __construct
  
  /**
   * Возвращает список категорий для детализации доходов
   * @param array $data Список категорий из сессии
   * @param string $prefix префикс для детализации in-доходы, out-расходы
   *
   * @return string готовый список с чекбоксами
   * @access public
   */
  public function getDetalizeCategoriesForPlan($data, $prefix, $start_date, $finish_date, $cats) {
  
  	if ($prefix == 'in')
	{
		$drain = 0;
	}else{
		$drain = 1;
	}

  	$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
	$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
  	$sum_period = $dbs->select("select sum(m.money) as sum, m.cat_id, m.bill_id, b.bill_currency from money m 
									left join bill b on b.bill_id = m.bill_id and b.user_id = '".$_SESSION['user']['user_id']."'
									where m.user_id='".$_SESSION['user']['user_id']."' and m.drain=$drain and m.date >= '$start_date' and m.date < '$finish_date'
									group by cat_id, bill_id");
		
  	return $this->_category->getDetalizeCategoriesForPlan($data, $prefix, $sum_period, $this->_sys_currency, $cats);
  } // getDetalizeCategoriesForPlan
  
  /**
   * Сохраняет план бюджета
   * @param array $plan Список настроек плана
   * @param array $category_income Список категорий для учета дохода
   * @param array $category_outcome Список категорий для учета расходов
   * @param array $user_accounts Список счетов для учета в плане
   * @param object $dbs Объект управления базой данных
   * @param array $sys_currency Список валют с котировками
   *
   * @return boolen или да или нет
   * @access public
   */
  public function savePlan($plan, $user_accounts, $dbs = null, $sys_currency)
  {
	if (!$dbs) 
	{
		$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
		$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
	}
	
	unset($plan['planning_month']);
	unset($plan['planning_year']);
	
		/*pre($plan);
		pre($this->plan_category['income']);
		pre($this->plan_category['outcome']);
		pre($user_accounts,true);*/
	
	
	
	// делаем сборку для объекта контроля
	/*$conf['categories'] = $this->plan_category['income'];
	$conf['plan'] = $plan;
	
	$income = new PlanCategory($conf, $dbs);*/
	
	if (!$dbs->query('INSERT INTO plan_settings(?#) VALUES(?a)', array_keys($plan), array_values($plan)))
	{
		return false;
	}
	
	$cnt_account = count($_SESSION['user_account']);
	$cnt_income = count($this->plan_category['income']);
	$cnt_outcome = count($this->plan_category['outcome']);
	$plan_id = mysql_insert_id();
	
	// собираем сумму по всем счетам для каждой категории доходов в заданный период
	if (!empty($this->plan_category['income']['income']))
	{
		foreach ($this->plan_category['income']['income'] as $category_id=>$v)
		{
			$total_sum = 0;
			
			foreach ($user_accounts as $account_id=>$v2)
			{
				$rows = $dbs->select("SELECT sum(money) as total_sum FROM money 
												WHERE user_id = '".$plan['user_id']."' and 
													  cat_id = '".$category_id."' and 
													  bill_id = '".$account_id."' and 
													  drain = 0 and
													  date > '".$plan['date_start_plan']."' 
													  and date < '".$plan['date_finish_plan']."'");
				for ($i=0; $i<$cnt_account; $i++)
				{
					if ($_SESSION['user_account'][$i]['id'] == $account_id)
					{
						// если надо, конвертируем валюту в рубли
						$rows[0]['total_sum'] = $rows[0]['total_sum'] * $this->_sys_currency[$_SESSION['user_account'][$i]['currency']];
					}
				}
				
				$total_sum = $total_sum + $rows[0]['total_sum'];
			}
			
			$row = array(
				'plan_id' => $plan_id, 
				'category_id' => $category_id, 
				'drain' => 0,
				'total_sum' => $total_sum,
				'total_sum_plan' => $v 
				);
				
			$dbs->query('INSERT INTO plan_fact(?#) VALUES(?a)', array_keys($row), array_values($row));
	
		}
	}
	
	if (!empty($this->plan_category['outcome']['outcome']))
	{
		// собираем сумму по всем счетам для каждой категории расходов в заданный период
		foreach ($this->plan_category['outcome']['outcome'] as $category_id=>$v)
		{
			$total_sum = 0;
	
			foreach ($user_accounts as $account_id=>$v2)
			{
				$rows = $dbs->select("SELECT sum(money) as total_sum FROM money 
												WHERE user_id = '".$plan['user_id']."' and 
													  cat_id = '".$category_id."' and 
													  bill_id = '".$account_id."' and 
													  drain = 1 and
													  date > '".$plan['date_start_plan']."' 
													  and date < '".$plan['date_finish_plan']."'");
	
				for ($i=0; $i<$cnt_account; $i++)
				{
					if ($_SESSION['user_account'][$i]['id'] == $account_id)
					{
						// если надо, конвертируем валюту в рубли
						$currency_current = $_SESSION['user_account'][$i]['currency'];					
						$rows[0]['total_sum'] = $rows[0]['total_sum'] * $this->_sys_currency[$currency_current];
					}
				}
				
				$total_sum = $total_sum + $rows[0]['total_sum'];
			}		
	
			$row = array(
				'plan_id' => $plan_id, 
				'category_id' => $category_id, 
				'drain' => 1,
				'total_sum' => $total_sum,
				'total_sum_plan' => $v
				);
			
			$dbs->query('INSERT INTO plan_fact(?#) VALUES(?a)', array_keys($row), array_values($row));		
		}
	}
	
	// записываем все счета
	foreach ($user_accounts as $account_id=>$v2)
	{
		$row = array(
				'user_id' => $_SESSION['user']['user_id'], 
				'account_id' => $account_id,
				'plan_id' => $plan_id
				);
		$dbs->query('INSERT INTO plan_accounts(?#) VALUES(?a)', array_keys($row), array_values($row));
	}
	
	return true;
	
  } // savePlan
  
  
  /**
   * Получает план бюджета
   * @param string $user_id ID-пользователя
   * @param object $dbs Объект управления базой данных
   *
   * @return array список категорий, общая сумма и настройки плана
   * @access public
   */
  public function getUserPlan($user_id, $dbs = null, $history_date = false)
  {
	if (!$dbs) 
	{
		$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
		$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
	}
	
	if (!empty($history_date))
	{		
		$plan['plan_settings'] = $dbs->selectRow("select * from plan_settings where user_id=? and date_start_plan>=? and date_finish_plan <=?", 
										  $user_id, $history_date['last_period'], $history_date['next_period']);
	}else{	
		$date_today = date("Y-m-d");
		$plan['plan_settings'] = $dbs->selectRow("select * from plan_settings where user_id=? and date_start_plan<=? and date_finish_plan >?", 
										  $user_id, $date_today, $date_today);
	}
	
	if (!count($plan['plan_settings']))
	{
		return false;	
	}
	
	// определяем дату начала и конца периода для среднего за 3 периода
	list($year,$month,$day) = explode("-", $plan['plan_settings']['date_start_plan']);
	list($fyear,$fmonth,$fday) = explode("-", $plan['plan_settings']['date_finish_plan']);
	$plan['plan_settings']['start_plan'] = $day.".".$month.".".$year;
	$plan['plan_settings']['finish_plan'] = $fday.".".$fmonth.".".$fyear;
	switch($plan['plan_settings']['planning_horizon'])
	{
		case 1:
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day-21, $year));
			$date_finish = $plan['plan_settings']['date_start_plan'];
		break;
		
		case 2:
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month-3, $day, $year));
			$date_finish = $plan['plan_settings']['date_start_plan'];
		break;
		
		case 3:
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month-9, $day, $year));
			$date_finish = $plan['plan_settings']['date_start_plan'];
		break;
		
		case 4:
			$date_start = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year-3));
			$date_finish = $plan['plan_settings']['date_start_plan'];
		break;
	}	
	
	$plan['plan_total_sum_income']['total_sum'] = 0;
	$categories_income = $dbs->select("select pf.plan_id, pf.category_id, pf.drain, pf.total_sum, pf.total_sum_plan,c.cat_name, c.cat_parent from plan_fact pf
											left join category c on pf.category_id = c.cat_id
											where pf.plan_id=? and drain=0 order by c.cat_parent, c.cat_name", 
										  $plan['plan_settings']['plan_id']);
	
	
	
	// получаем суммы факта по всем категориям за период плана
	$categories_income_fact = $dbs->select("select sum(m.money) as sum, m.cat_id, c.cat_name, c.cat_parent from money m 
												left join category c on c.cat_id = m.cat_id and c.user_id = '".$user_id."'
													where m.cat_id>0 and m.user_id = '".$user_id."' 
													   and m.date>='".$plan['plan_settings']['date_start_plan']."'
													   and m.date<='".$plan['plan_settings']['date_finish_plan']."' and m.drain=0 group by m.cat_id");
	
	// получаем суммы факта по всем категориям за 3 периодa
	$categories_income_fact_3_period = $dbs->select("select sum(money) as sum, cat_id from money where cat_id>0 and user_id = '".$user_id."' 
													   and date>='".$date_start."'
													   and date<'".$date_finish."' and drain=0 group by cat_id");
	
	// перезаписываем в массив категорий правильные значения факта
	$categories_income = $this->checkFactCategories($categories_income_fact, $categories_income);
	$cnt_categories_income = count($categories_income);
	for ($i=0; $i<$cnt_categories_income; $i++)
	{
		$categories_income[$i]['total_sum'] = 0;
		$categories_income[$i]['total_sum_otkl_percent'] = 0;
		for ($cof=0; $cof<count($categories_income_fact); $cof++)
		{
			if ($categories_income[$i]['category_id'] == $categories_income_fact[$cof]['cat_id'])
			{
				$categories_income[$i]['total_sum'] = $categories_income_fact[$cof]['sum'];
				
				if ($categories_income[$i]['total_sum_plan'] == 0)
				{
					$categories_income[$i]['total_sum_otkl_percent'] = 100;
				}else{
					$otkl = $categories_income_fact[$cof]['sum'] - $categories_income[$i]['total_sum_plan'];				
					$categories_income[$i]['total_sum_otkl_percent'] = ($otkl / $categories_income[$i]['total_sum_plan']) * 100;
				}
			}
		}
		$categories_income[$i]['total_sum_3_period'] = 0;
		for ($cof=0; $cof<count($categories_income_fact_3_period); $cof++)
		{
			if ($categories_income[$i]['category_id'] == $categories_income_fact_3_period[$cof]['cat_id'])
			{
				$categories_income[$i]['total_sum_3_period'] = $categories_income_fact_3_period[$cof]['sum'] / 3;
			}
		}
	}

	// составляем подсчет общей суммы каждой категории доходов, включая подкатегории
	$plan['plan_total_sum_income']['total_sum_3_period'] = 0;
	for ($i=0; $i<$cnt_categories_income; $i++)
	{
		$plan['plan_total_sum_income']['total_sum'] = $plan['plan_total_sum_income']['total_sum'] + $categories_income[$i]['total_sum'];
		if ($categories_income[$i]['cat_parent'] == 0)	
		{
			for ($j=0; $j<count($categories_income); $j++)
			{
				if ($categories_income[$j]['cat_parent'] == $categories_income[$i]['category_id'])	
				{
					$categories_income[$i]['total_sum_plan']=$categories_income[$i]['total_sum_plan']+$categories_income[$j]['total_sum_plan'];
					$categories_income[$i]['total_sum_3_period']=$categories_income[$i]['total_sum_3_period']+$categories_income[$j]['total_sum_3_period'];
					$plan['plan_total_sum_income']['total_sum_3_period'] += $categories_income[$i]['total_sum_3_period'];
					$categories_income[$i]['total_sum'] = $categories_income[$i]['total_sum'] + $categories_income[$j]['total_sum'];
				}
			}
		}
		
		$categories_income[$i]['total_sum_otkl'] = $categories_income[$i]['total_sum'] - $categories_income[$i]['total_sum_plan'];
		if ($categories_income[$i]['total_sum_plan'] == 0)
		{
			$categories_income[$i]['total_sum_otkl_percent'] = 100;
		}else{
			$categories_income[$i]['total_sum_otkl_percent'] = ($categories_income[$i]['total_sum_otkl'] / $categories_income[$i]['total_sum_plan']) * 100;
		}
		
		if ($categories_income[$i]['total_sum_otkl'] < 0)
		{			
			$categories_income[$i]['total_sum_otkl'] = number_format($categories_income[$i]['total_sum_otkl'], 2, '.', ' ');
			$categories_income[$i]['total_sum_otkl_percent'] = number_format($categories_income[$i]['total_sum_otkl_percent'], 1, '.', ' ');
			$categories_income[$i]['total_sum_otkl'] = "<span style='color:red;'>".$categories_income[$i]['total_sum_otkl']."</span>";
			$categories_income[$i]['total_sum_otkl_percent'] = "<span style='color:red;'>".$categories_income[$i]['total_sum_otkl_percent']."%</span>";
		}else{
			$categories_income[$i]['total_sum_otkl'] = number_format($categories_income[$i]['total_sum_otkl'], 2, '.', ' ');
			$categories_income[$i]['total_sum_otkl_percent'] = number_format($categories_income[$i]['total_sum_otkl_percent'], 1, '.', ' ');
			$categories_income[$i]['total_sum_otkl_percent'] .= "%";
		}		
		
		//$categories_income[$i]['total_sum_otkl_percent'] = number_format($categories_income[$i]['total_sum_otkl_percent'], 1, '.', ' ');
		//$categories_income[$i]['total_sum_otkl'] = number_format($categories_income[$i]['total_sum_otkl'], 2, '.', ' ');
		$categories_income[$i]['total_sum_plan'] = number_format($categories_income[$i]['total_sum_plan'], 2, '.', ' ');
		$categories_income[$i]['total_sum'] = number_format($categories_income[$i]['total_sum'], 2, '.', ' ');
		$categories_income[$i]['total_sum_3_period'] = number_format($categories_income[$i]['total_sum_3_period'], 2, '.', ' ');
	}
	
	$plan['plan_total_sum_outcome']['total_sum'] = 0;
	$categories_outcome = $dbs->select("select pf.plan_id, pf.category_id, pf.drain, pf.total_sum, pf.total_sum_plan, c.cat_name, c.cat_parent from plan_fact pf
											left join category c on pf.category_id = c.cat_id
											where pf.plan_id=? and drain=1 order by c.cat_parent, c.cat_name", 
										  $plan['plan_settings']['plan_id']);
	
	// получаем суммы факта по всем категориям за период плана
	$categories_outcome_fact = $dbs->select("select sum(m.money) as sum, m.cat_id, c.cat_name, c.cat_parent from money m 
												left join category c on c.cat_id = m.cat_id and c.user_id = '".$user_id."'
													where m.cat_id>0 and m.user_id = '".$user_id."' 
													   and m.date>='".$plan['plan_settings']['date_start_plan']."'
													   and m.date<='".$plan['plan_settings']['date_finish_plan']."' and m.drain=1 group by m.cat_id");
	
	// получаем суммы факта по всем категориям за 3 периодa
	$categories_outcome_fact_3_period = $dbs->select("select sum(money) as sum, cat_id from money where cat_id>0 and user_id = '".$user_id."' 
													   and date>='".$date_start."'
													   and date<'".$date_finish."' and drain=1 group by cat_id");
	
	// перезаписываем в массив категорий правильные значения факта
	$categories_outcome = $this->checkFactCategories($categories_outcome_fact, $categories_outcome);
	$cnt_categories_outcome = count($categories_outcome);
	for ($i=0; $i<$cnt_categories_outcome; $i++)
	{
		$categories_outcome[$i]['total_sum'] = 0;
		$categories_outcome[$i]['total_sum_otkl_percent'] = 0;
		for ($cof=0; $cof<count($categories_outcome_fact); $cof++)
		{
			if ($categories_outcome[$i]['category_id'] == $categories_outcome_fact[$cof]['cat_id'])
			{				
				$categories_outcome[$i]['total_sum'] = $categories_outcome_fact[$cof]['sum'] * -1;
				
				if ($categories_outcome[$i]['total_sum_plan'] == 0)
				{
					$categories_outcome[$i]['total_sum_otkl_percent'] = 100;
				}else{
					$otkl = $categories_outcome_fact[$cof]['sum'] + $categories_outcome[$i]['total_sum_plan'];				
					$categories_outcome[$i]['total_sum_otkl_percent'] = ($otkl / $categories_outcome[$i]['total_sum_plan']) * 100;
				}
			}
		}
		$categories_outcome[$i]['total_sum_3_period'] = 0;
		for ($cof=0; $cof<count($categories_outcome_fact_3_period); $cof++)
		{
			if ($categories_outcome[$i]['category_id'] == $categories_outcome_fact_3_period[$cof]['cat_id'])
			{
				$categories_outcome[$i]['total_sum_3_period'] = ($categories_outcome_fact_3_period[$cof]['sum']*-1) / 3;
			}
		}
	}
	
	// составляем подсчет общей суммы каждой категории расходов, включая подкатегории
	$plan['plan_total_sum_outcome']['total_sum_3_period'] = 0;
	for ($i=0; $i<count($categories_outcome); $i++)
	{		
		$plan['plan_total_sum_outcome']['total_sum'] = $plan['plan_total_sum_outcome']['total_sum'] + $categories_outcome[$i]['total_sum'];
		
		if ($categories_outcome[$i]['cat_parent'] == 0)	
		{
			for ($j=0; $j<count($categories_outcome); $j++)
			{
				if ($categories_outcome[$j]['cat_parent'] == $categories_outcome[$i]['category_id'])	
				{
					$categories_outcome[$i]['total_sum_plan']=$categories_outcome[$i]['total_sum_plan']+$categories_outcome[$j]['total_sum_plan'];
					$categories_outcome[$i]['total_sum_3_period']=$categories_outcome[$i]['total_sum_3_period']+$categories_outcome[$j]['total_sum_3_period'];
					$plan['plan_total_sum_outcome']['total_sum_3_period'] += $categories_outcome[$i]['total_sum_3_period'];
					$categories_outcome[$i]['total_sum'] = $categories_outcome[$i]['total_sum'] + $categories_outcome[$j]['total_sum'];					
				}
			}
		}
		$categories_outcome[$i]['total_sum_otkl'] = $categories_outcome[$i]['total_sum_plan'] - $categories_outcome[$i]['total_sum'];
		if ($categories_outcome[$i]['total_sum_plan'] == 0)
		{
			$categories_outcome[$i]['total_sum_otkl_percent'] = '---';
		}else{
			$categories_outcome[$i]['total_sum_otkl_percent'] = ($categories_outcome[$i]['total_sum_otkl'] / $categories_outcome[$i]['total_sum_plan']) * 100;
		}
		
		// В расходах, если факт больше плана, красим тотал красным
		
		if ($categories_outcome[$i]['total_sum_otkl'] < 0)
		{			
			$categories_outcome[$i]['total_sum_otkl'] = number_format($categories_outcome[$i]['total_sum_otkl'], 2, '.', ' ');
			$categories_outcome[$i]['total_sum_otkl_percent'] = number_format($categories_outcome[$i]['total_sum_otkl_percent'], 1, '.', ' ');
			$categories_outcome[$i]['total_sum_otkl'] = "<span style='color:red;'>".$categories_outcome[$i]['total_sum_otkl']."</span>";
			$categories_outcome[$i]['total_sum_otkl_percent'] = "<span style='color:red;'>".$categories_outcome[$i]['total_sum_otkl_percent']."%</span>";
		}else{
			$categories_outcome[$i]['total_sum_otkl'] = number_format($categories_outcome[$i]['total_sum_otkl'], 2, '.', ' ');
			$categories_outcome[$i]['total_sum_otkl_percent'] = number_format($categories_outcome[$i]['total_sum_otkl_percent'], 1, '.', ' ');
			$categories_outcome[$i]['total_sum_otkl_percent'] .= "%";
		}
		
		//$categories_outcome[$i]['total_sum_otkl_percent'] = number_format($categories_outcome[$i]['total_sum_otkl_percent'], 1, '.', ' ');		
		//$categories_outcome[$i]['total_sum_otkl'] = number_format($categories_outcome[$i]['total_sum_otkl'], 2, '.', ' ');
		$categories_outcome[$i]['total_sum_plan'] = number_format($categories_outcome[$i]['total_sum_plan'], 2, '.', ' ');
		$categories_outcome[$i]['total_sum'] = number_format($categories_outcome[$i]['total_sum'], 2, '.', ' ');
		$categories_outcome[$i]['total_sum_3_period'] = number_format($categories_outcome[$i]['total_sum_3_period'], 2, '.', ' ');
	}
	
	
	//Доходы
	//1.Отклонение = Факт - План
	//2.% откл = Если( план=0 тогда 100% иначе =Отклонение/План*100%)
	
	$plan['plan_total_sum_income']['total_sum_otkl'] = $plan['plan_total_sum_income']['total_sum'] - $plan['plan_settings']['total_income'];
	
	if ($plan['plan_settings']['total_income'] == 0)
	{
		$plan['plan_total_sum_income']['total_sum_otkl_percent'] = 100;
		$plan['plan_total_sum_income']['total_sum_otkl_percent'] = number_format($plan['plan_total_sum_income']['total_sum_otkl_percent'], 1, '.', ' ');
		$plan['plan_total_sum_income']['total_sum_otkl_percent'] .= "%";
	}else{
		$plan['plan_total_sum_income']['total_sum_otkl_percent'] = ($plan['plan_total_sum_income']['total_sum_otkl'] / $plan['plan_settings']['total_income']) * 100;
		$plan['plan_total_sum_income']['total_sum_otkl_percent'] = number_format($plan['plan_total_sum_income']['total_sum_otkl_percent'], 1, '.', ' ');
		$plan['plan_total_sum_income']['total_sum_otkl_percent'] .= "%";
	}
	
	
	//Расходы
	//1.Отклонение = План -Факт
	//2.% откл = Если( план=0; тогда -100%  иначе =Отклонение/План*100%)
	
	$plan['plan_total_sum_outcome']['total_sum_otkl'] = $plan['plan_settings']['total_outcome'] - $plan['plan_total_sum_outcome']['total_sum'];
	
	if ($plan['plan_settings']['total_outcome'] == 0)
	{
		$plan['plan_total_sum_outcome']['total_sum_otkl_percent'] = '---';
		$plan['plan_total_sum_outcome']['total_sum_otkl_percent'] = number_format($plan['plan_total_sum_outcome']['total_sum_otkl_percent'], 1, '.', ' ');
		$plan['plan_total_sum_outcome']['total_sum_otkl_percent'] .= "%";
	}else{
		$plan['plan_total_sum_outcome']['total_sum_otkl_percent'] = ($plan['plan_total_sum_outcome']['total_sum_otkl'] / $plan['plan_settings']['total_outcome']) * 100;
		$plan['plan_total_sum_outcome']['total_sum_otkl_percent'] = number_format($plan['plan_total_sum_outcome']['total_sum_otkl_percent'], 1, '.', ' ');
		$plan['plan_total_sum_outcome']['total_sum_otkl_percent'] .= "%";
	}
	
	
	$plan['plan_total_sum_income']['total_sum_3_period'] = number_format(($plan['plan_total_sum_income']['total_sum_3_period']/3), 2, '.', ' ');
	$plan['plan_total_sum_outcome']['total_sum_3_period'] = number_format(($plan['plan_total_sum_outcome']['total_sum_3_period']/3), 2, '.', ' ');
	
	if ($plan['plan_total_sum_income']['total_sum_otkl'] < 0)
	{
		$plan['plan_total_sum_income']['total_sum_otkl'] = number_format($plan['plan_total_sum_income']['total_sum_otkl'], 2, '.', ' ');
		$plan['plan_total_sum_income']['total_sum_otkl'] = "<span style='color:red;'>".$plan['plan_total_sum_income']['total_sum_otkl']."</span>";
		$plan['plan_total_sum_income']['total_sum_otkl_percent'] = "<span style='color:red;'>".$plan['plan_total_sum_income']['total_sum_otkl_percent']."</span>";
	}else{	
		$plan['plan_total_sum_income']['total_sum_otkl'] = number_format($plan['plan_total_sum_income']['total_sum_otkl'], 2, '.', ' ');
	}
	
	// В расходах, если факт больше плана, красим тотал красным
	if ($plan['plan_total_sum_outcome']['total_sum_otkl'] < 0)
	{
		$plan['plan_total_sum_outcome']['total_sum_otkl'] = number_format($plan['plan_total_sum_outcome']['total_sum_otkl'], 2, '.', ' ');
		$plan['plan_total_sum_outcome']['total_sum_otkl'] = "<span style='color:red;'>".$plan['plan_total_sum_outcome']['total_sum_otkl']."</span>";
		$plan['plan_total_sum_outcome']['total_sum_otkl_percent'] = "<span style='color:red;'>".$plan['plan_total_sum_outcome']['total_sum_otkl_percent']."</span>";
	}else{
		$plan['plan_total_sum_outcome']['total_sum_otkl'] = number_format($plan['plan_total_sum_outcome']['total_sum_otkl'], 2, '.', ' ');
	}
	
	//Остаток
	//План = Доходы план  расходы план
	//Факт = Доходы факт -расходы факт
	//Отклонение = остаток факт - остаток план
	//% Отклонения = Если( план=0 тогда ------- иначе =Отклонение/План*100%)
	$plan['plan_settings']['rest_plan_sum'] = $plan['plan_settings']['total_income'] - $plan['plan_settings']['total_outcome'];
	$plan['plan_settings']['rest_fact_sum'] = $plan['plan_total_sum_income']['total_sum'] - $plan['plan_total_sum_outcome']['total_sum'];
	$plan['plan_settings']['rest_otkl'] = $plan['plan_settings']['rest_fact_sum'] - $plan['plan_settings']['rest_plan_sum'];
	
	$plan['plan_settings']['rest_plan_sum'] = number_format($plan['plan_settings']['rest_plan_sum'], 2, '.', ' ');
	$plan['plan_settings']['rest_fact_sum'] = number_format($plan['plan_settings']['rest_fact_sum'], 2, '.', ' ');
	$plan['plan_settings']['rest_otkl'] = number_format($plan['plan_settings']['rest_otkl'], 2, '.', ' ');
	
	if ($plan['plan_settings']['rest_plan_sum'] == 0)
	{
		$plan['plan_settings']['rest_otkl_percent'] = '---';
	}else{
		$plan['plan_settings']['rest_otkl_percent'] = ($plan['plan_settings']['rest_otkl'] / $plan['plan_settings']['rest_plan_sum'])*100;
		$plan['plan_settings']['rest_otkl_percent'] = number_format($plan['plan_settings']['rest_otkl_percent'], 2, '.', ' ');
		$plan['plan_settings']['rest_otkl_percent'] .= "%";
	}
	
	$plan['plan_total_sum_income']['total_sum'] = number_format($plan['plan_total_sum_income']['total_sum'], 2, '.', ' ');
	$plan['plan_total_sum_outcome']['total_sum'] = number_format($plan['plan_total_sum_outcome']['total_sum'], 2, '.', ' ');
	$plan['plan_settings']['total_income'] = number_format($plan['plan_settings']['total_income'], 2, '.', ' ');
	$plan['plan_settings']['total_outcome'] = number_format($plan['plan_settings']['total_outcome'], 2, '.', ' ');
	
	$plan['plan_income'] = $categories_income;
	$plan['plan_outcome'] = $categories_outcome;	

	return $plan;
  } // getUserPlan
  
  /**
   * Удаляет план бюджета
   * @param string $user_id ID-пользователя
   * @param string $plan_id ID-плана
   * @param object $dbs Объект управления базой данных
   *
   * @return boolen или да или нет
   * @access public
   */
  public function deleteUserPlan($user_id, $plan_id, $dbs=null)
  {
	if (!$dbs) 
	{
		$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
		$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
	}

	if ($dbs->query("delete from plan_settings where user_id=? and plan_id=?",$user_id, $plan_id))
	{
		$dbs->query("delete from plan_fact where plan_id=?",$plan_id);
		$dbs->query("delete from plan_accounts where user_id=? and plan_id=?",$user_id, $plan_id);

		return true;
	}
	return false;
  } // deleteUserPlan
  
  public function recountPlan()
  {	
	return true;
  }
  
  public function checkFactCategories($fact, $plan)
  {
  	$fcnt = count($fact);
	$pcnt = count($plan);
	
	for($i=0; $i<$fcnt; $i++)
	{
		$result = false;
		for($j=0; $j<$pcnt; $j++)
		{
			if ($plan[$j]['category_id'] == $fact[$i]['cat_id'])
			{
				$result = true;
			}
		}
		if ($result == false)
		{
			$next_id = count($plan);
			$plan[$next_id]['plan_id'] = $plan[0]['plan_id'];
			$plan[$next_id]['category_id'] = $fact[$i]['cat_id'];
			$plan[$next_id]['drain'] = $plan[0]['drain'];
			$plan[$next_id]['total_sum'] = $fact[$i]['sum'];
			$plan[$next_id]['total_sum_plan'] = '0.00';
			$plan[$next_id]['cat_name'] = $fact[$i]['cat_name'];
			$plan[$next_id]['cat_parent'] = $fact[$i]['cat_parent'];
		}
	}
	return $plan;
  }
  
	/**
	* Пересобирает в массив отмеченные категории
	* @param array $categories массив категорий
	*
	* @return array
	* @access public
	*/
	public function isCheckCategory2($categories, $name)
	{		
		foreach ($categories as $k=>$v)
		{
			if (isset($v[0]) && $v[0] == 'on')
			{
				// для родительской категории устанавливается 0
				$data[$k] = $v[1] ? $v[1] : 0;
				if ($name == 'income')
				{
					$this->total_income += $data[$k];
				}else{
					$this->total_outcome += $data[$k];
				}
			}
		}
		return $data;
	}
	
	public function isCheckCategory($categories, $name)
	{		
		foreach ($categories as $k=>$v)
		{
			if (isset($v[0]) && $v[0] == 'on')
			{
				// для родительской категории устанавливается 0
				$data[$k] = $v[1] ? $v[1] : 0;
			}
			if (!empty($v[1]))
			{
				$data[$k] = $v[1];
				if ($name == 'income')
				{
					$this->total_income += $data[$k];
				}else{
					$this->total_outcome += $data[$k];
				}
			}
		}
		return $data;
	}
	
	public function getPrefixCategories($prefix, $user_id)
	{
		if (!$dbs) 
		{
			$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		
		$categories = $dbs->select("select * from category where user_id=? and type != ? and cat_active=1", $user_id, $prefix);
		return $categories;
	}
	
	public function getCategoriesFromPlan($id, $dbs = null, $drain)
	{
		if (!$dbs) 
		{
			$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		
		$categories = $dbs->select("select * from plan_fact where plan_id=? and drain=?", $id, $drain);
		$cnt = count($categories);
		for ($i=0; $i<$cnt; $i++)
		{
			$data[$categories[$i]['category_id']] = $categories[$i]['total_sum_plan'];
		}
		return $data;
	}
	
	public function geAccountsFromPlan($id, $dbs = null, $user_id)
	{
		if (!$dbs) 
		{
			$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		
		$accounts = $dbs->select("select * from plan_accounts where plan_id=$id and user_id='$user_id'");

		return $accounts;
	}
	
	public function getSettingsFromPlan($id, $dbs = null, $user_id)
	{
		if (!$dbs) 
		{
			$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		
		$plan = $dbs->select("select * from plan_settings where plan_id=$id and user_id='$user_id'");

		return $plan[0];
	}
	
	public function getListCopyPlan($dbs = null)
	{
		if (!$dbs) 
		{
			$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		
		$list = $dbs->select("select * from plan_settings where user_id=? order by date_start_plan", $_SESSION['user']['user_id']);

		return $list;
	}
	
	public function checkPeriodPlan($date_start, $date_finish, $dbs=null)
	{
		if (!$dbs) 
		{
			$dbs = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		
		$list = $dbs->select("select * from plan_settings where date_start_plan=? and date_finish_plan=? and user_id=?", 
							 $date_start, $date_finish, $_SESSION['user']['user_id']);
		if (count($list))
		{
			return false;	
		}
		return true;
	}
}

class PlanSettings
{
	public $plan = array();
	public $income = array();
	public $outcome = array();
	public $accounts = array();
	
	public function __construct()
	{
		$this->load();
	}
	
	private function load()
	{
		$this->plan = $_SESSION['plan'];
		$this->income = $_SESSION['income'];
		$this->outcome = $_SESSION['outcome'];
		$this->accounts = $_SESSION['accounts'];
	}
	
	public function save()
	{
		$_SESSION['plan'] = $this->plan;
		$_SESSION['income'] = $this->income;
		$_SESSION['outcome'] = $this->outcome;
		$_SESSION['accounts'] = $this->accounts;
	}
	
	public function getDateDetalizeCategoriesForPlan()
	{
		$planning_horizon = $this->plan['planning_horizon'];

		switch ($planning_horizon)
		{
			case "1":
				$week=date("W");
				$sun_day=7*$week-date("w",mktime(0,0,0,1,7*$week));

				$finish_date = date("Y-m-d",mktime(0,0,0,1,$sun_day-6));
				$start_date = date("Y-m-d",mktime(0,0,0,1,$sun_day-20));
			break;

			case "2":
				//$finish_date = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
				//$start_date = date("Y-m-d", mktime(0, 0, 0, date("m")-3, "01", date("Y")));

				$finish_date = date("Y-m-d", mktime(0, 0, 0, $this->plan['planning_month'], "01", $this->plan['planning_year']));
				$start_date = date("Y-m-d", mktime(0, 0, 0, date($this->plan['planning_month'])-3, "01", $this->plan['planning_year']));
			break;

			case "3":
				$finish_date = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
				$start_date = date("Y-m-d", mktime(0, 0, 0, date("m")-9, "01", date("Y")));
			break;

			case "4":
				$finish_date = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
				$start_date = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")-3));
			break;
		}
		
		$date['start_date'] = $start_date;
		$date['finish_date'] = $finish_date;
		
		return $date;
	}
}
?>