<strong><?=(false)?'Редактирование счёта' . $accountName : 'Добавление счёта';?></strong>
<br /><br />
<form method="POST">
<table width="220px"><tbody>
<tr>
	<td><span class="asterisk">*</span> Тип:</td>
	<td>
		<select id="acc_type" name="type">
			<option value="1" <? if (1 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Наличные</option>
			<option value="2" <? if (2 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Дебетовая карта</option>
			<option value="9" <? if (9 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Кредит</option>
			<option value="5" <? if (5 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Депозит</option>
			<option value="6" <? if (6 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Займ выданный</option>
			<option value="7" <? if (7 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Займ полученый</option>
			<option value="8" <? if (8 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Кредитная карта</option>
			<option value="15" <? if (15 == isset($acc['type'])?$acc['type']:0) { ?> selected="selected" <? } ?>>Электронный кошелек</option>
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
                foreach ($res['currency'] as $k=>$v){ if ($k !='default' ) if ($k!='errors') 
                    ?><option value="<?=$k?> <? if ($k == isset($acc['currency'])?$acc['currency']:0) { ?> selected <? } ?>"><?=$v['text']?></option>
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
	<td><input type="submit" value="Сохранить" id="btnSave">
        <? if (isset($acc['description'])) { ?><a href="/accounts/delete/<? echo($acc['id']) ?>">удалить</a></td><? } ?>
	<td></td>
</tr>
</tbody></table>
</form>
