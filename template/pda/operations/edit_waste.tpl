<?php
$accountId = 0;

if( isset(_Core_Request::getCurrent()->get['accountId']) )
{
	$accountId = (int)_Core_Request::getCurrent()->get['accountId'];
}
?>
<p><strong>Добавление операции</strong></p>

<?php $this->display( 'blocks/operation_menu.tpl' );
if( isset($error) && is_array($error) && array_key_exists( 'text', $error ) )
{
	?><p style="color:red; font-weight:bold; font-size: 12px;"><?=$error['text']?></p><?php
}
if( isset($result) && is_array($result) && array_key_exists('text', $result) )
{
	?><p style="color:green; font-weight:bold; font-size: 12px;"><?=$result['text']?></p><?php
}
?>
<form method="POST">
<div style="width:100%">
	<input type="hidden" name="type" value="<?=$res['accounts']?>"></input>
	Счёт: <select name="account" >
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
	Сумма: <input name="amount" size="15" value="<?=(isset($operation['amount']))?$operation['amount']:''?>" inputmode="user digits" /><br />
	Категория: <select name="category" style="width: 8em" >
		<?php
		while( list(,$category) = each($res['category']['user']) )
		{
			if( $category['type'] != 1 )// Выводим только универсальные
			{
				?><option value="<?=$category['id']?>"
				<?=(isset($operation['category']) && $operation['category'] == $category['id'])?"selected='selected'":''?>
				><?=$category['name']?></option>
				<?php
			}
		}
		?>
	</select><br />
	Дата: <select name="date[day]"><?php
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
	Комментарий: <textarea name="comment" cols="15" rows="3" style="width:100%" inputmode="user" ></textarea><br/>
	
	<input id="btnSave" type="submit" style="width:100%" value="Сохранить">
</div>
</form>
