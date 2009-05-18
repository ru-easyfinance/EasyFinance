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

$tpl->assign('name_page', 'account');

$action = html($g_action);

if ($_SESSION['account'] == "reload")
{
	$user->initUserAccount($_SESSION['user']['user_id']);
	$user->save();
	$_SESSION['account'] = false;
}

switch( $action )
{
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
				$error_text['name'] = "Название счета не должно быть пустым!";
			}
			
			if (isset($p_acc['money']))
			{
				if (preg_match('/^[0-9.-]+$/', $p_acc['money']))
				{
					if ($account['type'] == 3 && $p_acc['money'] == 0)
					{
						$error_text['money'] = "В типе 'долг', начальный капитал не должен быть нулевым!";
					}else{					
						$account['money'] = $p_acc['money'];
					}
				}
				else
				{
					$error_text['money'] = "Неверные данные!";
				}								
			}
			
			$account['user_id'] = $user->getId();
			
			if (empty($error_text))
			{			
				if($acc->saveAccount($account['type'], $account['name'], $account['money'], $account['currency']))
				{
					//$tpl->assign('good_text', "Счет добавлен!");
					$_SESSION['good_text'] = "Счет добавлен!";
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
				$error_text['name'] = "Название счета не должно быть пустым!";
			}
			
			if (isset($p_acc['money']))
			{
				if (preg_match('/^[0-9.-]+$/', $p_acc['money']))
				{
					if ($account['type'] == 3 && $p_acc['money'] == 0)
					{
						$error_text['money'] = "В типе 'долг', начальный капитал не должен быть нулевым!";
					}else{					
						$account['money'] = $p_acc['money'];
					}
				}
				else
				{
					$error_text['money'] = "Неверные данные!";
				}								
			}			

			if (empty($error_text))
			{
				if($acc->updateAccount($p_acc['id'],$account['type'], $account['name'], $account['money'], $account['currency']))
				{
					$tpl->assign('good_text', "Счет изменен!");
					$_SESSION['good_text'] = "Счет изменен!";
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
				$account = $acc->selectAccountForEdit(html($g_id));
				
				if(count($account)>0)
				{					
					$tpl->assign('account', $account[0]);
				}
				else
				{
					$error_text['account'] = "Такого счета не существует!";
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
				$_SESSION['good_text'] = "Счет удален!";
				header("Location: index.php?modules=account");
				exit;
			}	
		}
		else
		{
			message_error(GENERAL_ERROR, "Получен неверный параметр!");
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
				$error_text['account'] = "Такого счета не существует!";
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
					$error_text['to_account'] = "Перечисление на счет, не должно быть пустым!";
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
								$error_text['date'] = "Неверная дата!";
							}
						}else{
							$error_text['date'] = "Неверная дата!";
						}
					}else{
						$error_text['date'] = "Неверная дата!";
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
						$error_text['money'] = "Неверные данные!";
					}								
				}else{
					$error_text['money'] = "Введите сумму!";
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
						$error_text['kat'] = "Неверные данные!";
					}								
				}
				
				$transfer['from_account'] = $p_transfer['from_account'];

				if (empty($error_text))
				{				
					if (isset($p_transfer['tr_id']) && $p_transfer['tr_id'] != "")
					{
						if($acc->updateMoney($transfer['money'], $transfer['convert'], $transfer['date'], $transfer['from_account'], $transfer['to_account'], html($p_transfer['tr_id']), $transfer['comment']))
						{
							//$tpl->assign('good_text', "Перечисление изменено!");
							$_SESSION['good_text'] = "Перечисление изменено!";
							header("Location: index.php?modules=money&a=".$transfer['from_account']);
						}					
					}else{
						if($acc->saveMoney($transfer['money'], $transfer['convert'], $transfer['date'], $transfer['from_account'], $transfer['to_account'], $transfer['comment']))
						{
							//$tpl->assign('good_text', "Финансы перечислены!");
							$_SESSION['good_text'] = "Финансы перечислены!";
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
	
		$accounts = $_SESSION['user_account'];
		$cnt = count($accounts);
		for ($i=0; $i<$cnt; $i++)
		{
			$accounts[$i]['sum'] = $acc->getTotalSum($accounts[$i]['id']);
			$accounts[$i]['total_account_sum'] = number_format($acc->getTotalSum($accounts[$i]['id']), 2, '.', ' ');
		}
	
		$tpl->assign("page_title","account all");				
		
		for($i=0; $i<$cnt; $i++)
		{
			if ($accounts[$i]['currency'] == '1')
			{
				$total['1'] = $total['1'] + $accounts[$i]['sum'];
			}
			if ($accounts[$i]['currency'] == '2')
			{
				$total['2'] = $total['2'] + $accounts[$i]['sum'];
			}
			if ($accounts[$i]['currency'] == '3')
			{
				$total['3'] = $total['3'] + $accounts[$i]['sum'];
			}
			if ($accounts[$i]['currency'] == '4')
			{
				$total['4'] = $total['4'] + $accounts[$i]['sum'];
			}
		}
		
		$tpl->assign('account', $accounts);

		if (!empty($total['1']))
		{
			$total['1'] = $total['1'];
			$total['rub'] = "&nbsp;руб.";
		}
		if (!empty($total['2']))
		{
			$total['2'] = $total['2'];
			$total['dol'] = "&nbsp;$&nbsp;";
		}
		if (!empty($total['3']))
		{
			$total['3'] = $total['3'];
			$total['evro'] = "&nbsp;€&nbsp;";
		}
		if (!empty($total['4']))
		{
			$total['4'] = $total['4'];
			$total['grvn'] = "&nbsp;грвн.&nbsp;";
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