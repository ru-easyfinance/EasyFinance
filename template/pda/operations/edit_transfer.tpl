<?php
$accountId = 0;

if( isset(_Core_Request::getCurrent()->get['accountId']) )
{
	$accountId = (int)_Core_Request::getCurrent()->get['accountId'];
}

$this->display( 'blocks/operation_menu.tpl' ) ?>
<form method="POST">
<div style="width:100%">
	<input type="hidden" name="type" value="<?=$res['accounts']?>"></input>
	Сумма: <input name="amount" size="15" value="<?=(isset($operation['amount']))?$operation['amount']:''?>" inputmode="user digits" /><br />
	Со счёта: <select name="account" >
		<?php
		if( !$accountId || !isset($operation['account']) || !$operation['account'] )
		{
			?><option value="0">-</option><?php
		}
		
		while ( list(,$account) = each($res['accounts']))
		{
			?><option <?=($account['id'] == $accountId 
					|| ( isset($operation['account']) && $account['id'] == $operation['account'] )
					)?'selected="selected"':''?>
			value="<?=$account['id']?>"><?=$account['name']?></option><?php
		}
		?>
	</select><br/>
	
	На счёт: <select name="toAccount" >
		<?php
		if( !isset($operation['toAccount']) || !$operation['toAccount'] )
		{
			?><option value="0">-</option><?php
		}
		reset( $res['accounts'] );
		while ( list(,$account) = each($res['accounts']))
		{
			?><option 
			<?=( isset($operation['toAccount']) && $account['id'] == $operation['toAccount'] )?'selected="selected"':''?>
			value="<?=$account['id']?>"><?=$account['name']?></option><?php
		}
		?>
	</select><br/>
	
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
	?></select>
	<br/>
	Комментарий: <textarea name="comment" cols="15" rows="3" style="width:100%" inputmode="user" ></textarea><br/>
	
	<input id="btnSave" type="submit" style="width:100%" value="Сохранить">
</div>
</form>
