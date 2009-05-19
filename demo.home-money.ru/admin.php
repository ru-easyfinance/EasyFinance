<?php

if (!isset($_SERVER['PHP_AUTH_USER'])) {
	header("WWW-Authenticate: Basic realm='My Realm'");
	header("HTTP/1.0 401 Unauthorized");
	exit;
} else {
	if($_SERVER['PHP_AUTH_USER']!='demo' && $_SERVER['PHP_AUTH_PW'] != 'qwe123')
	{
		header("WWW-Authenticate: Basic realm='My Realm'");
		header("HTTP/1.0 401 Unauthorized");
		exit('введен неверный логин или пароль');
	}
}

define('SYS_DIR_ROOT',   "/home/rkorostov/data/www");
define('SYS_DIR_LIBS',   SYS_DIR_ROOT."/core/");

// DB settings
define('SYS_DB_HOST', 	'localhost');
define('SYS_DB_USER', 	'demo');
define('SYS_DB_PASS', 	'lw0Hraec');
define('SYS_DB_BASE', 	'demo_homemoney_ru');

require_once SYS_DIR_LIBS.'/db.class.php';

$db = new sql_db(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS, SYS_DB_BASE);

if(!$db->db_connect_id)
{
	message_error(CRITICAL_ERROR, "Could not connect to the database");
}

if ($_GET['id'])
{
	$sql = "select *, DATE_FORMAT(date_time,'%d.%m.%Y %H:%i:%s') as date_time from statistic where user_id = '".htmlspecialchars($_GET['id'])."' order by date_time";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrowset($result);
	
	$data = "
	<table width=100%>
			<tr>
				<td width=10 style='background-color:#B8C1DA; color:#FFF; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>#</td>
				<td width=180 style='background-color:#B8C1DA; color:#FFF; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>
					Модуль
				</td>
				<td width=180 style='background-color:#B8C1DA; color:#FFF; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>Действие</td>
				<td width=180 style='background-color:#B8C1DA; color:#FFF; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>Время</td>
			</tr>";
	
	$j=0;
	for($i=0;$i<count($row);$i++)
	{
		$j++;
		
		$data .= "
			<tr>
				<td width=10 style='border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>$j</td>
				<td width=180 style='border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>
					".$row[$i]['page']."&nbsp;
				</td>
				<td width=180 style='border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>
					".$row[$i]['action']."&nbsp;
				</td>
				<td width=180 style='border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;'>
					".$row[$i]['date_time']."&nbsp;
				</td>
			</tr>";		
	}
	$data .= "</table>";
	echo $data;
	exit;
}

$sql = "select *, DATE_FORMAT(date_time,'%d.%m.%Y %H:%i:%s') as date_time from statistic group by user_id order by date_time";
$result = $db->sql_query($sql);

$row = $db->sql_fetchrowset($result);

$sql = "select *, count(page) as cnt, DATE_FORMAT(date_time,'%d.%m.%Y %H:%i:%s') as date_time from statistic where page='budget' group by user_id order by date_time";
$result = $db->sql_query($sql);
$budget = $db->sql_fetchrowset($result);

$sql = "select *, count(page) as cnt, DATE_FORMAT(date_time,'%d.%m.%Y %H:%i:%s') as date_time from statistic where page='account' group by user_id order by date_time";
$result = $db->sql_query($sql);
$account = $db->sql_fetchrowset($result);

$sql = "select *, count(page) as cnt, DATE_FORMAT(date_time,'%d.%m.%Y %H:%i:%s') as date_time from statistic where page='operation' group by user_id order by date_time";
$result = $db->sql_query($sql);
$operation = $db->sql_fetchrowset($result);

$sql = "select *, count(page) as cnt, DATE_FORMAT(date_time,'%d.%m.%Y %H:%i:%s') as date_time from statistic where page='category' group by user_id order by date_time";
$result = $db->sql_query($sql);
$category = $db->sql_fetchrowset($result);
?>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>Статистика</title>
    
    <style type="text/css">
		.addElement
		{
			position: absolute;
			display: none;
			z-index: 100;
			border: 1px solid #B8C1DA;
			height: 500px;
			width: 550px;
			top: 20%;
			left: 30%;
			padding: 10px;
			background-color: white;
		}
	</style>
</head>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
	function getUserStatistic(id)
	{
		$.get(
			'/admin.php',
			{
				id: id
			},
			onAjaxSuccess
		);
		
		$("#userName").html('<b>Пользователь: '+id+'</b>');
		$("#addElement").css({display:"block"});
		$("#statistic").html('<img src="http://www.infopark.kz/_05/ajax-loader.gif"> Загрузка данных, подождите...');
	}
	
	function onAjaxSuccess(data)
	{
		$("#statistic").html(data);
	}
	
	function addElementClose()
	{
		data = "";
		$("#addElement").css({display:"none"});
		$("#statistic").html(data);
	}


</script>


<body>

<div id="list">
<table width="100%">
	<tr>
        <td width="3%" style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;">#</td>
        <td width="25%" style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:14px; font-family:Verdana, Geneva, sans-serif;">id-пользователя</td>
        <td width="10%" style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:14px; font-family:Verdana, Geneva, sans-serif;">Счета</td>
        <td width="10%" style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:14px; font-family:Verdana, Geneva, sans-serif;">Операции</td>
        <td width="10%" style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:14px; font-family:Verdana, Geneva, sans-serif;">Категории</td>
        <td width="10%" style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:14px; font-family:Verdana, Geneva, sans-serif;">Бюджет</td>
        <td width="20%" style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:14px; font-family:Verdana, Geneva, sans-serif;">время действия</td>
        <td style="background-color:#B8C1DA; color:#FFF; padding:3px; font-size:14px; font-family:Verdana, Geneva, sans-serif;">&nbsp;</td>
   	</tr>    
<?
$j=0;
	for($i=0;$i<count($row);$i++)
	{
		$j++;
	?>
    <tr>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;"><?=$j?></td>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;">
			<a href="javascript:getUserStatistic('<?=$row[$i]['user_id']?>');"><?=$row[$i]['user_id']?></a>
        </td>
        <?	
		$acc_cnt = 0;
		for ($n=0; $n<count($account); $n++)
		{
			if ($account[$n]['user_id'] == $row[$i]['user_id'])
			{
				$acc_cnt = $account[$n]['cnt'];
			}
		}
		$op_cnt = 0;
		for ($n=0; $n<count($operation); $n++)
		{
			if ($operation[$n]['user_id'] == $row[$i]['user_id'])
			{
				$op_cnt = $operation[$n]['cnt'];
			}
		}
		$cat_cnt = 0;
		for ($n=0; $n<count($category); $n++)
		{
			if ($category[$n]['user_id'] == $row[$i]['user_id'])
			{
				$cat_cnt = $category[$n]['cnt'];
			}
		}
		$bdg_cnt = 0;
		for ($n=0; $n<count($budget); $n++)
		{
			if ($budget[$n]['user_id'] == $row[$i]['user_id'])
			{
				$bdg_cnt = $budget[$n]['cnt'];
			}
		}
		?>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;"><?=$acc_cnt?>&nbsp;</td>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;"><?=$op_cnt?>&nbsp;</td>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;"><?=$cat_cnt?>&nbsp;</td>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;"><?=$bdg_cnt?>&nbsp;</td>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;"><?=$row[$i]['date_time']?></td>
        <td style="border:1px solid #B8C1DA; padding:3px; font-size:12px; font-family:Verdana, Geneva, sans-serif;">&nbsp;</td>
   	</tr> 
    <?		
	}
?>            
</table>
</div>

	<div id='addElement' class='addElement'>
    	<div style='float: left;' id="userName"></div>
		<div style='float: right;'><a href="javascript:addElementClose();">[х]</a></div>
		<div style='float: left; overflow-y:auto;overflow-x:hidden; height: 470px; width:550px; border:1px solid #B8C1DA;' id="statistic"></div>
	</div>


</body>

</html>