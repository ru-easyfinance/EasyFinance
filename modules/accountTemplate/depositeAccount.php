<?php
$cnt = count($_SESSION['user_account']);
$option = "<option value='' selected></option>";
for($i=0; $i<$cnt; $i++)
{
	$option .= "<option value=".$_SESSION['user_account'][$i]['id'].">".$_SESSION['user_account'][$i]['name']."</option>";
}

echo "
						<table style='border: 1px dotted rgb(255, 96, 2); background-color: rgb(248, 248, 216); padding: 10px;'>
						<tr>
							<td align=right class=cat_add width=10%>�������� �����:</td>
							<td align=left class=cat_add width=90%><input type='text' value='' name='name' id='name'></td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>�������� �����:</td>
							<td align=left class=cat_add><input type='text' value='' name='bank' id='bank'></td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>����� ��������:</td>
							<td align=left class=cat_add>
								<input type='text' id='pos_oc' value='' style='width:80px;' OnKeyUp='onSumChange();'>&nbsp;<b>������:</b> 125.50
								<input type='hidden' name='money' id='pos_mc' size='7'>
							</td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>������:</td>
							<td align=left class=cat_add>
								<select name='currency' id='currency'>
									<option value='1'>���������� �����</option>
									<option value='2'>������������ ������</option>
									<option value='3'>����</option>
									<option value='4'>���������� ������</option>
								</select>
							</td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>������� ���������� ������:</td>
							<td align=left class=cat_add><input type='text' value='' name='percent' id='percent' style='width:80px;'>&nbsp;<b>������:</b> 6.5</td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>����������:</td>
							<td align=left class=cat_add>
								<select name='getpercent' id='getpercent'>
									<option value='1'>����������</option>
									<option value='2'>�������������</option>
									<option value='3'>��������</option>
								</select>
							</td>
						</tr>						
						<tr>
							<td align=right class=cat_add width=10%>���� �������� �����:</td>
							<td align=left class=cat_add>
								<input type='text' value='".date('d.m.Y')."' class='standart' style='width:80px;' name='sel3' id='sel3'>
								<button type='reset' class='button_calendar' onclick=\"return showCalendar('sel3', '%d.%m.%Y', false, true);\">
								<img src='img/calendar/cal.gif'></button>
							</td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>��������� ����� �� �����:</td>
							<td align=left class=cat_add>
								<select name='selectAccountForTransfer' id='selectAccountForTransfer' onChange=changeAccountFromMoneyForDeposit();>
									$option
								</select>
								<span id='currencyFromMoneyForDeposit' style='padding-left:5px;'></span>
							</td>
						</tr>
						<tr>
							<td width=10%>&nbsp;</td>
							<td align=left class=cat_add>
								<input type='hidden' value='4' name='typeAccount' id='typeAccount'>
								<input type='button' value='�����' onClick=accountNextStep(1);>&nbsp;
								<input type='button' value='���������' onClick=accountSave(4);>&nbsp;
								<input type='button' value='������' onClick=formCreateAccountUnVisible();>
							</td>
						</tr>
						</table>";
exit;
?>