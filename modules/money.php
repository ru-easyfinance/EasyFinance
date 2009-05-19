<?
/**
* file: money.php
* author: Roman Korostov
* date: 14/03/07	
**/

if (empty($_SESSION['user']))
{
	header("Location: index.php");
}

if ($_GET['et']!='money')
{
	$tpl->assign('name_page', 'money');
}else{
$tpl->assign('name_page', 'money3');
}

require_once SYS_DIR_LIBS.'/money.class.php';

$action = html($g_action);

$money = new Money($db, $user);

$tpl->assign('accounts', $_SESSION['user_account']);

switch( $action )
{
	case "add":
		$tpl->assign("page_title","money add");			
		$tpl->assign('categories', $_SESSION['user_category']);
		//if (!empty($_GET['id']))
		//{
			if (isset($g_id))
			{
				$account = $acc->selectAccount(html($g_id));
				
				if(count($account)>0)
				{
					//$tpl->assign('account', $account[0]);					
					$active_account['id'] = $g_id;
					$active_account['name'] = $account[$g_id]['name'];
				}
				else
				{				
					$error_text['account'] = "Такого счета не существует!";
					$tpl->assign('error_text', $error_text);
					//exit;
				}
			}
		//}else{
		//	$active_account = $money->getActiveAccount();
		//}

		$tpl->assign('account', $active_account);		
		$order['date'] = $money->current_date;
		$tpl->assign('money', $order);
		$categories_select = get_three_select($_SESSION['user_category']);
		$tpl->assign('categories_select', $categories_select, 0, 0);		

		if (!empty($p_money))
		{
		//pre($p_money);			
			
			if(isset($p_money['cat_type']))
			{
				$order['cat_type'] = $p_money['cat_type'];
				if ($order['cat_type'] == 1)
				{
					if (!empty($p_money['cat_name']))
					{
						$order['cat_name'] = html($p_money['cat_name']);
					}
					else
					{
						$error_text['cat_name'] = "Название категории не должно быть пустым!";
					}
				}
			}
			
			if(isset($p_money['cat_id']) && $p_money['cat_id'] != '')
			{
				$order['cat_id'] = $p_money['cat_id'];
			}else{
				$error_text['cat_id'] = "Выберите категорию!";
			}
			
			if (!empty($p_money['date']))
			{
				list($day,$month,$year) = explode(".", $p_money['date']);
				
				if (is_numeric($day) && $day >0 && $day <= 31)
				{
					if (is_numeric($month) && $month >0 && $month < 13)
					{
						if (is_numeric($year) && $year >2000 && $year < 2099)
						{
							$order['date'] = $year.".".$month.".".$day;
						}else{
							$error_text['date'] = "Неверная дата!";
						}
					}else{
						$error_text['date'] = "Неверная дата!";
					}
				}else{
					$error_text['date'] = "Неверная дата!";
				}
				
				//$order['date'] = html($p_money['date']);
			}
			else
			{				
				$error_text['date'] = "Дата не должна быть пустой!";
			}
			
			if(isset($p_money['drain']))
			{
				$order['drain'] = $p_money['drain'];
			}
			
			//$pos = strpos($money, "-");
			
			if (isset($p_money['money']))
			{

				if (preg_match('/^[0-9.]+$/', $p_money['money']))
				{
					if ($order['drain'] == 1 && $p_money['money'] != 0)
					{		
						$order['money'] = "-".$p_money['money'];
					}else{
						$order['money'] = $p_money['money'];
					}
				}
				else
				{
					$error_text['money'] = "Неверные данные!";
				}								
			}		
			
			if(isset($p_money['comment']) && $p_money['comment'] != "")
			{
				$order['comment'] = htmlspecialchars($p_money['comment']);
			}
			
			if (empty($error_text))
			{
				if($money->saveMoney($order['cat_type'], $order['cat_name'], $order['cat_id'], $order['money'], $order['date'], $order['drain'], $order['comment'], $p_money['bill_id']))
				{
					$_SESSION['good_text'] = "Запись сохранена!";
					header("Location: index.php?modules=money&a=".$p_money['bill_id']);					
				}
			}
			else
			{
				$order['date'] = $day.".".$month.".".$year;
				$tpl->assign('error_text', $error_text);
				$tpl->assign('money', $order);				
			}
			//pre($order);
		}
		
		break;
				
	case "edit":
		if (isset($g_restore) && is_numeric($g_restore))
		{
			if (isset($g_id) && is_numeric($g_id))
			{
				if ($user->restoreCategory(html($g_restore)))
				{
					header("Location: index.php?modules=money&action=edit&id=".html($g_id)."");
				}
			}else
			{
				message_error(GENERAL_ERROR, "Получен неверный параметр!");
			}
		}
		
		$tpl->assign("page_title","money edit");
		$tpl->assign('categories', $_SESSION['user_category']);
		$active_account = $money->getActiveAccount();
		$tpl->assign('account', $active_account);
		$order['date'] = $money->current_date;
		
		if (!empty($p_money))
		{
			if(isset($p_money['cat_type']))
			{
				$order['cat_type'] = $p_money['cat_type'];
				if ($order['cat_type'] == 1)
				{
					if (!empty($p_money['cat_name']))
					{
						$order['cat_name'] = html($p_money['cat_name']);
					}
					else
					{
						$error_text['cat_name'] = "Название категории не должно быть пустым!";
					}
				}
			}
			
			if(isset($p_money['cat_id']) && $p_money['cat_id'] != '')
			{
				$order['cat_id'] = $p_money['cat_id'];
			}else{
				$error_text['cat_id'] = "Выберите категорию!";
			}
			
			if (!empty($p_money['date']))
			{
				list($day,$month,$year) = explode(".", $p_money['date']);
				if (is_numeric($day) && $day >0 && $day <= 31)
				{
					if (is_numeric($month) && $month >0 && $month < 13)
					{
						if (is_numeric($year) && $year >2000 && $year < 2099)
						{
							$order['date'] = $year.".".$month.".".$day;
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
			else
			{				
				$error_text['date'] = "Дата не должна быть пустой!";
			}
			
			if(isset($p_money['drain']))
			{
				$order['drain'] = $p_money['drain'];
			}
			
			//$pos = strpos($money, "-");
			
			if (isset($p_money['money']))
			{
				if (preg_match('/^[0-9.]+$/', $p_money['money']))
				{
					if ($order['drain'] == 1 && $p_money['money'] != 0 && empty($error_text['cat_id']))
					{		
						$order['money'] = "-".$p_money['money'];
					}else{
						$order['money'] = $p_money['money'];
					}
				}
				else
				{
					$error_text['money'] = "Неверные данные!";
				}								
			}		
			
			if(isset($p_money['comment']) && $p_money['comment'] != "")
			{
				$order['comment'] = htmlspecialchars($p_money['comment']);
			}
			
			if (empty($error_text))
			{
				if($money->updateMoney($p_money['id'], $order['cat_type'], $order['cat_name'], $order['cat_id'], $order['money'], $order['date'], $order['drain'], $order['comment'], $p_money['bill_id']))
				{
					$_SESSION['good_text'] = "Запись изменена!";
					header("Location: index.php?modules=money&a=".$p_money['bill_id']);
				}
			}
			else
			{	
				$order['date'] = $day.".".$month.".".$year;
				$tpl->assign('money', $order);
				$categories_select = get_three_select($_SESSION['user_category'], 0, $order['cat_id']);
				$pos = strpos($categories_select, "selected = 'selected'");
				
				if ($pos === false)
				{
					$categories_select .= "														
									<option value='' selected>Категоря не найдена</option>"; 
					$error_text['cat_id'] = "Выберите категорию!";
					$tpl->assign('restore_cat', $order['cat_id']);
				}
	
				$tpl->assign('categories_select', $categories_select);
				$tpl->assign('error_text', $error_text);	
			}	
		}
		else
		{		
			if (isset($g_id) && is_numeric($g_id))
			{
				$order = $money->getMoney(html($g_id));
			}
			
			$tpl->assign('money', $order);	
			$categories_select = get_three_select($_SESSION['user_category'], 0, $order['cat_id']);
			$pos = strpos($categories_select, "selected = 'selected'");
			
			if ($pos === false)
			{
				$categories_select .= "														
								<option value='' selected>Категоря не найдена</option>";
				$error_text['cat_id'] = "Выберите категорию!";
				$tpl->assign('restore_cat', $order['cat_id']);
			}

			$tpl->assign('categories_select', $categories_select);
			$tpl->assign('error_text', $error_text);
		}
		
		break;
	case "del":	
		$tpl->assign("page_title","money del");

		if (isset($p_money['id']) && is_numeric($p_money['id']))
		{
			if($money->deleteMoney(html($p_money['id'])))
			{
				$_SESSION['good_text'] = "Запись удалена!";
				header("Location: index.php?modules=money&a=".$p_money['bill_id']);
				exit;
			}	
		}
		else
		{
			message_error(GENERAL_ERROR, "Получен неверный параметр!");
		}
		
		break;	
	case "getTotalSumm":
		$count = count($_SESSION['user_account']);
		for($i=0; $i<$count; $i++)
		{
			if ($_SESSION['user_account'][$i]['id'] == html($_GET['id']))
			{
				if ($_SESSION['user_account'][$i]['sum'] > 0)
				{
					$sum = "<font color=green>".$_SESSION['user_account'][$i]['sum']."</font> ".$_SESSION['user_account'][$i]['currency_name'];
				}else{
					$sum = "<font color=#bc5f5f>".$_SESSION['user_account'][$i]['sum']."</font> ".$_SESSION['user_account'][$i]['currency_name'];
				}
				
				if ($_SESSION['user_account'][$i]['currency'] != 1)
				{
					$currency = "<div style='margin-left:148px;'>Итого в рублях: ".$_SESSION['user_account'][$i]['sum'] * $sys_currency[$_SESSION['user_account'][$i]['currency']]." руб.</div>";
				}
				$total_summ = $sum."&nbsp;&nbsp;&nbsp;&nbsp;".$currency;
			}
		}
		
		echo "Остаток на счете: ".$total_summ;
		exit;
		break;
	default:
		$order = html($g_order);

		if ($order == "all")
		{
			$tpl->assign('begin', true);
		}
		
		if ($_SESSION['user_money'] == "reload" || !empty($order))
		{
			$money->selectMoney($g_a);			
		}		

		if ($_SESSION['user_money'][0]['bill_id'] != $g_a && is_numeric($g_a))
		{
			$money->selectMoney($g_a);
			
			$tpl->assign('account', $money->getActiveAccount());
			$tpl->assign('money', $_SESSION['user_money']);
			$tpl->assign('total', $_SESSION['total_sum']);		
		}
		else
		{
			$tpl->assign('account', $money->getActiveAccount());
			$tpl->assign('money', $_SESSION['user_money']);
			$tpl->assign('total',  $_SESSION['total_sum']);
		}
	
		if ($_SESSION['good_text'])
		{
			$tpl->assign('good_text', $_SESSION['good_text']);
			$_SESSION['good_text'] = false;
		}
			
		break;
}


?>