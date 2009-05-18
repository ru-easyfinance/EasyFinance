<?php
/**
   * Открыв этот файл Вы автоматически приняли условия Лицензионного
   * соглашения, указанные в файле LICENSE
   */

/**
   * Парсер файлов формата CSV
   *
   * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser_csv extends prs_Parser {
	/**
   * Возвращает массив с разобранными данными
   *
   * @param string $page Текст для разбора
   * @return array Массив с полезными данными
   * Ключи позиции:
   *  amount : string - Сумма транзакции
   *  date : string - Дата транзакции
   *  category : string - Название категории
   *  comment : string - Комментарий к транзакции
   * @access public
   */
	function parse($page) {
		
		// Параметры конфига:
		// delimiter - разделитель
		// firstIsHeader - первая строка является заголовком, не разбирать ее
		// tr_date_name - номер поля даты
		// tr_amount_name - номер поля суммы
		// tr_cat_name - номер поля категории
		// tr_comment_name - номер поля комментария
		//
		// Зачем я добавил "_name"  к каждому полю? Загадка...
		
		//pre($this->_conf);
		
		$dlm = $this->_conf['delimiter'];
		
		// Создаем временный файл и записываем туда нашу страничку
		$temp = tmpfile();
		fwrite($temp, $page);
		fseek($temp, 0);

		// Парсим
		while (($fields = fgetcsv($temp, 2000, $dlm)) !== FALSE) {

			// Преобразуем дату в формат Y.M.D
			$dt = $fields[$this->_conf['tr_date_name']];
			if (preg_match('/\./',$dt)) {
				list($d,$m,$y) = explode('.',$dt);
			}
			else {
				list($d,$m,$y) = explode('/',$dt);
			}
			$dt = "$y.$m.$d";

			$cm = $fields[$this->_conf['tr_comment_name']];
			
			$am = $fields[$this->_conf['tr_amount_name']];
			$am = preg_replace('/,/','',$am);
			
			$ct = $fields[$this->_conf['tr_cat_name']];
			$ct = preg_replace('/\'/','',$ct);
			$cats = explode(':',$ct);
			
			$rowArray[] = array (
			"categories" => $cats,
			"comment" => $cm,
			"date" => $dt,
			"amount" => $am,
			);
			
		} // while
		
		// Удаляем временный файл
		fclose($temp);

		
		//pre($rowArray);
		return $rowArray;
	} // function parse

	/**
   * Возвращает массив с заголовками csv-файла
   *
   * @param string $page Текст для разбора
   * @return array Массив с полями (заголовками) файла
   * @access public
   * @throws PEAR_Error
   */
	function getFields($page) {
		// Разбиваем текст на строки
		$lines = explode("\n",$page);
		// Берем самую первую строку
		$headersString = trim($lines[0]);
		// И разбиваем ее на поля используя разделитель
		$headers = explode($this->_conf['delimiter'],$headersString);
		return $headers;
	} // function getFields


} // class
?>