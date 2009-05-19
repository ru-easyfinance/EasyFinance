<?
/**
* file: account.php
* author: Roman Korostov
* date: 7/03/07	
**/

if (empty($_SESSION['user']))
{
	header("Location: index.php");
	exit;
}

$tpl->assign('name_page', 'accounts');

$action = html($g_action);
if ($_SESSION['account'] == "reload")
{
	$user->initUserAccount($_SESSION['user']['user_id']);
	$user->save();
	$_SESSION['account'] = false;
}

require_once SYS_DIR_LIBS.'/money.class.php';
$money = new Money($db, $user);

switch( $action )
{
	case "test2":
		/*$aa = $acc->getTest();
		$cnt = count($aa);
		echo "<table width=100%>
			<tr>
				<td>&nbsp;</td>
				<td>login</td>
				<td>income</td>
				<td>outcome</td>
				<td>net_amount</td>
			</tr>
			";
		$filename = 'test.txt';
		for ($i=0; $i<$cnt;$i++)
		{
			/*if (is_writable($filename)) {

				if (!$handle = fopen($filename, 'a')) {
					echo "�� ���� ������� ���� ($filename)";
					exit;
				}
				$a = $i+1;
				$somecontent = $aa[$i]['user_login'].";".round($aa[$i]['income'],2).";".round($aa[$i]['outcome'],2).";".round($aa[$i]['net_amount'],2).";";
				if (fwrite($handle, $somecontent) === FALSE) {
					echo "�� ���� ���������� ������ � ���� ($filename)";
					exit;
				}
				fclose($handle);
			} else {
				echo "���� $filename ���������� ��� ������";
			}*/
			/*echo "
			<tr style='background-color: rgb(255, 255, 255);' onmouseover=this.style.backgroundColor='#f8f6ea'; onmouseout=this.style.backgroundColor='#FFFFFF';>
				<td class=cat_add>".$i."</td>
				<td class=cat_add>".$aa[$i]['user_login']."</td>
				<td class=cat_add>".round($aa[$i]['income'],2)."</td>
				<td class=cat_add>".round($aa[$i]['outcome'],2)."</td>
				<td class=cat_add>".round($aa[$i]['net_amount'],2)."</td>
			</tr>
			";*/
		//}
		//echo "</table>";
		break;
	case "getStepCreateAccount":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		
		switch ( $g_type )
		{
			case '0':
				if ($g_step == '2')
				{
					require_once SYS_DIR_MOD."accountTemplate/cashAccount.php";
				}
				break;
			case '4':
				if ( $g_step == '2' )
				{
					require_once SYS_DIR_MOD."accountTemplate/depositeAccount.php";
				}
				break;
			case '5':
				if ($g_step == '2')
				{
					require_once SYS_DIR_MOD."accountTemplate/metalAccount.php";
				}
				default:
					echo "
					<table style='border: 1px dotted rgb(255, 96, 2); background-color: rgb(248, 248, 216); padding: 10px; width:100%;'>
						<td class=cat_add>
						<h4>��� ���� �� ������ ��������?</h4>
						<select name='typeAccount' id='typeAccount' onChange='accountChangeType();'>
							<option value='0'>��������</option>
							<option value='1'>���������� �����</option>
							<option value='3'>����</option>
							<option value='4'>���������� ����</option>
							<option value='5'>������������� ����</option>
						</select><br><br>
						<span id='descriptionAccount'>����� ��� ��������� �����</span><br><br>
						<input type='button' value='�����' onClick='accountNextStep(2);'>
						<input type='button' value='������' onClick=formCreateAccountUnVisible();>
						</td></table>";
					exit;
				break;
		}
		break;
	case "saveAccount":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		
		switch ($g_type)
		{
			case '4':
				$name = iconv('utf-8', 'windows-1251', html($g_name));
				$bank = iconv('utf-8', 'windows-1251', html($g_bank));
				$sum = html($g_sum);
				$currency = html($g_currency);
				$percent = html($g_percent);
				$getpercent = html($g_getpercent);
				$dateCreated = html($g_dateCreated);
				list($d,$m,$y) = explode(".", $dateCreated);
				$dateCreated = $y."-".$m."-".$d;
				$type = 4;
				$from_account = html($g_from_account);
				$from_currency = html($g_from_currency);
				
				if ($from_currency != $currency)
				{					
					$from_sum = round($sum / $from_currency,2);		
				}
				
				$acc->saveAccountDeposite($type, $name, $bank, $sum, $currency, $percent, $getpercent, $dateCreated, $from_account, $from_currency, $from_sum);
				$money->addOperationDeposit($acc->current_account_id, $dateCreated, $from_sum, $from_account, $sum);
				break;
			//exit;
		}
		break;
	case "getCurrency":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		$id = html($g_id);
		$currencyId = html($g_currency);
		$count = count($_SESSION['user_account']);
		$data = "";
			
		for ($i=0; $i<$count; $i++)
		{
			if ($_SESSION['user_account'][$i]['id'] == $id)
			{
				$account['currency'] = $_SESSION['user_account'][$i]['currency'];
				$account['currency_name'] = $_SESSION['user_account'][$i]['currency_name'];
			}			
		}
		
		if ($g_type == 'edit')
		{
			$g_type = 'edit';
			$data = "<input type='hidden' name='currency_from' id='currency_from_edit' value='1'>";
		}else{
			$g_type = 'add';
			$data = "<input type='hidden' name='currency_from' id='currency_from_add' value='1'>";
		}
			
		if ($currencyId != $account['currency'])
		{
			$count = count($sys_currency);
			$course = $sys_currency[$account['currency']];
			list($c1,$c2) = explode(",", $course);
			if (!empty($c2)) $c2 = ".".$c2;
			$course =$c1.$c2;
			
			$current_course = $sys_currency[$currencyId];
			list($c3,$c4) = explode(",", $current_course);
			if (!empty($c4)) $c4 = ".".$c4;
			$current_course = $c3."".$c4;			
			
			$account['course'] = round($course / $current_course,2);
			$data = "
				���� <b>".$sys_currency_name[$currencyId]."</b> � <b>".$account['currency_name']."</b> 
				<input type='text' name='currency' id='currency_".$g_type."' value='".$account['course']."' OnKeyUp=onSumConvert('".$g_type."'); style='width:50px;'>
				&nbsp;&nbsp;<span id='convertSumCurrency_".$g_type."'></span>
			";			
		}else{
			$data = "<input type='text' name='currency' id='currency_".$g_type."' value='".$account['course']."' OnKeyUp=onSumConvert('".$g_type."'); style='width:50px; display:none;'>";
		}
		
		echo $data;
		exit;
		break;
	case "getAccountList":
		if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		
		$accounts = $acc->getAccountsList();
		$cnt = count($accounts);
		$block = -1;
		$data = "<table border='0' cellpadding='0' cellspacing='0' width='100%' >";
		
		for ($i=0; $i<$cnt; $i++)
		{
			if ($accounts[$i]['bill_type'] == 0)
			{
				if ($block != 0)
				{
					$data .= "
						<tr>
							<td colspan='3' class='cForm'  style='border-bottom:1px solid #cccccc;'>
								<span id='title_cForm'>��������</span><br>
							</td>
						</tr>
						<tr>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=20%><b>�������� �����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=15% align=right><b>�����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=65%>&nbsp;</td>
						</tr>
						<!--<tr>
							<td class='cat_add' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>�������� �����</b></td>
							<td class='cat_add' align='right' style='padding-right: 15px; border-bottom:1px solid #cccccc;'><b>�����</b></td>						
						</tr>-->
						";
					$block = 0;
				}
				$data .= "
					<tr style='background-color: rgb(255, 255, 255);' onmouseover=this.style.backgroundColor='#f8f8d8'; onmouseout=this.style.backgroundColor='#FFFFFF';>
						<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f8d8;' width=20%>
							<a href='#' onclick=".$onclick."><img src='img/delete.gif' border=0></a>&nbsp;
							<a href='index.php?modules=operation&a=".$accounts[$i]['bill_id']."'>".$accounts[$i]['bill_name']."</a></td>
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' align=right>".get_number_format($acc->getTotalSumAccount($accounts[$i]['bill_id']))." ".$sys_currency_name[$accounts[$i]['bill_currency']]."</td>						
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;'>&nbsp;</td>
					</tr>
				";
			}
			if ($accounts[$i]['bill_type'] == 1)
			{
				if ($block != 1)
				{
					$data .= "
					</table><br>
					<table border='0' cellpadding='5' cellspacing='0' width='100%'>
						<tr>
							<td colspan='3' class='cForm' style='border-bottom:1px solid #cccccc;'>
								<span id='title_cForm'>���������� �����</span><br>
							</td>
						</tr>
						<tr>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=20%><b>�������� �����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=15% align=right><b>�����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=65%>&nbsp;</td>
						</tr>
						<!--<tr>
							<td class='cat_add' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Iacaaiea n?aoa</b></td>
							<td class='cat_add' align='right' style='padding-right: 15px; border-bottom:1px solid #cccccc;'><b>Noiia</b></td>						
						</tr>-->
						";
					$block = 1;
				}
				$data .= "
					<tr style='background-color: rgb(255, 255, 255);' onmouseover=this.style.backgroundColor='#f8f6ea'; onmouseout=this.style.backgroundColor='#FFFFFF';>
						<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f6ea;'>
							<a href='#' onclick=".$onclick."><img src='img/delete.gif' border=0></a>&nbsp;
							<a href='index.php?modules=operation&a=".$accounts[$i]['bill_id']."'>".$accounts[$i]['bill_name']."</a>
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' align=right>".get_number_format($acc->getTotalSumAccount($accounts[$i]['bill_id']))." ".$sys_currency_name[$accounts[$i]['bill_currency']]."</td>						
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;'>&nbsp;</td>
					</tr>
				";
			}
			if ($accounts[$i]['bill_type'] == 3)
			{
				if ($block != 3)
				{
					$data .= "
					</table><br>
					<table border='0' cellpadding='5' cellspacing='0' width='100%'>
						<tr>
							<td colspan='3' class='cForm' style='border-bottom:1px solid #cccccc;'>
								<span id='title_cForm'>�������� �����</span><br>
							</td>
						</tr>
						<tr>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=20%><b>�������� �����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=15% align=right><b>�����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' width=65%>&nbsp;</td>
						</tr>
						<!--<tr>
							<td class='cat_add' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Iacaaiea n?aoa</b></td>
							<td class='cat_add' align='right' style='padding-right: 15px; border-bottom:1px solid #cccccc;'><b>Noiia</b></td>						
						</tr>-->
						";
					$block = 3;
				}
				$data .= "
					<tr style='background-color: rgb(255, 255, 255);' onmouseover=this.style.backgroundColor='#f8f6ea'; onmouseout=this.style.backgroundColor='#FFFFFF';>
						<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f6ea;'>
							<a href='#' onclick=".$onclick."><img src='img/delete.gif' border=0></a>&nbsp;
							<a href='index.php?modules=operation&a=".$accounts[$i]['bill_id']."'>".$accounts[$i]['bill_name']."</a>
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' align=right>".get_number_format($acc->getTotalSumAccount($accounts[$i]['bill_id']))." ".$sys_currency_name[$accounts[$i]['bill_currency']]."</td>						
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;'>&nbsp;</td>
					</tr>
				";
			}
			if ($accounts[$i]['bill_type'] == 4)
			{
				if ($block != 4)
				{
					$data .= "
					</table><br>
					<table border='0' cellpadding='5' cellspacing='0' width='100%'>
						<tr>
							<td colspan='5' class='cForm' style='border-bottom:1px solid #cccccc;'>
								<span id='title_cForm'>���������� �����</span><br>
							</td>
						</tr>
						<tr>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>�������� �����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' align=right><b>�����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>����</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>% ������</b></td>
							<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>���� ��������</b></td>
						</tr>
						<!--<tr>
							<td class='cat_add' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Iacaaiea n?aoa</b></td>
							<td class='cat_add' align='right' style='padding-right: 15px; border-bottom:1px solid #cccccc;'><b>Noiia</b></td>						
						</tr>-->
						";
					$block = 4;
				}
				$data .= "
					<tr style='background-color: rgb(255, 255, 255);' onmouseover=this.style.backgroundColor='#f8f6ea'; onmouseout=this.style.backgroundColor='#FFFFFF';>
						<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f6ea;' width=20%>
						<a href='#' onclick=".$onclick."><img src='img/delete.gif' border=0></a>&nbsp;
						<a href='index.php?modules=operation&a=".$accounts[$i]['bill_id']."'>".$accounts[$i]['bill_name']."</a>
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' width=15% align=right>
						".get_number_format($acc->getTotalSumAccount($accounts[$i]['bill_id']))." ".$sys_currency_name[$accounts[$i]['bill_currency']]."</td>
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' width=20%>".$accounts[$i]['name_bank']."</td>
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' width=20% align=center>".$accounts[$i]['percent']."%</td>
						<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' width=25%>".$accounts[$i]['open_date']."</td>
					</tr>
				";
			}
		}
		
		$data .= "</table>";
		//pre($accounts);
		echo $data;
		exit;
		break;
	case "getTotalSumm":
	{
		//if($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') exit;
		
		$accounts = $_SESSION['user_account'];
		$cnt = count($accounts);
		$total = 0;
		
		for ($i=0; $i<$cnt; $i++)
		{
			$sum = $accounts[$i]['sum'];
			
			if ($accounts[$i]['currency'] != 1)
			{
				$sum = $sys_currency[$accounts[$i]['currency']] * $accounts[$i]['sum'];				
			}
			echo $sum."<br>";
			$total += $sum;
		}
		echo "=".$total;
		//exit;
		break;
	}
		
		
		
		
		
		
		
		
	case "add":
		$tpl->assign("page_title","account add");	
		$tpl->assign('currency', $_SESSION['user_currency']);		
		//$tpl->assign('accounts', $_SESSION['user_account']);

		if (!empty($p_acc))
		{
			$account['type'] = $p_acc['type'];
			$account['currency'] = $p_acc['currency'];
			
			if (!empty($p_acc['name']))
			{
				$account['name'] = html($p_acc['name']);
			}
			else
			{
				$error_text['name'] = "Iacaaiea n?aoa ia aie?ii auou ionoui!";
			}
			
			if (isset($p_acc['money']))
			{
				if (preg_match('/^[0-9.-]+$/', $p_acc['money']))
				{
					if ($account['type'] == 3 && $p_acc['money'] == 0)
					{
						$error_text['money'] = "A oeia 'aiea', ia?aeuiue eaieoae ia aie?ai auou ioeaaui!";
					}else{					
						$account['money'] = $p_acc['money'];
					}
				}
				else
				{
					$error_text['money'] = "Iaaa?iua aaiiua!";
				}								
			}
			
			$account['user_id'] = $user->getId();
			
			if (empty($error_text))
			{			
				if($acc->saveAccount($account['type'], $account['name'], $account['money'], $account['currency']))
				{
					//$tpl->assign('good_text', "N?ao aiaaaeai!");
					$_SESSION['good_text'] = "N?ao aiaaaeai!";
					header("Location: index.php?modules=account");
				}
			}
			else
			{
				$tpl->assign('error_text', $error_text);
				$tpl->assign('account', $account);
			}			
		}
		
		break;
	case "edit":	
		$tpl->assign("page_title","account edit");
		$tpl->assign('currency', $_SESSION['user_currency']);

		if (!empty($p_acc))
		{
			//pre($p_acc);
			$account['type'] = $p_acc['type'];
			$account['currency'] = $p_acc['currency'];
			
			if (!empty($p_acc['name']))
			{
				$account['name'] = html($p_acc['name']);
			}
			else
			{
				$error_text['name'] = "Iacaaiea n?aoa ia aie?ii auou ionoui!";
			}
			
			if (isset($p_acc['money']))
			{
				if (preg_match('/^[0-9.-]+$/', $p_acc['money']))
				{
					if ($account['type'] == 3 && $p_acc['money'] == 0)
					{
						$error_text['money'] = "A oeia 'aiea', ia?aeuiue eaieoae ia aie?ai auou ioeaaui!";
					}else{					
						$account['money'] = $p_acc['money'];
					}
				}
				else
				{
					$error_text['money'] = "Iaaa?iua aaiiua!";
				}								
			}			

			if (empty($error_text))
			{
				if($acc->updateAccount($p_acc['id'],$account['type'], $account['name'], $account['money'], $account['currency']))
				{
					$tpl->assign('good_text', "N?ao eciaiai!");
					$_SESSION['good_text'] = "N?ao eciaiai!";
					header("Location: index.php?modules=account");
				}
			}
			else
			{
				$tpl->assign('error_text', $error_text);				
			}	
		}
		else
		{		
			if (isset($g_id) && is_numeric($g_id))
			{
				$account = $acc->selectAccount(html($g_id));
				if(count($account)>0)
				{
					$tpl->assign('account', $account[0]);
				}
				else
				{
					$error_text['account'] = "Oaeiai n?aoa ia nouanoaoao!";
					$tpl->assign('error_text', $error_text);
				}
			}				
		}
		
		break;
	case "del":	
		$tpl->assign("page_title","account del");

		if (isset($p_acc['id']) && is_numeric($p_acc['id']))
		{			
			if($acc->deleteAccount(html($p_acc['id'])))
			{
				$_SESSION['good_text'] = "N?ao oaaeai!";
				header("Location: index.php?modules=account");
				exit;
			}	
		}
		else
		{
			message_error(GENERAL_ERROR, "Iieo?ai iaaa?iue ia?aiao?!");
		}
		
		break;
	case "transfer":	
		$tpl->assign("page_title","account transfer");
		$tpl->assign('date_today', date("d.m.Y"));
		if (isset($g_id) && is_numeric($g_id))
		{
			//pre($p_transfer);
			$account = $acc->selectAccount(html($g_id));
			if(count($account)>0)
			{
				$tpl->assign('account', $account[0]);
			}
			else
			{
				$error_text['account'] = "Oaeiai n?aoa ia nouanoaoao!";
				$tpl->assign('error_text', $error_text);
			}
			
			if(!empty($g_tr))
			{
				$transfer = $acc->selectTransfer(html($g_tr));
				$transfer['money'] = substr($transfer['money'], 1);
				if ($transfer['convert'] != $transfer['money'])
				{
					$transfer['convert'] = round($transfer['money'] / $transfer['convert'],2);
				}else{
					$transfer['convert'] = "";
				}
				$tpl->assign("page_title","edit transfer");
			}
			
			if(!empty($p_transfer) && empty($p_transfer['tr_d']))
			{
				if(!empty($p_transfer['to_account']))
				{
					$transfer['to_account'] = $p_transfer['to_account'];
				}else{
					$error_text['to_account'] = "Ia?a?eneaiea ia n?ao, ia aie?ii auou ionoui!";
				}
				
				$transfer['currency'] = $p_transfer['currency'];
				
				if (!empty($p_transfer['date']))
				{
					list($day,$month,$year) = explode(".", $p_transfer['date']);					
					
					if (is_numeric($day) && $day >0 && $day <= 31)
					{
						if (is_numeric($month) && $month >0 && $month < 13)
						{
							if (is_numeric($year) && $year >2000 && $year < 2099)
							{
								$transfer['date'] = $year.".".$month.".".$day;
							}else{
								$error_text['date'] = "Iaaa?iay aaoa!";
							}
						}else{
							$error_text['date'] = "Iaaa?iay aaoa!";
						}
					}else{
						$error_text['date'] = "Iaaa?iay aaoa!";
					}
				}
				
				if (!empty($p_transfer['comment']))
				{
					$transfer['comment'] = htmlspecialchars($p_transfer['comment']);
				}
				
				if (isset($p_transfer['money']))
				{
					if (preg_match('/^[0-9.]+$/', $p_transfer['money']))
					{
						$transfer['money'] = $p_transfer['money'];
						$transfer['convert'] = 	$transfer['money'];				
					}
					else
					{
						$error_text['money'] = "Iaaa?iua aaiiua!";
					}								
				}else{
					$error_text['money'] = "Aaaaeoa noiio!";
				}
				
				if (!empty($p_transfer['kat']))
				{
					if (preg_match('/^[0-9.]+$/', $p_transfer['kat']))
					{
						$transfer['kat'] = $p_transfer['kat'];
						if($transfer['currency'] == 1)
						{
							$transfer['convert'] = $transfer['money'] / $transfer['kat'];
							$transfer['convert'] = round($transfer['convert'],2);
						}else{
							$transfer['convert'] = $transfer['money'] * $transfer['kat'];
							$transfer['convert'] = round($transfer['convert'], 2);
						}
					}
					else
					{
						$error_text['kat'] = "Iaaa?iua aaiiua!";
					}								
				}
				
				$transfer['from_account'] = $p_transfer['from_account'];

				if (empty($error_text))
				{				
					if (isset($p_transfer['tr_id']) && $p_transfer['tr_id'] != "")
					{
						if($acc->updateMoney($transfer['money'], $transfer['convert'], $transfer['date'], $transfer['from_account'], $transfer['to_account'], html($p_transfer['tr_id']), $transfer['comment']))
						{
							//$tpl->assign('good_text', "Ia?a?eneaiea eciaiaii!");
							$_SESSION['good_text'] = "Ia?a?eneaiea eciaiaii!";
							header("Location: index.php?modules=money&a=".$transfer['from_account']);
						}					
					}else{
						if($acc->saveMoney($transfer['money'], $transfer['convert'], $transfer['date'], $transfer['from_account'], $transfer['to_account'], $transfer['comment']))
						{
							//$tpl->assign('good_text', "Oeiainu ia?a?eneaiu!");
							$_SESSION['good_text'] = "Oeiainu ia?a?eneaiu!";
							header("Location: index.php?modules=money&a=".$transfer['from_account']);
						}
					}
				}
				else
				{
					$transfer['date'] = $day.".".$month.".".$year;
					$tpl->assign('error_text', $error_text);				
				}
			} // end if(!empty($p_transfer))
			
			if(!empty($p_transfer) && !empty($p_transfer['tr_d']))
			{
				if($acc->deleteMoney(html($p_transfer['tr_d'])))
				{
					header("Location: index.php?modules=account");
					exit;
				}
			}
		}
		
		$tpl->assign('transfer', $transfer);
		
		$tpl->assign('account', $account);
		$tpl->assign('accounts', $_SESSION['user_account']);
		
		break;
	default:
		$tpl->assign("page_title","account all");				
		$tpl->assign('account', $_SESSION['user_account']);
		for($i=0; $i<count($_SESSION['user_account']); $i++)
		{
			if ($_SESSION['user_account'][$i]['currency'] == '1')
			{
				$total['1'] = $total['1'] + $_SESSION['user_account'][$i]['sum'];
			}
			if ($_SESSION['user_account'][$i]['currency'] == '2')
			{
				$total['2'] = $total['2'] + $_SESSION['user_account'][$i]['sum'];
			}
			if ($_SESSION['user_account'][$i]['currency'] == '3')
			{
				$total['3'] = $total['3'] + $_SESSION['user_account'][$i]['sum'];
			}
			if ($_SESSION['user_account'][$i]['currency'] == '4')
			{
				$total['4'] = $total['4'] + $_SESSION['user_account'][$i]['sum'];
			}
		}
		if (!empty($total['1']))
		{
			$total['1'] = $total['1'];
			$total['rub'] = "&nbsp;?oa.";
		}
		if (!empty($total['2']))
		{
			$total['2'] = $total['2'];
			$total['dol'] = "&nbsp;$&nbsp;";
		}
		if (!empty($total['3']))
		{
			$total['3'] = $total['3'];
			$total['evro'] = "&nbsp;?&nbsp;";
		}
		if (!empty($total['4']))
		{
			$total['4'] = $total['4'];
			$total['grvn'] = "&nbsp;a?ai.&nbsp;";
		}
		$tpl->assign('total_all_sum', $total);
		
		if ($_SESSION['good_text'])
		{
			$tpl->assign('good_text', $_SESSION['good_text']);
			$_SESSION['good_text'] = false;
		}
		
		//pre($total);
		
		break;
}
?>