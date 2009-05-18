<?php
echo "
						<table style='border: 1px dotted rgb(255, 96, 2); background-color: rgb(248, 248, 216); padding: 10px;'>
						<tr>
							<td align=right class=cat_add width=10%>Название счета:</td>
							<td align=left class=cat_add width=90%><input type='text' value='' name='name' id='name'></td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>Количество грамм:</td>
							<td align=left class=cat_add><input type='text' value='' name='total' id='bank'></td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>На сумму:</td>
							<td align=left class=cat_add>
								<input type='text' id='pos_oc' value='' style='width:80px;' OnKeyUp='onSumChange();'>&nbsp;<b>Пример:</b> 125.50
								<input type='hidden' name='money' id='pos_mc' size='7'>
							</td>
						</tr>
						<tr>
							<td align=right class=cat_add width=10%>Металл:</td>
							<td align=left class=cat_add>
								<select name='metal' id='metal'>
									<option value='1'>Золото</option>
									<option value='2'>Серебро</option>
									<option value='3'>Платина</option>
								</select>
							</td>
						</tr>						
						<tr>
							<td align=right class=cat_add width=10%>Дата открытия счета:</td>
							<td align=left class=cat_add>
								<input type='text' value='".date('d.m.Y')."' class='standart' style='width:80px;' name='sel3' id='sel3'>
								<button type='reset' class='button_calendar' onclick=\"return showCalendar('sel3', '%d.%m.%Y', false, true);\">
								<img src='img/calendar/cal.gif'></button>
							</td>
						</tr>
						<tr>
							<td width=10%>&nbsp;</td>
							<td align=left class=cat_add>
								<input type='hidden' value='4' name='typeAccount' id='typeAccount'>
								<input type='button' value='Назад' onClick=accountNextStep(1);>&nbsp;
								<input type='button' value='Сохранить' onClick=accountSave(5);>&nbsp;
								<input type='button' value='Отмена' onClick=formCreateAccountUnVisible();>
							</td>
						</tr>
						</table>";
exit;
?>