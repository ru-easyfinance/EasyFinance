<?php
  /**
   * ������ ���� ���� �� ������������� ������� ������� �������������
   * ����������, ��������� � ����� LICENSE
   */

  /**
   * ������ XML-����, ���������������� umaxsearch.com
   * ��������� XML � ������� ���������� DOM
   *
   * @author   ������� ����� <varenich@yahoo.com>, �������, ������, 2006
   * @package  prs
   * @version  1.0
   * @access   public
   */
class prs_Parser_umax_xml_dom extends prs_Parser {
  /**
   * ���������� ��������� ���������� �������� ���������� �� ��������
   *
   * @param string $page ��������� ��������
   * @return array ������ � ��������� �������. ���������� �� �������� ��������������.
   * ����� �������:
   *  url : string - URL �����
   *  click_url : string - URL ��� �������
   *  name : string - �������� �����
   *  description : string - �������� ����
   *  bid : real - �������������� �� ������� �� ����,  � USD ($)
   * @access public
   */
  function parse($page) {
    $dom = new DOMDocument();
    // ������ XML
    $dom->loadXML( $page );
    // �������� ������ ���� �������
    $nodeList = $dom->getElementsByTagName( 'record' );

    // ��������� ������� ��� ���������� �� ����
    $bids = array();
    // ����������� ������ �� ������ ������
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
