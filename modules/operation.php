<?php
/**
 * Модуль "Операции по счету"
 *
 * @author   Роман Коростов <korogen@gmail.com> Москва, Россия, 2008
 * @package  home-money
 * @version  2.0
 */

if (empty($_SESSION['user']))
{
	header("Location: index.php");
}

$tpl->assign('name_page', 'operation');

$cnt = count($_SESSION['user_account']);
$currentAccount = (!empty($g_a)) ? html($g_a) : $_SESSION['user_account'][0]['id'];
$currentType = 0;
$tpl->assign('operationTpl','default');


	/*for ($i=0; $i<$cnt; $i++)
	{
		if ($_SESSION['user_account'][$i]['id'] == $currentAccount)
		{
			switch ($_SESSION['user_account'][$i]['type'])
			{
				case 4:
					$currentType = 4;
					$tpl->assign('operationTpl','deposit');
					$tpl->assign('aboutAccount', $acc->getAboutDeposit($currentAccount));
					break;
				default:
					$tpl->assign('operationTpl','default');
					break;
			}
		}
	}*/

require_once SYS_DIR_LIBS.'/money.class.php';

$action = html($g_action);
$area = html($g_area);

$money = new Money($db, $user);

$tpl->assign('accounts', $_SESSION['user_account']);
$tpl->assign('currentAccount', $currentAccount);
$tpl->assign('dateFrom', date('01.m.Y'));
$tpl->assign('dateTo', date('d.m.Y'));
$tpl->assign('categories', get_three_select($_SESSION['user_category']));

$i=0;
$parent_category[0]['cat_name'] = "";
$count = count($_SESSION['user_category']);

for($i=0; $i<$count;$i++)
{
	if ($_SESSION['user_category'][$i]['cat_parent']==0)
	{
		$parent_category[$_SESSION['user_category'][$i]['cat_id']]['parent_name'] = $_SESSION['user_category'][$i]['cat_name'];
	}
}

switch( $action )
{
	case "updateOperation":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;

		$id = html($g_id);
		$bill_id = html($g_a);
		$cat_id = html($g_cat_id);
		$date = html($g_dateTo);
		$type = html($g_type);
		$comment = html($g_comment);
		//$comment = iconv('utf-8', 'windows-1251', $comment);
		$sum = html($g_sum);
		$cat_name = '';
		$cat_type = 0;

		list($tday,$tmonth,$tyear) = explode(".", $date);
		$date =$tyear.".".$tmonth.".".$tday;

		if(isset($g_cat_type) && $g_cat_type == 1)
		{
			$cat_type = $g_cat_type;
			$cat_name = html($g_cat_name);
		}

		switch ($type)
		{
			case '0':
				$drain = 1;
				$sum = $sum * -1;
				break;

			case '1':
				$drain = 0;
				break;
		}
		$money->editOperation($id, $cat_type, $cat_name, $cat_id, $sum, $date, $drain, $comment, $bill_id);
		break;
	case "addOperation":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;

		$bill_id = html($g_a);
		$cat_id = html($g_cat_id);
		$date = html($g_dateTo);
		$type = html($g_type);
		$comment = html($g_comment);
		//$comment = iconv('utf-8', 'windows-1251', $comment);
		$sum = html($g_sum);
		$cat_name = '';
		$cat_type = 0;

		list($tday,$tmonth,$tyear) = explode(".", $date);
		$date =$tyear.".".$tmonth.".".$tday;

		if(isset($g_cat_type) && $g_cat_type == 1)
		{
			$cat_type = $g_cat_type;
			$cat_name = html($g_cat_name);
		}

		switch ($type)
		{
			case '0':
				$drain = 1;
				$sum = $sum * -1;
				$money->saveMoney($cat_type, $cat_name, $cat_id, $sum, $date, $drain, $comment, $bill_id);
				break;

			case '1':
				$drain = 0;
				$money->saveMoney($cat_type, $cat_name, $cat_id, $sum, $date, $drain, $comment, $bill_id);
				break;
		}

		break;
	case "addOperationDeposit":
		$bill_id = html($g_a);
		$date = html($g_dateTo);
		$sum = html($g_sum);
		$to_account = html($g_toAccount);
		$convert = $sum / $g_currency;

		list($tday,$tmonth,$tyear) = explode(".", $date);
		$date =$tyear.".".$tmonth.".".$tday;

		$money->addOperationDeposit($bill_id, $date, $sum, $to_account, $convert);

		break;
	case "addTransfer":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;

		$bill_id = html($g_a);
		$cat_id = -1;
		$date = html($g_dateTo);
		$type = html($g_type);
		$comment = html($g_comment);
		//$comment = iconv('utf-8', 'windows-1251', $comment);
		$sum = html($g_sum);

		$to_account = html($g_toAccount);
		$convert = $sum / $g_currency;

		list($tday,$tmonth,$tyear) = explode(".", $date);
		$date =$tyear.".".$tmonth.".".$tday;

		if ($type == 2)
		{
			$money->addOperationTransfer($sum, $convert, $date, $bill_id, $to_account, $comment);
		}
		break;
	case "updateTransfer":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		$id = html($g_id);
		$bill_id = html($g_a);
		$cat_id = -1;
		$date = html($g_dateTo);
		$type = html($g_type);
		$comment = html($g_comment);
		//$comment = iconv('utf-8', 'windows-1251', $comment);
		$sum = html($g_sum);
		$to_account = html($g_toAccount);
		$convert = $sum / $g_currency;

		list($tday,$tmonth,$tyear) = explode(".", $date);
		$date =$tyear.".".$tmonth.".".$tday;

		if ($type == 2)
		{
			$money->editOperationTransfer($id, $convert, $sum, $date, $to_account, $bill_id, $comment);
		}
		break;
	case "getCurrency":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		$id = html($g_id);
		$currentId = html($g_currentId);
		$count = count($_SESSION['user_account']);

		for ($i=0; $i<$count; $i++)
		{
			if ($_SESSION['user_account'][$i]['id'] == $id)
			{
				$account['currency'] = $_SESSION['user_account'][$i]['currency'];
				$account['currency_name'] = $_SESSION['user_account'][$i]['currency_name'];
			}
			if ($_SESSION['user_account'][$i]['id'] == $currentId)
			{
				$account['currency_current'] = $_SESSION['user_account'][$i]['currency'];
				$account['currency_current_name'] = $_SESSION['user_account'][$i]['currency_name'];
			}
		}

		if ($g_type == 'edit')
		{
			$g_type = 'edit';
			$data = "<input type='hidden' name='currency' id='currency_edit' value='1'>";
		}else{
			$g_type = 'add';
			$data = "<input type='hidden' name='currency' id='currency_add' value='1'>";
		}

		if ($account['currency_current'] != $account['currency'])
		{
			$count = count($sys_currency);
			$course = $sys_currency[$account['currency']];
			list($c1,$c2) = explode(",", $course);
			if (!empty($c2)) $c2 = ".".$c2;
			$course =$c1.$c2;

			$current_course = $sys_currency[$account['currency_current']];
			list($c3,$c4) = explode(",", $current_course);
			if (!empty($c4)) $c4 = ".".$c4;
			$current_course = $c3."".$c4;

			$account['course'] = round($course / $current_course,2);
			$data = "
				Курс <b>".$account['currency_current_name']."</b> к <b>".$account['currency_name']."</b>
				<input type='text' name='currency' id='currency_".$g_type."' value='".$account['course']."' OnKeyUp=onSumConvert('".$g_type."');>
				&nbsp;&nbsp;<span id='convertSumCurrency_".$g_type."'></span>
			";
		}

		echo $data;
		exit;
		break;
	case "deleteOperation":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
		{
			exit;
		}
		$id = html($g_id);
		$tr_id = html($g_tr_id);

		if ($tr_id == '0')
		{
			$money->deleteMoney($id);
		}else{
			$money->deleteOperationTransfer($tr_id);
		}
		break;
	case "editOperation":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		$id = html($g_id);
		$arr = $money->getMoney($id);
//pre($arr);
		$accounts = "";
		$categories_select = get_three_select($_SESSION['user_category'], 0, $arr['cat_id']);
		$count = count($_SESSION['user_account']);
		for($i=0; $i<$count; $i++)
		{
			$transfer = "";
			if ($_SESSION['user_account'][$i]['id'] == $arr['transfer']) $transfer = "selected";
			$accounts .= "<option value='".$_SESSION['user_account'][$i]['id']."' $transfer>".$_SESSION['user_account'][$i]['name']."</option>";
		}
		switch ($arr['drain'])
		{
			case '1':
				$drain = "
					<option value='0' selected>Расход</option>
					<option value='1'>Доход</option>
					<option value='2'>Перевод на счет</option>
				";
				//$arr['money'] = $arr['money'] * -1;
				break;
			case '0':
				$drain = "
					<option value='0'>Расход</option>
					<option value='1' selected>Доход</option>
					<option value='2'>Перевод на счет</option>
				";
				break;

		}
		if ($arr['cat_id'] == -1)
		{
			$drain = "
					<option value='0'>Расход</option>
					<option value='1'>Доход</option>
					<option value='2' selected>Перевод на счет</option>
				";
		}

		$data = "
			<a name='panelEditOperation'></a>
			<table width=100% style='background-color:#f8f8d8; padding-top:15px; padding-bottom:15px; border:1px dotted #ff6002;'>
			<tr>
				<td colspan='2' height='25'><span style='color:#3878d7; font-size:12px;'>Редактирование операции:</style></td>
			</tr>
			<tr>
				<td  align='right' class=cat_add width=10%>Тип операции:</td>
				<td  align='left' class='cat_add' width='90%'>
					<select name='type' id='type_edit' onChange=changeTypeOperation('edit');>
						$drain
					</select>
				</td>
			</tr>
			<tr>
				<td  align=right class=cat_add valign=top>
					Сумма:
				</td>
				<td  align=left class=cat_add>
					<input type=text id=pos_oc_edit value='".$arr['money']."' style=width:80px; OnKeyUp=onSumChangeEdit();onSumConvert('edit');>&nbsp;<b>Пример:</b> 125.50
					<input type=hidden name=money id=pos_mc_edit size=7>
				</td>
			</tr>
			<tr id='old_cat_edit'>
				<td align=right class=cat_add>Категория:</td>
				<td align=left class=cat_add>
					<select name=cat_id style='width:250px;' id=cat_id_old_edit>
						$categories_select
					</select>
					<span style='padding-left:10px;'><a href='index.php?modules=category' class=cat_add>Нет нужной категории? Добавьте ее в разделе [категории]</a></span>
				</td>
			</tr>
			<tr id='transferSelectEdit' style='display:none;'>
				<td align='right' class='cat_add'>На счет:</td>
				<td align='left' class='cat_add'>
					<select name='selectAccountForTransferEdit' id='selectAccountForTransferEdit' onChange=changeAccountForTransferEdit();>
						$accounts
					</select>
					<span id='operationTransferCurrencyEdit' style='padding-left:5px;'></span>
				</td>
			</tr>
			<tr>
				<td  align=right class=cat_add>
					Дата:
				</td>
				<td  align=left class=cat_add>
					<input type='text' value='".$arr['date']."' class='standart' style='width:80px;' name='dateFrom' id='sel1'>
					<button type='reset' class='button_calendar' onclick=\"return showCalendar('sel1', '%d.%m.%Y', false, true);\">
					<img src='img/calendar/cal.gif'></button>
				</td>
			</tr>
			<tr>
				<td  align=right class=cat_add valign=top>Комментарий:</td>
				<td  align=left class=cat_add>
					<textarea rows=4 cols=50 name=comment id=comment_edit>".$arr['comment']."</textarea>
				</td>
			</tr>
			<tr>
				<td  align=right class=cat_add valign=top>&nbsp;</td>
				<td  align=left class=cat_add>
					<input type='hidden' value='".$arr['id']."' id='m_id'>
					<input class=inputsubmit type=button value=Редактировать onclick=updateOperation();>
					<input class=inputsubmit type=button value=Отменить onclick=operationAddInVisible();>
				</td>
			</tr>
		</table><br><br>
		";
		echo $data;
		exit;
		break;
	case "getOperationList":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
		{
			exit;
		}

		switch ($currentType)
		{
			case 0:
				require_once SYS_DIR_MOD."operationTemplate/defaultOperationList.php";
				break;
			case 4:
				require_once SYS_DIR_MOD."operationTemplate/depositOperationList.php";
				break;
		}
		break;

	default:
		//if ($_SESSION['user']['user_login'] == 'demo')
		//{
			$often = $cat->getOftenCategories($_SESSION['user']['user_id']);
			$list = $cat->loadUserTree($_SESSION['user']['user_id']);
			$tpl->assign('often', $often);
			$tpl->assign('list', $list);
		//}
		switch ($area)
		{
			case "add":
				$tpl->assign('areaAdd', true);
				break;
			case "transfer":
				$tpl->assign('areaTransfer', true);
				break;
		}
	//pre( $money->getOperationList($currentAccount));
		break;
}
?>