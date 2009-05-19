<?php
/**
 * Обработчик бизнес-задачи "Проанализировать данные"
 *
 * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  home-money
 * @version  1.0
 */

require_once(SYS_DIR_LIBS.'/GraphReport/GraphReport.php');
/**
 * Класс контролера
 * 
 * @package  home-money
 * @access   public
 *
 */ 
class ReportHandler {
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
  
   /**
   * Производит инициализацию объектов
   *
   * @param array $conf Хэш с конфигурационными параметрами
   * Ключи:
   * db : object Account Объект контейнера счетов. Выполняет операции по извлечению данных о счетах из хранилища  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!	!!!!!!!!!!!!!! Изменить
   *
   * @return object ReportHandler
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
  } // __construct
  
  /**
   * Выводит графический отчет
   *
   * @param string $reportType Вид отчета
   * @param array $rpd Параметры отчета
   *
   * @return string Картинка
   * @throws Exception
   * @access public
   */
  public function displayGraphReport($reportType='graph_profit',$rpd) {
  	if (!$reportType) throw new Exception('Не указан вид отчета',1); 
  	
  	switch ($reportType) {
  		case 'graph_profit':{
  			$format = 'jpgraph_pie';
  			break;
  		} // case
  		default: {
  			throw new Exception('Неверный тип отчета',1);
  		}
  	} // switch
  	
  	$conf = array();
  	$gr = GraphReport::factory($format,$conf);
  	//pre($gr);

  	$data = array(
  		'зарплата'=>75000,
  		'халтура'=>20000,
  		);
  	return $gr->build($data);
  	
  } // displayGraphReport
  
  /**
   * Выводит данные о доходах пользователя за период с разбивкой по категориям доходов
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - код валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о доходе. Хэш.
   * ключ - название категории
   * значение - величина дохода
   * @throws Exception
   * @access public
   */
  public function getProfit($rpd) {
	if (!$rpd['dateFrom'] || !$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
	//if (!$rpd['account']) throw new Exception('Для этого отчета требуется выбрать счет',2);
	
  	return $this->_money->getProfit($rpd,$this->_category);
  } // getProfit
  
  /**
   * Выводит данные о доходах пользователя за период с разбивкой по категориям доходов
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о доходе. Хэш.
   * название категории => разбивка дохода по дням и счетам. Хэш
   * 	дата => список счетов. Хэш
   * 		счет => величина дохода в рублях?
   * @throws Exception
   * @access public
   */
  public function getDetailedProfit($rpd) {
	if (!$rpd['dateFrom'] || !$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	return $this->_money->getDetailedProfit($rpd);
  } // getDetailedProfit
  
  /**
   * Выводит данные о расходах пользователя за период с разбивкой по категориям расходов
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о доходе. Хэш.
   * название категории => разбивка дохода по дням и счетам. Хэш
   * 	дата => список счетов. Хэш
   * 		счет => величина дохода в рублях?
   * @throws Exception
   * @access public
   */
  public function getDetailedLoss($rpd) {
	if (!$rpd['dateFrom'] || !$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	return $this->_money->getDetailedLoss($rpd);
  } // getDetailedLoss
  
  /**
   * Возвращает список родителей для каждой пользовательской категории 
   *
   * @param string $userID Код пользователя
   *
   * @return array Список категорий и их родителей. Хэш.
   * Код категории => Список родителей. Хэш.
   * 	Код родительской категории => Ее название
   * @throws Exception
   * @access public
   */
  public function getCategoryPaths($userID) {
  	$catPaths = array();
  	// Получает полный список пользовательских категорий
  	$userCats = $this->_category->getUserCategories($userID);
  	foreach ($userCats as $catID=>$catName) {
  		// Для каждой категории определяем родителей в виде списк
  		$parents = array_reverse($this->_category->getParents($catID));
  		$catPaths[$catID] = $parents;
  	} // foreach
  	
  	//pre($catPaths);
  	return $catPaths;
  } // getCategoryPaths
  
  /**
   * Выводит данные о расходах пользователя за период с разбивкой по категориям расходов
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о расходе. Хэш.
   * ключ - название категории
   * значение - величина расхода
   * @throws Exception
   * @access public
   */
  public function getLoss($rpd) {
  	if (!$rpd['dateFrom'] || !$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	//if (!$rpd['account']) throw new Exception('Для этого отчета требуется выбрать счет',2);
  	
  	return $this->_money->getLoss($rpd,$this->_category);
  } // getLiabilities
  
  /**
   * Выводит данные о расходах и доходах пользователя за период с разбивкой по месяцам
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о расходах и доходах. Хэш.
   * profit
   * 	"название месяца,номер года" => величина дохода
   * loss
   * 	"название месяца,номер года" => величина расхода
   * 
   * Количество ключей "месяц, номер года" у profit & loss должно совпадать
   * @throws Exception
   * @access public
   */
  public function getProfitAndLoss($rpd) {
  	if (!$rpd['dateFrom'] || !$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	//if (!$rpd['account']) throw new Exception('Для этого отчета требуется выбрать счет',2);
  	
 	return $this->_money->getProfitAndLoss($rpd,$this->_category);
  } // getProfitAndLoss
  
  /**
   * Выводит данные о расходах пользователя за период с разбивкой по месяцам
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о расходах
   * ОПИСАТЬ!!!
   * 
   * @throws Exception
   * @access public
   */
  public function getLossDifference($rpd) {
  	if (@!$rpd['dateFrom'] || @!$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	if (@!$rpd['dateFrom2'] || @!$rpd['dateTo2']) throw new Exception('Не указаны даты 2-го периода. Укажите их, пожалуйста',2);
  	
  	// Получаем расходы по 1-му интервалу
  	$loss1 = $this->_money->getLoss($rpd,$this->_category);
  	//pre($loss1);
  	
  	// Получаем расходы по 2-му интервалу
  	$rpd['dateFrom'] = $rpd['dateFrom2'];
  	$rpd['dateTo'] = $rpd['dateTo2'];
  	$loss2 = $this->_money->getLoss($rpd,$this->_category);
  	
  	// Объединяем два массива в один из двух колонок
  	$res = array();
  	foreach ($loss1 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss1'] = $itm['sum'];
  		$res[$catName]['loss2'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
  	foreach ($loss2 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss2'] = $itm['sum'];
  		if (!key_exists('loss1',$res[$catName])) $res[$catName]['loss1'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
 	return $res;
  } // getLossDifference
  
  /**
   * Выводит данные о доходах пользователя за периоды с разбивкой по категориям
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о доходах. Хэш.
   * ОПИСАТЬ!!!
   * 
   * @throws Exception
   * @access public
   */
  public function getProfitDifference($rpd) {
  	if (@!$rpd['dateFrom'] || @!$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	if (@!$rpd['dateFrom2'] || @!$rpd['dateTo2']) throw new Exception('Не указаны даты 2-го периода. Укажите их, пожалуйста',2);
  	
  	// Получаем доходы по 1-му интервалу
  	$loss1 = $this->_money->getProfit($rpd,$this->_category);
  	//pre($loss1);
  	
  	// Получаем доходы по 2-му интервалу
  	$rpd['dateFrom'] = $rpd['dateFrom2'];
  	$rpd['dateTo'] = $rpd['dateTo2'];
  	$loss2 = $this->_money->getProfit($rpd,$this->_category);
  	
  	// Объединяем два массива в один из двух колонок
  	$res = array();
  	foreach ($loss1 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss1'] = $itm['sum'];
  		$res[$catName]['loss2'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
  	foreach ($loss2 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss2'] = $itm['sum'];
  		if (!key_exists('loss1',$res[$catName])) $res[$catName]['loss1'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
 	return $res;
  } // getProfitDifference
  
  /**
   * Возвращает список всех счетов указанного пользователя
   *
   * @param string $userID Код пользователя
   *
   * @return array Список счетов пользователя
   * @throws Exception
   * @access public
   */
  public function getUserAccounts($userID='') {
  	if (!$userID) throw new Exception('Не указан код пользователя',1); 	
  	return $userAccounts = $this->_account->getUserAccounts($userID);
  } // getUserAccounts
  
  /**
   * Выводит сравнение доходов пользователя со средним за периоды с разбивкой по категориям
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о доходах. Хэш. Ключ - название категории.
   * Значения:
   * loss1 double Сумма доходов за период 1 по категории
   * loss2 double Средний доход за период 2 по категории
   * catID string Код категории
   * 
   * 
   * @throws Exception
   * @access public
   */
  public function getProfitAvgDifference($rpd) {
  	if (@!$rpd['dateFrom'] || @!$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	if (@!$rpd['dateFrom2'] || @!$rpd['dateTo2']) throw new Exception('Не указаны даты 2-го периода. Укажите их, пожалуйста',2);
  	
  	// Получаем доходы по 1-му интервалу
  	$loss1 = $this->_money->getProfit($rpd,$this->_category);
  	//pre($loss1);
  	
  	// Получаем доходы по 2-му интервалу
  	$rpd['dateFrom'] = $rpd['dateFrom2'];
  	$rpd['dateTo'] = $rpd['dateTo2'];
  	$loss2 = $this->_money->getProfit($rpd,$this->_category);
  	
  	//pre($loss1);
  	// Объединяем два массива в один из двух колонок
  	$res = array();
  	foreach ($loss1 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss1'] = $itm['sum'];
  		$res[$catName]['loss2'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
  	foreach ($loss2 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss2'] = $itm['sum']/$itm['cn'];
  		if (!key_exists('loss1',$res[$catName])) $res[$catName]['loss1'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
  	
  	//pre($res);
 	return $res;
  } // getProfitAvgDifference
  
  /**
   * Выводит сравнение расходов пользователя со средним за периоды с разбивкой по категориям
   *
   * @param array $rpd Параметры отчета
   * userID - код пользователя
   * dateFrom - дата начала периода в формате дд.мм.гггг
   * dateTo - дата окончания периода в формате дд.мм.гггг
   * account - код счета
   * currency_rates - курсы валют по отношению к рублю. Хэш. Код=>Курс
   * currency - валюты, в которой показывать сумму
   * months - названия месяцев. Хэш. Номер=>Название
   *
   * @return array Данные о расходах. Хэш. Ключ - название категории.
   * Значения:
   * loss1 double Сумма расходов за период 1 по категории
   * loss2 double Средний расходов за период 2 по категории
   * catID string Код категории
   * 
   * 
   * @throws Exception
   * @access public
   */
  public function getLossAvgDifference($rpd) {
  	if (@!$rpd['dateFrom'] || @!$rpd['dateTo']) throw new Exception('Не указаны даты. Укажите их, пожалуйста',2);
  	if (@!$rpd['dateFrom2'] || @!$rpd['dateTo2']) throw new Exception('Не указаны даты 2-го периода. Укажите их, пожалуйста',2);
  	
  	$days1 = $this->_calcDaysDiff($rpd['dateFrom'],$rpd['dateTo']);
  	$days2 = $this->_calcDaysDiff($rpd['dateFrom2'],$rpd['dateTo2']);
  	
  	// Получаем расходы по 1-му интервалу
  	$loss1 = $this->_money->getLoss($rpd,$this->_category);
  	//pre($loss1);
  	
  	// Получаем расходы по 2-му интервалу
  	$rpd['dateFrom'] = $rpd['dateFrom2'];
  	$rpd['dateTo'] = $rpd['dateTo2'];
  	$loss2 = $this->_money->getLoss($rpd,$this->_category);
  	
  	//pre($loss1);
  	// Объединяем два массива в один из двух колонок
  	$res = array();
  	foreach ($loss1 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss1'] = $itm['sum'];
  		$res[$catName]['loss2'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
  	foreach ($loss2 as $catID=>$itm) {
  		$catName = $itm['catName'];
  		$res[$catName]['loss2'] = ($itm['sum']/$days2)*$days1;
  		if (!key_exists('loss1',$res[$catName])) $res[$catName]['loss1'] = 0;
  		$res[$catName]['catID'] = $catID;
  	}
  	
  	//pre($res);
 	return $res;
  } // getLossAvgDifference
  
  /**
   * Подсчитывает количество дней между указанными датами
   *
   * @param string $dateFrom Дата начала периода
   * @param string $dateTo Дата окончания периода
   *
   * @return int Количество дней между датами
   * 
   * @throws Exception
   * @access public
   */
  private function _calcDaysDiff($dateFrom='',$dateTo='') {
  	if (@!$dateFrom || @!$dateTo) throw new Exception('Не указаны даты периода. Укажите их, пожалуйста',2);
  	
  	// Переводим даты в формат ISO
  	list($day,$month,$year) = explode(".", $dateFrom);
	$dateFrom = $year."-".$month."-".$day;
	
	list($day,$month,$year) = explode(".", $dateTo);
	$dateTo = $year."-".$month."-".$day;
  	
	// Создаем объекты с датами
	$dtFromS = strtotime($dateFrom);
  	$dtToS = strtotime($dateTo);
	
  	// Получаем количество секунд с начала эпохи, вычисляем разницу и переводим ее в дни
  	$dtDiffS = $dtToS-$dtFromS;
  	$dtDiffM = $dtDiffS/60;
  	$dtDiffH = $dtDiffM/60;
  	$dtDiffD = $dtDiffH/24+1;
  	 	
  	return $dtDiffD;
  } // _calcDaysDiff

} // class ReportHandler
?>