<strong><?=(false)?'Редактирование счёта' . $accountName : 'Добавление счёта';?></strong>
<br /><br />
<table width="220px"><tbody>
<tr>
	<td>Тип:</td>
	<td>
		<select id="acc_type" name="type">
			<option value="1">Наличные</option>
			<option value="2">Дебетовая карта</option>
			<option value="9">Кредит</option>
			<option value="5">Депозит</option>
			<option value="6">Займ выданный</option>
			<option value="7">Займ полученый</option>
			<option value="8">Кредитная карта</option>
			<option value="15">Электронный кошелек</option>
		</select>
	</td>
</tr>
<tr>
	<td>Название:</td>
	<td><input name="name"></td>
</tr>
<tr>
	<td>Начальный баланс :</td>
	<td><input name="balance"></td>
</tr>
<tr>
	<td>Валюта:</td>
	<td>
	<select name="select">
		<?php
		while( list(,$currency) = each($res['currency']) )
		{
			if( !is_array($currency) )
			{
				continue;
			}
			?><option value="<?=$currency['id']?>"><?=$currency['text']?></option>
			<?php
		}
		?>
	</select>
	</td>
</tr>
<tr>
	<td>Комментарий:</td>
	<td><textarea style="width: 100%;" rows="3" cols="20" name="comment"></textarea></td>
</tr>
<tr>
	<td><input type="submit" value="Сохранить" id="btnSave"></td>
	<td></td>
</tr>
</tbody></table>
