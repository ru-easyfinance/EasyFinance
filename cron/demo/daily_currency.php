<?php
$today = date("d/m/Y");
$file = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=".$today;
$map_array = array("CHARCODE","VALUE");
$data_array = array("USD","EUR", "UAH");
$if = false;
$res = array();
$tag = false;
$error_xml = false;

	function startElement($parser, $name, $attrs) 
	{
		global $map_array, $if, $res;
		if (in_array($name,$map_array)) {
			$if = $name;
			//$res[$name];
			//echo "<$map_array[$name]>";
		}
	}
	
	function endElement($parser, $name) 
	{
		global $map_array;
		if (isset($map_array[$name])) {
			//echo "</$map_array[$name]>";
		}
	}
	
	function characterData($parser, $data) 
	{
		global $if, $res, $data_array, $tag;
		if ($if)
		{
			if($tag)
			{
				$res[][$tag]=$data;
				$tag = false;
			}
			
			if (in_array($data, $data_array))
			{
				$tag=$data;
			}			
			$if = false;
		}
		//echo $data;
	}
	
	$xml_parser = xml_parser_create();
	// use case-folding so we are sure to find the tag in $map_array
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
	xml_set_element_handler($xml_parser, "startElement", "endElement");
	xml_set_character_data_handler($xml_parser, "characterData");
	if (!($fp = fopen($file, "r"))) {
		//die("could not open XML input");
		$error_xml = 'could not open XML input';
	}else{	
		while ($data = fread($fp, 4096)) {
			if (!xml_parse($xml_parser, $data, feof($fp))) {
				die(sprintf("XML error: %s at line %d",
							xml_error_string(xml_get_error_code($xml_parser)),
							xml_get_current_line_number($xml_parser)));
			}
		}
		xml_parser_free($xml_parser);
	}

	if (empty($error_xml))
	{
		if (!$dbs)
		{
			require_once ("/home/rkorostov/data/www/core/db.class.php");
			define('SYS_DB_HOST', 	'localhost');
			define('SYS_DB_USER', 	'homemone');
			define('SYS_DB_PASS', 	'lw0Hraec');
			define('SYS_DB_BASE', 	'demo_homemoney_ru');
			$db = new sql_db(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS, SYS_DB_BASE);			
		}
		
		$arr_surrency=array(
                'USD' => 2,
                'EUR' => 3,
				'UAH' => 4);
		
		for ($i=0; $i<count($res); $i++)
		{
			foreach ($res[$i] as $key=>$value)
			{
				$sum = str_replace(",", ".", $value);
				list($day, $month, $year) = explode("/", $today);
				$currency_date = $year."-".$month."-".$day;
				
				$sql = "select * from daily_currency where currency_date='".$currency_date."' and currency_id = '".$arr_surrency[$key]."'";
				$db->sql_query($sql);
				$row = $db->sql_fetchrowset($result);	
				
				if (!count($row))
				{				
					$sql = "insert into `daily_currency` 
							(`currency_id`,`currency_date`, `currency_sum`) 
						value 
							('".$arr_surrency[$key]."', '".$currency_date."', '".$sum."')";
					$db->sql_query($sql);
				}else{
					$error_text = "Не удалось сохранить! report: курс выбранной валюты на ".$today." существует!";	
				}
			}
		}
	}

?>
