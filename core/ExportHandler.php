<?php
/**
 * Обработчик бизнес-задачи экспорта данных
 *
 * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  home-money
 * @version  1.0
 */

require_once(SYS_DIR_LIBS.'/Transform/Transform.php');

/**
 * Класс контролера
 * 
 * @package  home-money
 * @access   protected
 *
 */ 
class ExportHandler {
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
   * Возвращает список транзакций пользователя по указанным счетам за указанный период
   *
   * @param string $format Код формата экспорта
   * @param string $userID Код пользователя
   * @param array $accounts Список кодов счетов
   * @param string $dateFrom Дата начала периода
   * @param string $dateTo Дата окончания периода
   *
   * @return array Список транзакций
   * @throws Exception
   * @access public
   */
  public function export($format,$userID,$accounts=array(),$dateFrom='',$dateTo='',$delimiter=';') {
  	if (count($accounts)==0 || !$dateFrom || !$dateTo || !$delimiter) throw new Exception('Не указаны обязательные параметры. Укажите их, пожалуйста',2);
  	  	
  	// Получаем список подходящих транзакций из хранилища
  	
  	$transactions = $this->_money->getTransactions($userID,$accounts,$dateFrom,$dateTo,$this->_account,$this->_category);
  	
  	$conf = array();
  	$conf['delimiter'] = $delimiter;
  	$res = $this->_transform($transactions,$format,$conf);
  	
  	$this->_download($res);
  } // export
  
  /**
   * 
   *
   * @param array $transactions Список транзакций
   * @param string $format Формат экспорта
   * @param array $conf Конфигурационный хэш экспорта
   *
   * @return mixed 
   * @throws Exception
   * @access public
   */
  private function _transform($transactions=array(),$format='transactions_cvs',$conf=array()) {
  	if (count($transactions)==0) throw new Exception('Нет транзакций',2);

  	$transform = Transform::factory($format,$conf);
  	
  	return $transform->transform($transactions);
  } // transform
  
  /**
   * Открывает окно сохранения файла с транзакциями
   *
   * @param string $downloadableFile Файл со списком транзакций
   *
   * @return void
   * @throws Exception
   * @access public
   */
  private function _download($downloadableFile) {
  	if (!$downloadableFile) throw new Exception('Пустой файл',1);
  	
  	header('Content-type: application/csv');
	header('Content-Disposition: attachment; filename="transactions.csv"');
	echo $downloadableFile;
	exit;
  } // download
} // class ExportHandler
?>