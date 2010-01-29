<strong>Добавление операции</strong>
<br /><br />
<?php $this->display( 'blocks/operation_menu.tpl' )?>
<form>
<div style="width:100%">
	<input type="hidden" name="type" value="1"></input>
	<nobr>Сумма: <input name="amount" style="position:relative;width: 100%"/></nobr><br />
	Категория: <select name="category" style="width: 100%" >
				<?php
				while( list(,$category) = each($res['category']['user']) )
				{
					?><option value="<?=$category['id']?>"><?=$category['name']?></option>
					<?php
				}
				?></select><br />
	<!-- Дата:&nbsp;<input name="date" value="<?=date("d.m.Y")?>" style="width:100%"/><br/> -->
	Дата:&nbsp;<select name="date[day]"><?php
	for( $day = 1; $day <= date("t"); $day++ )
	{
		?><option <?=($day==date('j'))?'selected="selected"':''?>
		><?=str_pad($day,2,'0',STR_PAD_LEFT)?></option><?php
	}
	?>
	</select>.<select name="date[month]"><?php
	for( $month = 1; $month <= 12; $month ++ )
	{
		?><option <?=($month==date('n'))?'selected="selected"':''?>
		><?=str_pad($month,2,'0',STR_PAD_LEFT)?></option><?php
	}
	?>
	</select>.<select name="date[year]"><?php
	for( $year = 2000; $year <= (date('Y') + 5); $year++ )
	{
		?><option <?=($year==date('Y'))?'selected="selected"':''?>
		><?=$year?></option><?php
	}
	?></select>
	<br/>
	Комментарий: <textarea name="comment" cols="15" rows="3" style="width:100%"></textarea><br/>
	
	<input id="btnSave" type="submit" style="width:100%" value="Сохранить">
</div>
</form>
