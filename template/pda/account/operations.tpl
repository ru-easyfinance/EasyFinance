<strong>
	<?=$res['accounts'][$accountId]['name']?>:
	<?=$res['accounts'][$accountId]['totalBalance']?>
	<?=$res['currency'][ $res['accounts'][ $accountId]['currency'] ]['text']?>
</strong>
<div class="line">
	<a href="/operation/add/waste?accountId=<?=$accountId?>">добавить операцию</a>
</div>
<div class="line">
	<a href="account/1/edit">редактировать счёт</a>
</div>

	<?php
	while( list(,$operation) = each($operations) )
	{
		?>
		<div class="operation">
			<p><a href="/account/1"><?=$res['category']['user'][ $operation['cat_id'] ]['name']?></a>
			<br />
			<span class="waste" <?// прибыль = profit ?> ><?=$operation['money']?>
			<?=$res['currency'][ $res['accounts'][ $accountId ]['currency'] ]['text']?></span>,
			<?=$operation['date']?>
			 </p>
		</div><?php
	}
	?>
