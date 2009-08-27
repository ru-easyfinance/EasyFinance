<?php
  /**
   * ������ ���� ���� �� ������������� ������� ������� �������������
   * ����������, ��������� � ����� LICENSE
   */
  
$dn = dirname(__FILE__);
require_once $dn.'/PEAR.php';

$dn = dirname (__FILE__);
require_once $dn.'/prs_Class.php';

  /**
   * ����������� �����, ����������� �������� ���������� ����������
   * ��������� �� �������� �������� ������ ��������� ��������� ������
   *
   * @author   ������� ����� <varenich@yahoo.com>,������� , ������, 2006
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser extends prs_Class {

  /**
   * ���������������� ���
   *
   * @var      array
   * @access   private
   */
  var $_conf;

  //--------------------------------

  /**
   * ����������� ��������
   *
   * @param array $conf ���������������� ���
   * @access public
   */
  function prs_Parser($conf) {
    parent::prs_Class();
    $this->_conf = $conf;
  } // prs_Parser

  /**
   * ���������� ��������� ��������� ����������
   *
   * @param string $name �������� ����������� ����������
   * @param array $conf ���������������� ��� ����������
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
   * ���������� ��������� ���������� �������� ���������� �� ��������
   *
   * @param string $page ��������� ��������
   * @return array ������ � ��������� �������
   * @access public
   */
  function parse($page) {
  } // parse

} // class
?>