<strong>
	<?=$res['accounts'][$accountId]['name']?>:
	<?=$res['accounts'][$accountId]['totalBalance']?>
	<?=$res['currency'][ $res['accounts'][ $accountId]['currency'] ]['text']?>
</strong>
<div class="line">
	<a href="/operation/add/waste?accountId=<?=$accountId?>">добавить операцию</a>
</div>
<div class="line">
	<a href="/account/edit/<?=$accountId?>">редактировать счёт</a>
</div>
	<?php
	while( list(,$operation) = each($operations) )
	{
		?><div class="operation">
			<table class="wide" cellspacing="2" cellpadding="0"><tbody><tr>
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
					<br />
					<span class="<?=$operation['drain']?'red':'green'?>" ><?=Helper_Money::format($operation['money'])?></span>
					<?=$res['currency'][ $res['accounts'][ $accountId ]['currency'] ]['text']?>,
					<?=$operation['date']?>
				</td>
				<td>
					<a href="/operation/del/<?=$operation['id']?>">(Х)</a>
				</td>
			</tr></tbody></table>
		</div><?php
	}
	?>
