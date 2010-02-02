<br/>
<strong>
	<?=$res['accounts'][$accountId]['name']?>:
	<?=$res['accounts'][$accountId]['totalBalance']?>
	<?=$res['currency'][ $res['accounts'][ $accountId]['currency'] ]['text']?>
</strong>
<p>
<a href="account/1/edit">редактировать счёт</a> |
<a href="/operation/add/waste?accountId=<?=$accountId?>">добавить операцию</a></p>
<br/>
<table border="1" width="100%"><tbody>
	<?php
	while( list(,$operation) = each($operations) )
	{
		?>
		<tr><td width="50%">
			<p><a href="/account/1"><?=$res['category']['user'][ $operation['cat_id'] ]['name']?></a>
			<br />
			<span class="waste" <?// прибыль = profit ?> ><?=$operation['money']?>
			<?=$res['currency'][ $res['accounts'][ $accountId ]['currency'] ]['text']?></span>,
			<?=$operation['date']?>
			 </p>
		</td></tr><?php
	}
	?>
</tbody></table>
