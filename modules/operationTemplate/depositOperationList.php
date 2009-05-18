<?php
	
$operationList = $money->getDepositOperationList($currentAccount);
$total_account_sum = $operationList[0]['total_sum'];	

		$data = "
			<table border='0' cellpadding='5' cellspacing='0' width='100%' bgcolor='#FFFFFF'>
			<!--<tr>
				<td colspan=6 class='cat_add' bgcolor='#f8f8d8'><span style='color: #3878d7;'>Для редактирования операции выберите ее в списке.</span></td>
			</tr>-->
			<tr>
				<td colspan='6' class='cat_add' bgcolor='#f8f8d8' style='border-bottom:1px solid #cccccc;'>
					Итого по счету:&nbsp;&nbsp;".number_format($total_account_sum, 2, '.', ' ')." ".$operationList[0]['cur_name']."<br>
					<!--<span style='float:right;'><a href='index.php?modules=export&a=".$currentAccount."'>Экспорт этого счета</a></span>--><br>
				</td>
			</tr>
			<tr>
				<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Дата</b></td>
				<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' align=right><b>Сумма операции по депозиту</b></td>
				<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;' align=right><b>Остаток депозита для расчета процентов</b></td>
				<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Начисленные проценты</b></td>
				<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Накопленные проценты</b></td>
				<td class='head_cForm' style='padding-left: 25px; border-bottom:1px solid #cccccc;'><b>Операция</b></td>
			</tr>
			
			";
		
		$count = count($operationList);
		$total_page_sum = 0;
		
		if ($count == 0)
		{
			$data .= "
				<tr>
					<td colspan='4' class='cat_add' style='padding-left: 25px;'>
						Нет данных
					</td>
				</tr>
			";
		}

		for ($i=0; $i<$count; $i++)
		{
			$sum = "<span style='color:green;'>".number_format($operationList[$i]['money'], 2, '.', ' ')."</span>";
			if ($operationList[$i]['drain'] == 1)
			{
				$sum = "<span style='color:red;'>".number_format($operationList[$i]['money'], 2, '.', ' ')."</span>";
			}
			
				$total_page_sum += $operationList[$i]['sum_operation'];
				if ($operationList[$i]['cat_id'] == -1)
				{
					$onclick = "deleteOperationTransfer('".$operationList[$i]['tr_id']."')";
				}else{
					$onclick = "deleteOperation('".$operationList[$i]['id']."')";
				}
				$data .= "
					<tr style='background-color: rgb(255, 255, 255);' onmouseover=this.style.backgroundColor='#f8f8d8'; onmouseout=this.style.backgroundColor='#FFFFFF';>
				<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f8d8;'>".$operationList[$i]['date_operation']."</td>
				<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' align=right>".get_number_format($operationList[$i]['sum_operation'])."</td>
				<td class='cForm' style='padding-left: 25px; border-bottom:1px solid #f8f6ea;' align=right>".get_number_format($operationList[$i]['balance_for_percent'])."</td>
				<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f8d8;' align=center>".$operationList[$i]['accrued_interest']."</td>
				<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f8d8;' align=center>".$operationList[$i]['added_interest']."</td>
				<td class='cForm' style='padding-left: 8px; border-bottom:1px solid #f8f8d8;'>".$operationList[$i]['description']."</td>
			</tr>
				";
		}
		
		$data .= "

			<tr>

				<td colspan='6' class='cat_add' bgcolor='#f8f8d8' style='border-top:1px solid #cccccc;'>
					<br>Итого на странице:&nbsp;&nbsp;".number_format($total_page_sum, 2, '.', ' ')." ".$operationList[0]['cur_name']."</td>
			</tr>
		";
		
		echo $data;
		exit;
?>