<?php
/**
   * ������ ���� ���� �� ������������� ������� ������� �������������
   * ����������, ��������� � ����� LICENSE
   */

/**
   * ������ ������ ������� CSV
   *
   * @author   ������� ����� <varenich@gmail.com>, �������, ������, 2008
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser_csv extends prs_Parser {
	/**
   * ���������� ������ � ������������ �������
   *
   * @param string $page ����� ��� �������
   * @return array ������ � ��������� �������
   * ����� �������:
   *  amount : string - ����� ����������
   *  date : string - ���� ����������
   *  category : string - �������� ���������
   *  comment : string - ����������� � ����������
   * @access public
   */
	function parse($page) {
		
		// ��������� �������:
		// delimiter - �����������
		// firstIsHeader - ������ ������ �������� ����������, �� ��������� ��
		// tr_date_name - ����� ���� ����
		// tr_amount_name - ����� ���� �����
		// tr_cat_name - ����� ���� ���������
		// tr_comment_name - ����� ���� �����������
		//
		// ����� � ������� "_name"  � ������� ����? �������...
		
		//pre($this->_conf);
		
		$dlm = $this->_conf['delimiter'];
		
		// ������� ��������� ���� � ���������� ���� ���� ���������
		$temp = tmpfile();
		fwrite($temp, $page);
		fseek($temp, 0);

		// ������
		while (($fields = fgetcsv($temp, 2000, $dlm)) !== FALSE) {

			// ����������� ���� � ������ Y.M.D
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
		
		// ������� ��������� ����
		fclose($temp);

		
		//pre($rowArray);
		return $rowArray;
	} // function parse

	/**
   * ���������� ������ � ����������� csv-�����
   *
   * @param string $page ����� ��� �������
   * @return array ������ � ������ (�����������) �����
   * @access public
   * @throws PEAR_Error
   */
	function getFields($page) {
		// ��������� ����� �� ������
		$lines = explode("\n",$page);
		// ����� ����� ������ ������
		$headersString = trim($lines[0]);
		// � ��������� �� �� ���� ��������� �����������
		$headers = explode($this->_conf['delimiter'],$headersString);
		return $headers;
	} // function getFields


} // class
?>