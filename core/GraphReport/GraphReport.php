<?php
/**
 * �������� � ���� ������ ���������� ������������ ������. �����-�������
 *
 * @author   ������� ����� <varenich@gmail.com>, �������, ������, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  GraphReport
 * @version  1.0
 */


/**
 * ����������� �����
 * 
 * @package  GraphReport
 * @access   public
 *
 */ 
class GraphReport {

 
  /**
   * ���������������� ��� ����������� ���������� ������
   *
   * @var      array
   * @access   private
   */
  protected $_conf;

  //--------------------------------

  /**
   * ����������� �������
   *
   * @param array $conf ���������������� ���
   * @throws Exception
   * @access public
   */ 
	public function __construct($conf){
		$this->_conf = $conf;
	} // __construct

 
  /**
   * ���������� ��������� ��������� ���������
   *
   * @param string $name �������� ����������� ����������
   * @param array  $conf ���������������� ��� ����������
   * @throws Exception
   * @access public
   */
  public function factory($name,$conf) {
  	// ��� ���������
    $cn = "GraphReport_".$name;
    $dn = dirname(__FILE__);
    // �������� �����, � ������� �������� ��������
    $fileName = $dn."/".$cn.".php";
	if (!file_exists($fileName)) throw new Exception("������ _ $name _ �� ��������������",1);
	require_once($fileName);
	// ������� ��������� ������� ���������
    $obj = new $cn($conf);
    return $obj;
  } // factory
} // class

  /**
 * ����������, ������� ������� ������������� �������� ����������� ��������
 * 
 * @package  GraphReport
 * @access   public
 *
 */ 
  interface iGraphReport
  {
  	/**
   * ������ ������
   *
   * @param mixed $data ������
   * @return string ������
   * @throws Exception
   * @access public
   */
  	public function build($data);
  } // interface iGraphReport
?>