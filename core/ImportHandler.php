<?php
/**
 * Обработчик бизнес-задачи импорта данных
 *
 * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  home-money
 * @version  1.0
 */

require_once(SYS_DIR_INC.'prs/prs_Parser.php');

/**
 * Класс контролера
 * 
 * @package  home-money
 * @access   protected
 *
 */ 
class ImportHandler {
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
   * db : object Account Объект контейнера счетов. Выполняет операции по извлечению данных о счетах из хранилища
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
  } // __construct
  
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
   * Импортирует все транзакции из файла в систему
   *
   * @param string $format Формат импорта
   * @param string $accountID Код счета, на который производится импорт
   * @param array $file2Import Файл с импортируемыми данными
   * @param array $params Разные дополнительные параметры выбранного формата импорта
   *
   * @return void
   * @throws Exception
   * @access public
   */
  public function import($userID,$format,$accountID,$file2Import,$params=array()) {
  	if (!$accountID || !$format || !$file2Import) throw new Exception('Не указаны обязательные параметры. Укажите их, пожалуйста',2);
  	  	
	// Инициализируем объект парсера
	$prs = prs_Parser::factory($format,$params);
	if (PEAR::isError($prs)) throw new Exception($prs->getMessage(),1);
	
	// Парсим файло
	$transactions = $prs->parse($file2Import);
	if (PEAR::isError($res)) throw new Exception($prs->getMessage(),2);
	
	if (count($transactions)==0) throw new Exception('Список транзакций пуст, импортировать нечего',2);
	
	// Создаем ID и дату импорта, чтобы потом можно было откатить
	$impId = $this->_genID();
	$impDate = date('Y.m.d H:i',time());

	// По-очереди добавляем транзакции в базу
	foreach ($transactions as $t) {

		$trCategories = $t['categories'];
		// В названии указаны сразу несколько категорий
		//pre($trCategories);

		//Проверяем список категорий на новые позиции, регистрируем их при необходимости
		//Возвращает название категории транзакции
		$catName = $this->_checkCategories($userID,$trCategories);
		
		// На последок получаем финальный список категорий
		$categories = $this->_category->getUserCategories($userID);
		
		//Определяем код категории транзакции
		$catID = array_search($catName,$categories);
		//pre($catID);
		
		$amount = $t['amount'];
		$drain = ($amount>0)?0:1;
		
		$this->_money->saveMoney(0, '', $catID, $amount, $t['date'], $drain, addslashes($t['comment']), $accountID,$impId,$impDate);
	} // foreach
  	
  } // import
  
  /**
   * Проверяет список категорий из импортируемых транзакций на наличие новых позиций
   * и корректно их регистрирует
   *
   * @param string $userID Код пользователя
   * @param array $trCategories Массив с названиями категорий
   *
   * @return string Название последней категории в списке
   * @throws Exception
   * @access public
   */
  private function _checkCategories($userID,$trCategories) {
  	// Регистрируем новые категории

  	//Составляем список категорий пользователя, чтобы смотреть, есть ли уже у пользователя категория из файла
  	$categories = $this->_category->getUserCategories($userID);
  	//pre($categories);
  	
  	$lastName = '';
  	
  	foreach ($trCategories as $catKey => $catName) {
  		$catKey = trim($catKey);
  		$catName = trim($catName);
  		$parent = '';
  		if (!$catName) $catName = 'Без категории';
		$lastName = $catName;


  		// Смотрим, зарегистрирова ли уже категория, указанная в транзакции
  		$ci = array_search($catName,$categories);
  		// Если нет - регистрируем ее
  		//$cat_type = ($ci)?0:1;
  		if ($ci) {
  			// Такая категория уже есть. Не делаем ничего
  			//$cat_type = 0;
  		}
  		else {
  			// Категории еще нет - регистрируем с корректным указанием родителя
  			// Определяем родителя. Если индекс категории > 0 , то предыдущий элемент - ее родитель
  			if ($catKey>0) {
  				// Находим название родителя
  				$parent = $trCategories[$catKey-1];
  				//pre($parent);
  				// Находим код родителя
  				$parID = array_search($parent,$categories);
  			}
  			else {
  				// Элемент первого уровня. У него нет родителя
  				$parID = 0;
  			}
  			// Регистрируем категорию
  			//pre("$parID=$catName");
  			$newCatId = $this->_category->saveCategory($parID,$catName);
  			// Добавляем ее в массив категорий, чтобы не перечитывать его заново из базы
  			$categories[$newCatId] = $catName;
  		} // Новая категория

  		//break;
  	} // foreach
  	
  	return $lastName;

  } //checkCategories
  
  /**
   * Возвращает список всех проведенных импортов
   * 
   *  @param string $userID Код пользователя
   *
   * @return array Список импортов. Значение - дата импорта
   * @throws Exception
   * @access public
   */
  public function getImportsList($userID) {
  	return $this->_money->getImportsList($userID);
  } // getImportsList
  
  
  
  /**
   * Генерирует уникальный идентификатор
   *
   * @return string
   * @access private
   */
  public function _genID() {
    return md5(uniqid('hmmon'));
  } // genID  
  
  /**
   * Откатывает указанный импорт
   * 
   * @param string $impID Код импорта
   * @param string $userID Код пользователя
   *
   * @return void
   * @throws Exception
   * @access public
   */
  public function rollbackImport($impID,$userID) {
  	$this->_money->rollbackImport($impID,$userID);
  } // rollbackImport
  
  /**
   * Возвращает массив с заголовками csv-файла
   *
   * @param string $file2Import Текст для разбора
   * @return array Массив с полями (заголовками) файла
   * @access public
   * @throws PEAR_Error
   */
	function getFields($file2Import,$delimiter) {

		if (!$file2Import || !$delimiter) throw new Exception('Не указаны обязательные параметры. Укажите их, пожалуйста',1);

		// Инициализируем объект парсера
		$params['delimiter'] = $delimiter;
		$format = 'csv';
		$prs = prs_Parser::factory($format,$params);
		if (PEAR::isError($prs)) throw new Exception($prs->getMessage(),1);

		// вытаскиеваем заголовки
		$headers = $prs->getFields($file2Import);
		if (PEAR::isError($res)) throw new Exception($prs->getMessage(),2);

		return  $headers;
	} // function getFields

} // class ExportHandler
?>
