<p><strong><?=(isset($operation['id']))?'Редактирование':'Добавление'?> операции</strong></p>
<p class="menu">
	<a href="/operation/add/waste" class="<?=($operationType == 0)?'current':''?>">расход</a> |
	<a href="/operation/add/profit" class="<?=($operationType == 1)?'current':''?>">доход</a> |
	<a href="/operation/add/transfer" class="<?=($operationType == 2)?'current':''?>">перевод</a> |
	<a href="/operation/add/target" class="<?=($operationType == 4)?'current':''?>">цель</a>
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
