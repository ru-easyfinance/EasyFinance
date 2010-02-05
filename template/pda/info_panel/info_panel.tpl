		<table cellspacing="0" cellpadding="0" class="wide"><tbody>
			<tr>
				<td class="wide"><strong>Счета</strong></td>
				<td><a href="/accounts/add">добавить</a></td>
			</tr>
		</tbody></table>
		<br />
<table border="0" width="100%" class="accounts" cellpadding="0" cellspacing="0">
          <tbody>
	<?php 
	$totalBalance = 0;
	while( list(, $account) = each($res['accounts']) )
	{
          	?><tr>
		<td width="50%"><a href="/operation/account/<?=$account['id']?>"><?=$account['name']?></a></td>
		<td align="right" width="50%">
			<span class="<?=($account['totalBalance']>0)?'green':'red'?>"><?=$account['totalBalance']?></span>
          		<?=$res['currency'][ $account['currency'] ]['text']?> </td>
		</tr>
		<?php
		// Расчёт итоговой суммы
		// формула: (наличные_аккаунта * курс_валюты_аккаунта) / курс_валюты_по_умолчанию
		$totalBalance += ($account['totalBalance'] * $res['currency'][ $account['currency'] ]['cost']) / $res['currency'][ $res['currency']['default'] ]['cost'];
	}
	?>
          <tr>
            <td width="50%"><strong>ИТОГО:</strong></td>
            <td align="right" width="50%"><strong><span class="<?=($totalBalance>0)?'green':'red'?>"><?=$totalBalance?></span> 
            <?=$res['currency'][ $res['currency']['default'] ]['text']?> </strong></td>
          </tr>
        </tbody></table>
