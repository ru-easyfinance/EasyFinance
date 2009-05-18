<?php
/**
 * Класс конечного преобразователя
 *
 * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  Transform
 * @version  1.0
 */


/**
 * Преобразует список транзакций home-money.ru в csv
 * 
 * @package  Transform
 * @access   public
 *
 */ 
class Transform_transactions_csv extends Transform implements iTransform  {

	/**
   * Трансформирует указанные данные в формат конечного преобразователя
   *
   * @param array $data Список транзакций. Массив, каждая строка которого - хэш
   * Ключи:
   * amount : number - Сумма транзакции
   * date : string - Дата транзакции
   * comment : string - Комментарий
   * category : Название категории
   * payer_account : string - Название счета, с которого произведено списывание денег
   * receiver_account : string - Название счета, на который было произведено начисление денег
   * @throws Exception
   * @access public
   */
    public function transform($data) {
    	if (!is_array($data)) throw new Exception('Список транзакций не явлется массивом',1);
    	// Разделитель значений csv
    	$dlm = $this->_conf['delimiter'];
    	if (!$dlm || !isset($dlm)) $dlm=';';
    	// Будущий csv
    	$res = '';
    	
    	foreach ($data as $itm) {
    		$itm['amount'] = preg_replace('/\./',',',$itm['amount']);
    		$itm['comment'] = preg_replace("/$dlm/",' ',$itm['comment']);
    		
    		$res .= $itm['payer_account'].$dlm.$itm['amount'].$dlm.$itm['date'].$dlm.$itm['comment'].$dlm.$itm['category'].$dlm.$itm['receiver_account']."\r\n";
    	}
    	return $res;
    } // transform

} // class
?>