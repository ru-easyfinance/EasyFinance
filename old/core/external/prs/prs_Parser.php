<?php
  /**
   * Открыв этот файл Вы автоматически приняли условия Лицензионного
   * соглашения, указанные в файле LICENSE
   */
  
$dn = dirname(__FILE__);
require_once $dn.'/PEAR.php';

$dn = dirname (__FILE__);
require_once $dn.'/prs_Class.php';

  /**
   * Абстрактный класс, реализующий создание конкретных подклассов
   * Вычленяет из страницы полезные данные используя указанный формат
   *
   * @author   Евгений Панин <varenich@yahoo.com>,Люберцы , Россия, 2006
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser extends prs_Class {

  /**
   * Конфигурационный хэш
   *
   * @var      array
   * @access   private
   */
  var $_conf;

  //--------------------------------

  /**
   * Конструктор объектов
   *
   * @param array $conf Конфигурационный хэш
   * @access public
   */
  function prs_Parser($conf) {
    parent::prs_Class();
    $this->_conf = $conf;
  } // prs_Parser

  /**
   * Возвращает экземпляр заданного контейнера
   *
   * @param string $name Название конкретного контейнера
   * @param array $conf Конфигурационный хэш контейнера
   * @access public
   */
  function factory($name,$conf) {
    $cn = "prs_Parser_".$name;
    $err = parent::_includeFile($cn);
    if (PEAR::isError($err)) return $err;
    if (class_exists($cn)) {
      $obj = new $cn($conf);
    }
    else {
      die ("Class $cn not exists! Turn error_reporting to E_ALL and look if _includeFile returns fail.");
    }
    return $obj;
  } // factory

  /**
   * Возвращает результат вычленения полезной информации из страницы
   *
   * @param string $page Текстовая страница
   * @return array Массив с полезными данными
   * @access public
   */
  function parse($page) {
  } // parse

} // class
?>