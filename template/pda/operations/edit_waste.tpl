<?php
$accountId = 0;

if( isset(_Core_Request::getCurrent()->get['accountId']) )
{
	$accountId = (int)_Core_Request::getCurrent()->get['accountId'];
}

$this->display( 'blocks/operation_menu.tpl' ) ?>
<form method="POST">
<div class="wide">
	<input type="hidden" name="id" value=""></input>
	<div class="line">Сумма: <br /><input class="wide" name="amount" value="<?=(isset($operation['amount']))?$operation['amount']:''?>" inputmode="user digits" /></div>
	<div class="line">Счёт:<br />
	<select name="account" class="wide">
		<?php
		if( $accountId != 0 || !isset($operation['account']) || !$operation['account'] )
		{
			?><option value="0">не выбран</option><?php
		}
		
		while ( list(,$account) = each($res['accounts']))
		{
			?><option <?=($account['id'] == $accountId 
					|| ( isset($operation['account']) && $account['id'] == $operation['account'] )
					)?'selected="selected"':''?>
			value="<?=$account['id']?>"><?=$account['name']?> (<?=$res['currency'][ $account['currency'] ]['text']?>)</option><?php
		}
		?>
	</select></div>
	<div class="line">
	Категория: <br><select name="category" class="wide" >
		<option>не выбрана</option>
		<?php
		while( list(,$category) = each($res['category']['user']) )
		{
			if( $category['type'] != 1 && $category['name'])// Выводим только расходные и универсальные и с именем =)
			{
				?><option value="<?=$category['id']?>"
				<?=(isset($operation['category']) && $operation['category'] == $category['id'])?"selected='selected'":''?>
				><?=$category['name']?></option>
				<?php
			}
		}
		?>
	</select></div>
	<div class="line">
	Дата: <select name="date[day]"><?php
	for( $day = 1; $day <= 31; $day++ )
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
	?></select></div>
	<div class="line">
	Комментарий: <textarea name="comment" cols="15" rows="3" class="wide" inputmode="user" ><?=isset($operation['comment'])?$operation['comment']:''?></textarea><br/>
	</div>
	
	<input id="btnSave" type="submit" style="width:100%" value="Сохранить">
</div>
</form>
