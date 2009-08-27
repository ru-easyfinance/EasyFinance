<?php
  /**
   * Открыв этот файл Вы автоматически приняли условия Лицензионного
   * соглашения, указанные в файле LICENSE
   */

  /**
   * Парсер XML-фида, предоставляемого umaxsearch.com
   *
   * @author   Евгений Панин <varenich@yahoo.com>, Люберцы, Россия, 2006
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser_umax_xml extends prs_Parser {
  /**
   * Возвращает результат вычленения полезной информации из страницы
   *
   * @param string $page Текстовая страница
   * @return array Массив с полезными данными. Упорядочен по убыванию вознаграждения.
   * Ключи позиции:
   *  url : string - URL сайта
   *  site_name : string - Название сайта
   *  site_description : string - Описание сайт
   *  bid : real - Вознаграждение за переход на сайт,  в USD ($)
   * @access public
   */
  function parse($page) {
    $res[] = array (
		  'url' => 'http://www.tradeleadscenter.com',
		  'name' => 'International import-export trade leads board',
		  'description' => 'Super-puper site where you can find anything you need',
		  'bid' => 1,
		  );
    return $res;
  } // parse

} // class
?>
