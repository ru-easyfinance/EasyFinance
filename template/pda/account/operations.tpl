
	<table width="100%"><tbody>
	<tr>
		<td>
		<strong><?=$res['accounts'][$accountId]['name']?></strong>
		<a href="/account/edit/<?=$accountId?>">ред.</a>
		</td>
		<td align="right">
			<span class="<?=($res['accounts'][$accountId]['totalBalance']<0)?'red':'green'?>">
				<?=Helper_Money::format($res['accounts'][$accountId]['totalBalance'])?>
			</span>
			<?=$res['currency'][ $res['accounts'][ $accountId]['currency'] ]['text']?>
		</td>
	</tr>
	</tbody></table>

<div class="line">
	<a href="/operation/add/waste?accountId=<?=$accountId?>">добавить</a>
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
