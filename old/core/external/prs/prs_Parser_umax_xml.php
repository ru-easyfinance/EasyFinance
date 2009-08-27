<?php
  /**
   * ������ ���� ���� �� ������������� ������� ������� �������������
   * ����������, ��������� � ����� LICENSE
   */

  /**
   * ������ XML-����, ���������������� umaxsearch.com
   *
   * @author   ������� ����� <varenich@yahoo.com>, �������, ������, 2006
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser_umax_xml extends prs_Parser {
  /**
   * ���������� ��������� ���������� �������� ���������� �� ��������
   *
   * @param string $page ��������� ��������
   * @return array ������ � ��������� �������. ���������� �� �������� ��������������.
   * ����� �������:
   *  url : string - URL �����
   *  site_name : string - �������� �����
   *  site_description : string - �������� ����
   *  bid : real - �������������� �� ������� �� ����,  � USD ($)
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
