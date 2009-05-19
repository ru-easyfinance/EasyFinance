<?php
  /**
   * Открыв этот файл Вы автоматически приняли условия Лицензионного
   * соглашения, указанные в файле LICENSE
   */

  /**
   * Парсер XML-фида, предоставляемого umaxsearch.com
   * Разбирает XML с помощью библиотеки DOM
   *
   * @author   Евгений Панин <varenich@yahoo.com>, Люберцы, Россия, 2006
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser_umax_xml_dom extends prs_Parser {
  /**
   * Возвращает результат вычленения полезной информации из страницы
   *
   * @param string $page Текстовая страница
   * @return array Массив с полезными данными. Упорядочен по убыванию вознаграждения.
   * Ключи позиции:
   *  url : string - URL сайта
   *  click_url : string - URL для нажатия
   *  name : string - Название сайта
   *  description : string - Описание сайт
   *  bid : real - Вознаграждение за переход на сайт,  в USD ($)
   * @access public
   */
  function parse($page) {
    $dom = new DOMDocument();
    // Парсим XML
    $dom->loadXML( $page );
    // Получаем список всех записей
    $nodeList = $dom->getElementsByTagName( 'record' );

    // Служебный массиво для сортировки по биду
    $bids = array();
    // Вытаскиваем данные из каждой записи
    for ($i=0;$i<$nodeList->length;$i++) {
        $node = $nodeList->item($i);
        
        $name = $node->getElementsByTagName('title')->item(0)->nodeValue;
        $description = $node->getElementsByTagName('description')->item(0)->nodeValue;
        $url = $node->getElementsByTagName('url')->item(0)->nodeValue;
        $clickUrl = $node->getElementsByTagName('clickurl')->item(0)->nodeValue;
        $bid = $node->getElementsByTagName('bid')->item(0)->nodeValue;
        
        $res[] = array (
		    'url' => $url,
		    'click_url' => $clickUrl,
		    'name' => $name,
        	'description' => $description,
	   	    'bid' => $bid,
	   	     );

        $bids[$i] = $bid;
    } // for

    array_multisort($bids, SORT_DESC, $res);

    return $res;
  } // parse

} // class
?>
