<?php
/**
 * Abstract class with common simple methods
 *
 * @author   Eugene Panin <varenich@yahoo.com>, Lubertsy, Russia, 2006
 * @home http://www.tortuga.pp.ru
 * @package  prs
 * @depends PEAR (http://pear.php.net)
 * @version  1.0
 */

$dn = dirname(__FILE__);
require_once $dn.'/PEAR.php';

class prs_Class {

  function prs_Class() {
    $err = prs_Class::_includeFile('prs_errors');
    if (PEAR::isError($err)) die('ERROR: '.$err->getMessage());
  }

  /**
   * Includes indicated class
   *
   * @param string $fName Class' file name without ".php"
   * @param string $customDir Path to custom include class. Default is empty.
   * @return void
   * @throws object PEAR_Error
   * @access protected
   */
  function _includeFile($fName,$customDir='') {
    $dn = dirname(__FILE__);
    $dirName = ($customDir)?$customDir:$dn;
    $fileName = $dirName."/".$fName.".php";
    
    if (!file_exists($fileName)) return new prs_NoFileExists_Error('No '.$fileName.' file exists in '.__FILE__.' at '.__LINE__);
    //echo "#####";
    //echo "->$fileName<-";
    //error_reporting(E_ALL);
    require_once $fileName;
  } // function _includeFile

  /**
   * Генерирует уникальный идентификатор
   *
   * @return string
   * @access protected
   */
  function _genID() {
    return md5(uniqid('prs'));
  } // genID  

  /**
   * Производит добавление слэшей, если не включен PHP magic quotes gpc
   *
   * @param string $txt Текст для модификации
   * @return string Результат
   * @access protected
   */
  function _as($txt) {
    if (!get_magic_quotes_gpc()) {
      $txt = addslashes($txt);
    }
    return $txt;
  }  // _as

} // class
?>
