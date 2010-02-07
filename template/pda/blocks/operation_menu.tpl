<p><strong><?=(isset($operation['id']))?'Редактирование':'Добавление'?> операции</strong></p>
<p class="menu">
	<a href="/operation/add/waste/<?=isset($operation['account'])?'?accountId=' . $operation['account']:''?>" 
		class="<?=($operation['type'] == Operation::TYPE_WASTE )?'current':''?>">расход</a> |
	<a href="/operation/add/profit/<?=isset($operation['account'])?'?accountId=' . $operation['account']:''?>" 
		class="<?=($operation['type'] == Operation::TYPE_PROFIT )?'current':''?>">доход</a> |
	<a href="/operation/add/transfer/<?=isset($operation['account'])?'?accountId=' . $operation['account']:''?>" 
		class="<?=($operation['type'] == Operation::TYPE_TRANSFER )?'current':''?>">перевод</a> |
	<a href="/operation/add/target/<?=isset($operation['account'])?'?accountId=' . $operation['account']:''?>" 
		class="<?=($operation['type'] == Operation::TYPE_TARGET )?'current':''?>">цель</a>
</p>
<?php
if( isset($error) && is_array($error) && array_key_exists( 'text', $error ) )
{
	?><p style="color:red; font-weight:bold; font-size: 12px;"><?=$error['text']?></p><?php
}
if( isset($result) && is_array($result) && array_key_exists('text', $result) )
{
	?><p style="color:green; font-weight:bold; font-size: 12px;"><?=$result['text']?></p><?php
}
