<?php
if ($_GET['area'] == 'automatic')
{
	include(SYS_DIR_ROOT.'/cron/homemoney/daily_currency.php');

	if (isset($error_xml) && $error_xml != '')
	{
		$error_text = $error_xml;
	}
}

if (!empty($_POST['currency_name']))
{
	$currency_name = html($_POST['currency_name']);
	$currency_sum = html($_POST['currency_sum']);
	list($day, $month, $year) = explode(".", $_POST['currency_date']);
	$currency_date = $year."-".$month."-".$day;
	
	$sql = "select * from daily_currency where currency_date='".$currency_date."' and currency_id = '".$currency_name."'";
	$db->sql_query($sql);
	$row = $db->sql_fetchrowset($result);
	
	if (count($row))
	{
		$error_text = "Не удалось сохранить! report: курс выбранной валюты на ".html($_POST['currency_date'])." существует!";	
	}else{
		$sql = "insert into `daily_currency` (`currency_id`,`currency_date`, `currency_sum`) value ('$currency_name', '$currency_date', '$currency_sum')";

		if ($db->sql_query($sql))
		{
			header("location: index.php?modules=admin&action=manage_daily_currency");
		}else{
			$error_text = "Не удалось сохранить!";
		}
	}
}

if (!empty($_GET['f_currency_name']))
{
	$filter['cur_id'] = html($_GET['f_currency_name']);
	$filter['w_cur_id'] = " and currency_id = '".html($_GET['f_currency_name'])."'";
}

if (!empty($_GET['dateFrom']))
{
	$filter['cur_date_from'] = html($_GET['dateFrom']);
	list($day, $month, $year) = explode(".", $_GET['dateFrom']);
	$filter['w_cur_date_from'] = " and currency_date >= '".$year."-".$month."-".$day."'";
}

if (!empty($_GET['dateTo']))
{
	$filter['cur_date_to'] = html($_GET['dateTo']);
	list($day, $month, $year) = explode(".", $_GET['dateTo']);
	$filter['w_cur_date_to'] = " and currency_date <= '".$year."-".$month."-".$day."'";
}

$sql = "select dc.*, c.*, DATE_FORMAT(dc.currency_date,'%d.%m.%Y') as currency_date from daily_currency dc 
		left join currency c on dc.currency_id = c.cur_id 
		where 1=1 ".$filter['w_cur_id']." ".$filter['w_cur_date_from']." ".$filter['w_cur_date_to']."
		order by dc.currency_date desc";
		
$result = $db->sql_query($sql);
$row = $db->sql_fetchrowset($result);

$sql = "select * from daily_currency where currency_date = '2009-04-01'";
$db->sql_query($sql);
$row_d = $db->sql_fetchrowset($result);


$sql = "select * from currency";
$result = $db->sql_query($sql);
$row_c = $db->sql_fetchrowset($result);

$tpl->assign('error_text', $error_text);
$tpl->assign('filter', $filter);
$tpl->assign('currencies', $row);
$tpl->assign('currency_names', $row_c);
$tpl->assign('name_page', 'admin/admin.manage_daily_currency');

?>