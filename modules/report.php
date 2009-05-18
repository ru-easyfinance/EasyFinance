<?
/**
* file: report.php
* author: Roman Korostov
* date: 26/03/07	
**/

if (empty($_SESSION['user']))
{
	header("Location: index.php");
}

$tpl->assign('name_page', 'report');

require_once SYS_DIR_LIBS.'/report.class.php';

$action = html($g_action);

$report = new Report($db, $user);

$categories_select = get_three_select2($_SESSION['user_category'], 0, $p_report['cat_id']);
$tpl->assign('categories_select', $categories_select);

$tpl->assign('bills', $_SESSION['user_account']);

$_SESSION['print_report'] = false;

switch( $action )
{
	case "cat":
		if ((isset($g_drain) || isset($p_report['drain'])) && (
			 is_numeric(html($g_drain)) || is_numeric(html($p_report['drain']))))
		{
			if ($g_drain != 0)
			{
				$g_drain = 1;
			}
			if (!empty($p_report))
			{
				$rep_filter['drain'] = $p_report['drain'];
				$filter .= "AND (m.`drain` = '".$p_report['drain']."')";
				$total_date .= "AND (m.`drain` = '".$p_report['drain']."')";
			}else{
				$rep_filter['drain'] = $g_report['drain'];
				$filter .= "AND (m.`drain` = '".$g_drain['drain']."')";
				$total_date .= "AND (m.`drain` = '".$g_drain['drain']."')";
			}
			
			if (!empty($p_report['date_from']) || !empty($p_report['date_to']))
			{
				if (!empty($p_report['date_from']) && !empty($p_report['date_to']))
				{
					list($day,$month,$year) = explode(".", $p_report['date_from']);
					$rep_filter['date_from'] = $p_report['date_from'];
					$date_from_sql = $year.".".$month.".".$day;
					
					list($day,$month,$year) = explode(".", $p_report['date_to']);
					$rep_filter['date_to'] = $p_report['date_to'];
					$date_to_sql = $year.".".$month.".".$day;
					
					$filter .= " and (m.`date` between '".$date_from_sql."' and '".$date_to_sql."')";
					$total_date .= " and (m.`date` between '".$date_from_sql."' and '".$date_to_sql."')";
					
				}elseif (!empty($p_report['date_from']) && empty($p_report['date_to'])){
					list($day,$month,$year) = explode(".", $p_report['date_from']);
					$rep_filter['date_from'] = $p_report['date_from'];
					$date_from_sql = $year.".".$month.".".$day;
					
					$filter .= " and (m.`date` >= '".$date_from_sql."')";
					$total_date .= " and (m.`date` >= '".$date_from_sql."')";
					
				}else{				
					list($day,$month,$year) = explode(".", $p_report['date_to']);
					$rep_filter['date_to'] = $p_report['date_to'];
					$date_to_sql = $year.".".$month.".".$day;
					
					$filter .= " and (m.`date` <= '".$date_to_sql."')";	
					$total_date	.= " and (m.`date` >= '".$date_from_sql."')";			
				}
			}else{
				$filter .= " and (m.`date` <> '0000.00.00')";	
				$total_date	.= " and (m.`date` <> '0000.00.00')";				
			}
			
			if ($p_report['group_account'] == 'on')
			{
				$rep_filter['group_account'] = 'on';
			}else{
				$rep_filter['group_account'] = 'off';
			}
			
			if ($p_report['with_transfer'] == 'on')
			{
				$rep_filter['with_transfer'] = 'on';			
			}else{
				$rep_filter['with_transfer'] = 'off';
				$filter .= " and m.`cat_id` > '0'";
				$total_date .= " and m.`cat_id` > '0'";
			}
			
			if (isset($p_report['bill_id']) && $p_report['bill_id'] != '')
			{
				$rep_filter['bill_id'] = $p_report['bill_id'];
				$filter .= " and (m.`bill_id` = '".$rep_filter['bill_id']."')";
				$total_date .= " and (m.`bill_id` = '".$rep_filter['bill_id']."')";
			}
			
			if (isset($p_report['cat_id']) && $p_report['cat_id'] != '')
			{
				$rep_filter['cat_id'] = $p_report['cat_id'];
				$filter .= " and(ca.`cat_id`='".$rep_filter['cat_id']."' or ca.`cat_parent`='".$rep_filter['cat_id']."')";
			}
			
			$rep_filter['currency'] = $p_report['currency'];
			
			if (empty($error_text))
			{
				$report_cat = $report->getReportCat($filter, $rep_filter['group_account']);				
				$report_total = $report->getReportTotalSumm($total_date);

				$k = 0;
				
				if($report_cat && $report_total)
				{
					for($i=0; $i<=count($report_cat); $i++)
					{
						if ($report_cat[$i]['date_for'] != '00.0000')
						{
							for($c=0; $c<count($report_total); $c++)
							{
								if ($report_total[$c]['date_for'] != '00.0000')
								{
									if ( ($report_total[$c]['date_for'] == $report_cat[$i]['date_for']) && 
										 ($report_total[$c]['bill_id'] == $report_cat[$i]['bill_id']) )
									{
										list($month,$year) = explode(".", $report_cat[$i]['date_for']);
										
										$k++;										
										
										if (!empty($report_cat[$i]['cat_name']))
										{
											if ($report_cat[$i]['cat_name'] != $last_cat_name)
											{
												$report_view[$k]['cat_name'] = $report_cat[$i]['cat_name'];
												$last_cat_name = $report_cat[$i]['cat_name'];
												$tmp_last_money = false;
											}else{
												$k--;
											}
											if ($report_cat[$i]['drain'] == 1)
											{
												$img = "red.gif";
											}else{
												$img = "green.gif";
											}
										}else{
											if ($report_cat[$i]['drain'] == 1)
											{
												$img = "red.gif";
												//$report_view[$k]['cat_name'] = "ѕереведено на счет: ".$report_cat[$i]['to_account'];
												$report_view[$k]['cat_name'] = "ѕереводы на счета";
											}else{
												//$report_view[$k]['cat_name'] = "ѕолучено со счета: ".$report_cat[$i]['to_account'];
												$report_view[$k]['cat_name'] = "ѕереводы на счет";
												$img = "green.gif";
											}
										}
										
										$report_view[$k]['month'] = $sys_month[$month];
										$report_view[$k]['bill_name'] = $report_cat[$i]['bill_name'];
										
										//ѕоказываем деньги с валютой
										$report_view[$k]['cat_summ'] = $report_cat[$i]['sum']."&nbsp;".$report_cat[$i]['currency_name']."";					
										
										// tmp_total_sum_month = деньги за этот мес€ц
										$tmp_total_sum_month = $report_total[$c]['summ'];										
										
										// tmp_cat_sum = деньги по категории
										$tmp_cat_sum = $report_cat[$i]['sum'];
										
										// если мы сгрупировываем счета
										if ($rep_filter['group_account'] == 'on')
										{											
											// обнул€ем все деньги за мес€ц
											$tmp_total_sum_month = 0;
											
											// пересчитанные деньги по категории
											// если не рубль
											if ($rep_filter['currency'] > 1)
											{
												//если валюта категории не рубль
												if ($report_cat[$i]['cur_id'] >1)
												{
													//приводим к рублю
													$tmp_cat_sum = $report_cat[$i]['sum'] * $sys_currency[$report_cat[$i]['cur_id']];
													//получаем сумму в выбранной валюте
													$tmp_cat_sum = round(($tmp_cat_sum / $sys_currency[$rep_filter['currency']]),2);
												}else{
													//если валюта категории рубль, то конвертируем в выбранную валюту
													$tmp_cat_sum = round(($report_cat[$i]['sum'] / $sys_currency[$rep_filter['currency']]),2);
												}
											}else{
											//если выбран рубль
												//если валюта категории не рубль
												if ($report_cat[$i]['cur_id'] >1)
												{
													//конвертируем валюту в рубль
													$tmp_cat_sum = round(($report_cat[$i]['sum'] * $sys_currency[$report_cat[$i]['cur_id']]),2);
												}else{
													$tmp_cat_sum = $report_cat[$i]['sum'];
												}
											}
											
											// пересчитанные деньги с валютой
											if (!empty($tmp_last_money))
											{
												$tmp_cat_sum = $tmp_cat_sum + $tmp_last_money;
											}
											$tmp_last_money = $tmp_cat_sum;
											$report_view[$k]['cat_summ'] = $tmp_cat_sum."".$_SESSION['user_currency'][$rep_filter['currency']-1]['cur_name']."";	
											//echo $report_view[$k]['cat_summ']."<br>";
											for($tmp=0; $tmp<=count($report_total); $tmp++)
											{
												if ($report_total[$tmp]['date_for'] == $report_cat[$i]['date_for'])
												{	
													// подсчитываем за мес€ц + переводим все в рубли
													if ($rep_filter['currency'] > 1)
													{
														//если валюта категории не рубль
														if ($report_total[$tmp]['bill_currency'] >1)
														{
															//приводим к рублю
															$tmp_convert = $report_total[$tmp]['summ'] * $sys_currency[$report_total[$tmp]['bill_currency']];
															//получаем сумму в выбранной валюте
															$tmp_total_sum_month = $tmp_total_sum_month + (round(($tmp_convert / $sys_currency[$rep_filter['currency']]),2));
														}else{
															//если валюта категории рубль, то конвертируем в выбранную валюту
															$tmp_total_sum_month = $tmp_total_sum_month + (round(($report_total[$tmp]['summ'] / $sys_currency[$rep_filter['currency']]),2));
														}
													}else{
													//если выбран рубль
														//если валюта категории не рубль
														if ($report_total[$tmp]['bill_currency'] >1)
														{
															//конвертируем валюту в рубль
															$tmp_convert = round(($report_total[$tmp]['summ'] * $sys_currency[$rep_filter['currency']]),2);
															$tmp_total_sum_month = $tmp_total_sum_month + (round(($report_total[$tmp]['summ'] * $sys_currency[$report_total[$tmp]['bill_currency']]),2));
															
														}else{
															$tmp_total_sum_month = $tmp_total_sum_month + $report_total[$tmp]['summ'];
														}
													}
													
													//$tmp_total_sum_month = $tmp_total_sum_month + ($report_total[$tmp]['summ'] * $sys_currency[$report_total[$tmp]['bill_currency']]);
													$report_view[$k]['total_month'] = $tmp_total_sum_month;
												}
											}
										}

										$report_view[$k]['summ'] = round(($tmp_cat_sum * 100) / $tmp_total_sum_month, 1);
										
										if (!empty($rep_filter['currency']))
										{
											$report_view[$k]['total_month'] = $tmp_total_sum_month."&nbsp;".$_SESSION['user_currency'][$rep_filter['currency']-1]['cur_name'];
										}else{
											$report_view[$k]['total_month'] = $tmp_total_sum_month."&nbsp;".$report_cat[$i]['currency_name'];
										}
									}
								}
							}
						}
					}
					//pre($report_total);
					for ($i=0; $i<=count($report_view); $i++)
					{
						if ($report_view[$i]['bill_name'] != $last_bill_name)
						{
							if ($last_bill_name != "")
							{
								if ($last_month != "")
								{
									$result .= "																			
										<tr>
										<td class=cat_add bgcolor='#FFFFFF' valign=top width=3%>&nbsp;</td>
										<td class=cat_add bgcolor='#FFFFFF' width=2% valign=top>&nbsp;</td>
										<td class=cat_add width=15% valign=top align=left>
											<b>»того: ".$report_view[$i-1]['total_month']."</b>
										</td>
										<td valign=top class=cat_add align=left colspan=2 width=80%>
										&nbsp;
										</td>
										</tr>																										
										";
								}
								$result .= "									
									</table>															
								";
							}
							$result .= "
									<table width=100% border=0>
									<tr>
									<br>
									<td width=100% class=report_bill_name colspan=5 valign=top>
										".$report_view[$i]['bill_name']."
									</td>
									</tr>
																
								";
							$last_bill_name = $report_view[$i]['bill_name'];

								$result .= "										
										<tr>
										<td width=2% class=cat_add valign=top>
											&nbsp;
										</td>
										<td class=report_month colspan=4 valign=top>
											".$report_view[$i]['month']."
										</td>
										</tr>																	
									";
							
							$last_month = $report_view[$i]['month'];
								$result .= "
										<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
						   		onMouseOut=this.style.backgroundColor='#FFFFFF';>
										<td class=cat_add bgcolor='#FFFFFF' valign=top width=3%>&nbsp;</td>
										<td class=cat_add bgcolor='#FFFFFF' width=2% valign=top>&nbsp;</td>
										<td class=cat_add width=15% valign=top align=left>
											".$report_view[$i]['cat_name']."
										</td>
										<td valign=top width=15% class=cat_add align=left>
										".$report_view[$i]['cat_summ']." (".$report_view[$i]['summ']."%)
										</td>
										<td valign=top class=cat_add align=left>
										<img src='img/".$img."' width='".$report_view[$i]['summ']."%' height=10></td>
										</tr>
																																				
										";
										
							$last_cat = $report_view[$i]['cat_name'];							
						}
						
						if ($report_view[$i]['month'] != $last_month)
						{
							$result .= "																			
										<tr>
										<td class=cat_add bgcolor='#FFFFFF' valign=top width=3%>&nbsp;</td>
										<td class=cat_add bgcolor='#FFFFFF' width=2% valign=top>&nbsp;</td>
										<td class=cat_add width=15% valign=top align=left>
											<b>»того: ".$report_view[$i-1]['total_month']."</b>
										</td>
										<td valign=top class=cat_add align=left colspan=2 width=80%>
											&nbsp;
										</td>
										</tr>																										
										";												
							$result .= "																			
										<tr>
										<td class=cat_add valign=top>
											&nbsp;
										</td>
										<td class=report_month colspan=4 valign=top>
											".$report_view[$i]['month']."
										</td>
										</tr>																										
										";
								$last_month = $report_view[$i]['month'];
								
								$result .= "
										<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
						   		onMouseOut=this.style.backgroundColor='#FFFFFF';>
										<td class=cat_add bgcolor='#FFFFFF' valign=top width=3%>&nbsp;</td>
										<td class=cat_add bgcolor='#FFFFFF' width=2% valign=top>&nbsp;</td>
										<td  class=cat_add valign=top align=left>
											".$report_view[$i]['cat_name']."
										</td>
										<td valign=top class=cat_add>
										".$report_view[$i]['cat_summ']." (".$report_view[$i]['summ']."%)
										</td>
										<td valign=top class=cat_add>
										<img src='img/".$img."' width='".$report_view[$i]['summ']."%' height=10>
										</td>
										</tr>																
										";
										
								$last_cat = $report_view[$i]['cat_name'];
						}
														
						if ($report_view[$i]['cat_name'] != $last_cat)
						{
							$result .= "
									<tr onMouseOver=this.style.backgroundColor='#f8f6ea';
							onMouseOut=this.style.backgroundColor='#FFFFFF';>
									<td class=cat_add bgcolor='#FFFFFF' valign=top width=3%>&nbsp;</td>
									<td class=cat_add bgcolor='#FFFFFF' width=2% valign=top>&nbsp;</td>
									<td  class=cat_add valign=top align=left width=15%>
										".$report_view[$i]['cat_name']."
									</td>
									<td valign=top class=cat_add width=15%>
									".$report_view[$i]['cat_summ']." (".$report_view[$i]['summ']."%)
									</td>
									<td valign=top class=cat_add>
									<img src='img/".$img."' width='".$report_view[$i]['summ']."%' height=10>
									</td>
									</tr>																
									";
						}				
					}
					$result .= "																			
									<tr>
									<td class=cat_add bgcolor='#FFFFFF' valign=top width=3%>&nbsp;</td>
									<td class=cat_add bgcolor='#FFFFFF' width=2% valign=top>&nbsp;</td>
									<td class=cat_add width=15% valign=top align=left>
										<b>»того: ".$report_view[$i-1]['total_month']."</b>
									</td>
									<td valign=top class=cat_add align=left colspan=2 width=80%>
									&nbsp;
									</td>
									</tr>																										
								";
					
					$_SESSION['print_report'] = $result;
					$tpl->assign('report_view', $result);					
				}
				$tpl->assign('report', $rep_filter);
			}
			else
			{
				$tpl->assign('error_text', $error_text);
				$tpl->assign('report', $rep_filter);				
			}
		}

		break;	
	case "come_for_period":
		//pre($_POST);
		
		if ($_GET['drain'] == '0' || $_GET['drain'] == '1')
		{
			$_POST['report']['drain'] = html($_GET['drain']);
			$_POST['report']['with_transfer'] = 'on';
			$_POST['report']['currency'] = 1;
		}
		
		if (empty($_POST['report']['date_to']))
		{
			$_POST['report']['date_to'] = date("d.m.Y");
		}
		
		$rep_filter = $_POST['report'];		
		
		if ( $rep_filter['date_from'] <> '')
		{
			if ($rep_filter['with_transfer'] != 'on')
			{
				if ($rep_filter['group_account'] == 'on')
				{
					$result = $report->getWithoutBillForPeriod($rep_filter);
				}else{
					$result = $report->getOutCameForPeriod($rep_filter);
				}
			}else{
				//if ($rep_filter['group_account'] == 'on')
				//{
					//echo "за период с группировкой по мес€цам и счету";
				//}else{
				$_POST['report']['group_account'] = 'off';
				$result = $report->getOutCameGroupedMonth($rep_filter);
				//}				
			}
		}else{
			$rep_filter['date_from'] = date('01.01.Y');
			$_POST['report']['date_from'] = date('01.01.Y');
			if ($rep_filter['with_transfer'] != 'on')
			{
				if ($rep_filter['group_account'] == 'on')
				{
					$result = $report->getWithoutBillForPeriod($rep_filter);
				}else{
					$_POST['report']['group_account'] = 'off';
					$result = $report->getOutCameForPeriod($rep_filter);
				}
			}else{
				$_POST['report']['group_account'] = 'off';
				$result = $report->getOutCameGroupedMonth($rep_filter);
			}
		}
		//pre($result);
		
		if ($rep_filter['with_transfer'] != 'on')
		{
			$make_report = make_report_outcome_for_period($result, $rep_filter['drain'], $sys_currency);	
		}else{
			$make_report = make_report_outcome_grouped($result, $rep_filter['drain'], $sys_currency, $sys_month);
		}
		
		$_SESSION['print_report'] = $make_report;

		if (empty($_POST))
		{
			$rep_filter['group_account'] = 'on';
		}

		$tpl->assign('report', $rep_filter);

		$tpl->assign('report_view', $make_report);
		
		break;
	default:
		$rep_filter['group_account'] = 'on';
		$rep_filter['with_transfer'] = 'on';
		$tpl->assign('report', $rep_filter);

		break;
}
/*

с группировкой по мес€цам

SELECT category.cat_name,
       sum(money.money) AS total_sum,
       date_format(money.date, '%m.%Y') AS date_new,
       currency.cur_name,
       currency.cur_id,
       category.cat_id,
       category.cat_parent,
       c2.cat_id AS parent_cat_id,
       CASE
         WHEN category.cat_parent = 0 THEN category.cat_name
         ELSE c2.cat_name
       END AS parent_cat_name
FROM money
     JOIN category ON category.cat_id = money.cat_id
     JOIN bill ON money.bill_id = bill.bill_id
     JOIN currency ON bill.bill_currency = currency.cur_id
     JOIN users ON bill.user_id = users.user_id
     LEFT JOIN category c2 ON category.cat_parent = c2.cat_id
WHERE users.user_id = '3be7fd4466a465e60bfa19eafa869a3e' AND
      money.drain = 1 AND
      money.date >= str_to_date('01.01.2007', '%d.%m.%Y') AND
      money.date < str_to_date(' 21.09.2007', '%d.%m.%Y')
GROUP BY category.cat_name,
         currency.cur_id ,
         date_new
ORDER BY parent_cat_name,
         category.cat_name,
         date_new,
         currency.cur_name
         */
?>