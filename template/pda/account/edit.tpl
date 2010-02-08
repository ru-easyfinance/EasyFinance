<strong><?=(false)?'Редактирование счёта' . $accountName : 'Добавление счёта';?></strong>
<br /><br />
<form method="POST">
<table width="220px"><tbody>
<tr>
	<td><span class="asterisk">*</span> Тип:</td>
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
	<td><span class="asterisk">*</span> Название:</td>
	<td><input name="name" value="<?php echo(isset($acc['name'])?$acc['name']:'') ?>"></td>
</tr>
<tr>
	<td>Начальный баланс :</td>
	<td><input name="initPayment" value="<?php echo(isset($acc['money'])?$acc['money']:'') ?>"></td>
</tr>
<tr>
	<td><span class="asterisk">*</span> Валюта:</td>
	<td>
	<select name="currency">
		<?php
		/*while( list(,$currency) = each($res['currency']) )
		{
			if( !is_array($currency) )
			{
				continue;
			}
			?><option value="<?=$currency?>"><?=$currency['text']?></option>
			<?php
		}*/
                foreach ($res['currency'] as $k=>$v){
                    ?><option value="<?=$k?>"><?=$v['text']?></option>
                    <?php
                }
		?>
	</select>
	</td>
</tr>
<tr>
	<td>Комментарий:</td>
	<td><textarea style="width: 100%;" rows="3" cols="20" name="comment"><?php echo(isset($acc['description'])?$acc['description']:'') ?></textarea></td>
</tr>
<tr>
	<td><input type="submit" value="Сохранить" id="btnSave"></td>
	<td></td>
</tr>
</tbody></table>
</form>
