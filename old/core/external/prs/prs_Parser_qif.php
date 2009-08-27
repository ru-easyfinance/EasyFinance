<?php
/**
   * Открыв этот файл Вы автоматически приняли условия Лицензионного
   * соглашения, указанные в файле LICENSE
   */

define ('HEADER_END_MARKER', '!Type:Bank');
define ('DATASET_BEGIN', 'D');
define ('DATASET_END', '^');

/**
   * Парсер файлов формата QIF
   *
   * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser_qif extends prs_Parser {
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
		$transactionAddress = "";
		$transactionCategory = "";
		$transactionCategorySub =  "";
		$transactionCleared = "";
		$transactionDollar = "";
		$transactionE = "";
		$transactionI = "";
		$transactionMemo = "";
		$transactionNumber = "";
		$transactionPartner = "";
		$transactionQ = "";
		$transactionY = "";
		/**
    * count Rows of csv
    *
    * @var int
    */
		$csvRow = 0;

		/**
    * is set true, after the header was ignored
    *
    * @var boolean
    */
		$headerIgnored = false;

		/***************************************************************************************************
		* Each item in a bank, cash, credit card, other liability,
		* or other asset account must begin with a letter that indicates the field in the Quicken register.
		* The non-split items can be in any sequence:
		* Field    Indicator Explanation
		* D    Date
		* T    Amount
		* C    Cleared status
		* N    Num (check or reference number)                              // first entry in description
		* P    Payee                                                 // entry in partner and title
		* M    Memo                                                // second entry in description
		* A    Address (up to five lines; the sixth line is an optional message)   // third entry in description
		* L    Category (Category/Subcategory/Transfer/Class)
		* S    Category in split (Category/Transfer/Class)
		* E    Memo in split
		* $    Dollar amount of split
		* ^    End of the entry
		***************************************************************************************************/
		$lines = explode("\n",$page);
		//pre($lines);

		foreach ($lines as $line) {
			$rowArray = NULL;

			//skip header
			/**if (!$headerIgnored) {
				$tmp = strpos($line, HEADER_END_MARKER);
				//Need this complex check as the source file is reported to say "Date" and Date
				//(with and without quotes) at random
				if (!$line == HEADER_END_MARKER) {
					$noValidFile = true;
				}
				if ($tmp !== false && $tmp <= 2) {
					$headerIgnored = true;
				}
				continue;
			}*/

			// Do not parse if line is a header
			$ignoreBody = (preg_match('/(Oth A|Oth L|Cash|Bank|CCard|Invst|Account|Cat|Class|Memorized)/',$line))?true:false;

			//pre($ignoreBody);
			
			if (!$ignoreBody) {

				if ($line{0} == DATASET_BEGIN && $line{0}!='!') {
					//all needed vars must be blank
					$transactionAddress = "";
					$transactionCategory = "";
					$transactionCategorySub =  "";
					$transactionCleared = "";
					$transactionDollar = "";
					$transactionE = "";
					$transactionI = "";
					$transactionMemo = "";
					$transactionNumber = "";
					$transactionPartner = "";
					$transactionQ = "";
					$transactionY = "";

					$date = substr($line,1);
					//format date YY-MM-DD or YYYY-MM-DD
					$date = $this->avoid_bad_sign($date);

					$date = str_replace("'", ".",$date);
					$date = str_replace("/", ".",$date);
					$valutaDate = explode(".", $date); //Valuta Date

					$valutaDate[0] = $this->strlen2($valutaDate[0]); // M
					$valutaDate[1] = $this->strlen2($valutaDate[1]); // D
					$valutaDate[2] = $this->strlen4($valutaDate[2]); // Y

					$date = $valutaDate[2].".".$valutaDate[0].".".$valutaDate[1]; // Y.M.D
				}
				elseif ($line{0} == "T") {
					//pre($line);
					$amount = substr($line,1);
					$amount = str_replace(",", "",$amount);
				}
				elseif ($line{0} == "P") {
					$transactionPartner = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "N") {
					$transactionNumber = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "M") {
					$transactionMemo = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "A") {
					$transactionAddress = $transactionAddress .$this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "L") {
					$transactionCategorySub = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "S") {
					$transactionCategory = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "$") {
					$transactionDollar = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "I") {
					$transactionI = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "Y") {
					$transactionY = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "C") {
					$transactionCleared = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "Q") {
					$transactionQ = $this->avoid_bad_sign(substr($line,1));
				}
				elseif ($line{0} == "E") {
					$transactionE = $this->avoid_bad_sign(substr($line,1));
				}
				/**
       			* transaction array
       			*
       			* @var array
       			*/
				if ($line{0} == DATASET_END) {
					/*echo $description = $transactionPartner
					.$transactionNumber
					.$transactionMemo
					.$transactionAddress
					.$transactionCategorySub
					.$transactionCategory
					.$transactionDollar
					.$transactionI
					.$transactionCleared
					.$transactionQ
					.$transactionE; */

					$rowArray = array (
					"categories" => explode(':',trim($transactionCategorySub.$transactionCategory)),

					// Insert your facts, what you wanna have to import in BADGER
					// DO NOT forget the "." between the vars and watch the comma at the end of the line
					"comment" => trim($transactionPartner.$transactionNumber.$transactionMemo.$transactionAddress.$transactionE),
					"date" => trim($date),
					"amount" => trim($amount),
					);
				}

				// if a row contains valid data
				if ($rowArray && $line{0} == DATASET_END) {
					/**
             * array of all transaction arrays
             *
             * @var array
             */
					$importedTransactions[$csvRow] = $rowArray;
					$csvRow++;

				} // if

			} // if ! ignoreBody


		} // foreach
		//pre($importedTransactions);
		//return array();

		return $importedTransactions;
	} // function parse

	// check the valueDate, if day | month have only 1 digit and adds an leading zero
	// those numbers could cause problems
	function strlen2($string) {
		if (strlen(trim($string)) == 1) {
			$string = "0".trim($string);
		}
		return $string;
	} // strlen2

	// check the valueDate, if year have only 2 digits.
	// If the number of the year is between 70 and 99 it will add a leading 19 else a leading 20
	// those numbers could cause problems
	function strlen4($string) {
		if (strlen(trim($string)) == 2) {
			if ($string >= 70 && $string <= 99) {
				$string = "19".trim($string);
			} else {
				$string = "20".trim($string);
			}
		}
		if (strlen(trim($string)) == 1) {
			$string = "200".trim($string);
		}
		return trim($string);
	} // strlen4

	// avoid " & \ in the title & description, those characters could cause problems
	function avoid_bad_sign($strings) {
		$strings = str_replace("\"", "", $strings);
		$strings = str_replace("\\", "", $strings);
		$strings = str_replace("\n", " ",$strings);
		$strings = str_replace("\r", " ",$strings);
		return $strings;
	} // avoid_bad_sign

} // class
?>