<?php
		$operationConf['currentAccount'] = $currentAccount;
		$operationConf['currentCategory'] = html($g_cat_id);

		list($fday,$fmonth,$fyear) = explode(".", $g_dateFrom);
		$operationConf['dateFrom'] =$fyear.".".$fmonth.".".$fday;

		list($tday,$tmonth,$tyear) = explode(".", $g_dateTo);
		$operationConf['dateTo'] =$tyear.".".$tmonth.".".$tday;

		$operationList = $money->getOperationList($operationConf);
		$total_account_sum = $money->getTotalSum($currentAccount);

		$data = "
			<table border='0' cellpadding='5' cellspacing='0' width='100%' bgcolor='#FFFFFF'>
			<tr>
				<td colspan=4 class='cat_add' bgcolor='#f8f8d8'><span style='color: #3878d7;'>Для редактирования операции выберите ее в списке.</span></td>
			</tr>
			<tr>
				<td colspan='4' class='cat_add' bgcolor='#f8f8d8' style='border-bottom:1px solid #cccccc;'>
					Итого по счету:&nbsp;&nbsp;".number_format($total_account_sum, 2, '.', ' ')." ".$operationList[0]['cur_name']."<br>
					<span style='float:right;'><a href='index.php?modules=export&a=".$currentAccount."'>Экспорт этого счета</a></span><br>
				</td>
			</tr>
			<tr>
				<td class='cat_add' width=20% style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Категория</b></td>
				<td class='cat_add' width=15% align='right' style='padding-right: 15px; border-bottom:1px solid #cccccc;'><b>Сумма</b></td>
				<td class='cat_add' width=10% style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Дата</b></td>
				<td class='cat_add' width=55% style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Комментарий</b></td>
			</tr>";

		$count = count($operationList);
		$total_page_sum = 0;

		if ($count == 0) {
			$data .= "<tr><td colspan='4' class='cat_add' style='padding-left: 25px;'>Нет данных</td></tr>";
		}

//		print '<pre>';
//		print (var_dump($operationList));
//		die('</pre>');

		for ($i=0; $i < $count; $i++) {
			$sum = "<span style='color:green;'>".number_format($operationList[$i]['money'], 2, '.', ' ')."</span>";
			if ($operationList[$i]['drain'] == 1) {
				$sum = "<span style='color:red;'>".number_format($operationList[$i]['money'], 2, '.', ' ')."</span>";
			}

			$cat_parent = "";

			// Если субсчёт (виртуальный, т.е. перевод на финансовую цель)
			if ($operationList[$i]['virt'] == 1) {
			    $cat_parent .= "Перевод на финасовую цель : ";
                $cat_name = "<a href='#' onclick=editTargetOperation('".$operationList[$i]['id']."') class='cat_add'>".
                    $cat_parent.stripslashes($operationList[$i]['cat_name'])."</a>";
			} else {
			    if ($operationList[$i]['cat_parent'] != 0) {
                    $cat_parent .= $parent_category[$operationList[$i]['cat_parent']]['parent_name']." : ";
                }
                $cat_name = "<a href='#' onclick=editOperation('".$operationList[$i]['id']."') class='cat_add'>".$cat_parent.$operationList[$i]['cat_name']."</a>";
                if ($operationList[$i]['cat_id'] == -1) {
                    $cat_name = "<a href='#panelEditOperation' onclick=editOperation('".$operationList[$i]['id']."') class='cat_add'>Перевод на счет: ".$operationList[$i]['cat_transfer']."</a>";
                    if ($operationList[$i]['drain'] == 0)
                    {
                        $cat_name = "Перевод со счета: ".$operationList[$i]['cat_transfer'];
                    }
                }
			}

			if ($operationList[$i]['cat_id'] != 0) {
				$total_page_sum += $operationList[$i]['money'];
				if ($operationList[$i]['virt'] == 1) {
				    $onclick = "deleteTargetOperation('".$operationList[$i]['id']."', '".$operationList[$i]['cat_id']."')";
				}elseif ($operationList[$i]['cat_id'] == -1) {
					$onclick = "deleteOperationTransfer('".$operationList[$i]['tr_id']."')";
				}else{
					$onclick = "deleteOperation('".$operationList[$i]['id']."')";
				}
				$data .= "
					<tr style='background-color: rgb(255, 255, 255);' onmouseover=this.style.backgroundColor='#f8f6ea'; onmouseout=this.style.backgroundColor='#FFFFFF';>
						<td style='padding-left: 6px; border-bottom:1px solid #f8f6ea;' class='cat_add'>
							<a href='#' onclick=".$onclick."><img src='img/delete.gif' border=0></a>
							| ".$cat_name." |
						</td>
						<td class='cat_add' align='right' style='padding-right: 15px; border-bottom:1px solid #f8f6ea;'>
							".$sum." ".$operationList[$i]['cur_name']."
						</td>
						<td class='cat_add' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;'>
							".$operationList[$i]['date']."
						</td>
						<td class='cat_add' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;'>
							".nl2br($operationList[$i]['comment'])."&nbsp;
						</td>
					</tr>
				";
			}
		}

		$data .= "
			<tr>

				<td colspan='4' class='cat_add' bgcolor='#f8f8d8' style='border-top:1px solid #cccccc;'>
					<br>Итого на странице:&nbsp;&nbsp;".number_format($total_page_sum, 2, '.', ' ')." ".$operationList[0]['cur_name']."</td>
			</tr>
		";

		echo $data;
		exit;
?>