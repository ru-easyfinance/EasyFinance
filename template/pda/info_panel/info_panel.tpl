<div style="width: 100%;"><a style="float: right;" href="/accounts/add">добавить</a><strong>Счета</strong></div>
<br />
<table border="1" width="100%">
          <tbody>
	<?php 
	$totalBalance = 0;
	while( list(, $account) = each($res['accounts']) )
	{
          	?><tr>
		<td width="50%"><a href="/operation/account/<?=$account['id']?>"><?=$account['name']?></a></td>
		<td align="right" width="50%"><?=$account['totalBalance']?> <?=$res['currency'][ $account['currency'] ]['text']?> </td>
		</tr>
		<?php
		// Расчёт итоговой суммы
		// формула: (наличные_аккаунта * курс_валюты_аккаунта) / курс_валюты_по_умолчанию
		$totalBalance += ($account['totalBalance'] * $res['currency'][ $account['currency'] ]['cost']) / $res['currency'][ $res['currency']['default'] ]['cost'];
	}
	?>
          <tr>
            <td width="50%"><strong>ИТОГО:</strong></td>
            <td align="right" width="50%"><strong><?=$totalBalance?> <?=$res['currency'][ $res['currency']['default'] ]['text']?> </strong></td>
          </tr>
        </tbody></table>
