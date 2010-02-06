<div class="line"><strong>Журнал операций</strong></div>
<div class="menu">
	<a href="/operation/listOperations/?period=day" 
		class="<?=($period == 'day')?'current':''?>">за сутки</a> | 
	<a href="/operation/listOperations/?period=week" 
		class="<?=($period == 'week')?'current':''?>">за неделю</a> | 
	<a href="/operation/listOperations/?period=month" 
		class="<?=($period == 'month')?'current':''?>">за месяц</a>
</div>
<?php
	while( list(,$operation) = each($operations) )
	{
		?><div class="operation">
			<table class="wide" cellspacing="2" cellpadding="0"><tbody><tr>
				<td valign="top"><?=substr($operation['date'],0,-5)?>&nbsp;</td>
				<td class="wide">
					<a href="/operation/edit/<?=$operation['id']?>">
						<?php
						// Перевод с текущего счёта
						if( $operation['type'] == 2 && !$operation['tr_id'])
						{
							echo 'Перевод на счёт "' . $res['accounts'][ $operation['transfer'] ]['name'] . '".';
						}
						// Перевод на текущий счёт
						elseif( $operation['type'] == 2 )
						{
							echo 'Перевод со счёта "' . $res['accounts'][ $operation['transfer'] ]['name'] . '".';
						}
						elseif( $operation['type'] == 4 )
						{
							echo 'Перевод на фин. цель "' . $res['user_targets'][ $operation['target_id'] ]['title'] . '"';
						}
						else
						{
							// Вывод названия категории
							echo $res['category']['user'][ $operation['cat_id'] ]['name'];
						}
						?>
					</a>
					</td>
					<td class="<?=$operation['drain']?'red':'green'?>" nowrap="nowrap"><?=Helper_Money::format($operation['money'])?>&nbsp;</td>
					<td align="left"><?=$res['currency'][ $res['accounts'][ $operation['account_id'] ]['currency'] ]['text']?>
				</td>
				<td>
					&nbsp;<a href="/operation/del/<?=$operation['id']?>">Х</a>
				</td>
			</tr></tbody></table>
		</div><?php
	}
	?>
