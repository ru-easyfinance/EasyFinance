<?php
if( isset($res['user']) )
{
	?>
<table cellpadding="0" cellspacing="0" width="100%" style="text-align: center;"><tbody><tr>
	<td>
		<a href="/operation/add/waste"><img src="/img/pda/menuAddOperation.gif" alt="" width="24" height="24" /></a>
                <br><a href="/operation/add/waste">добавить</a>
	</td>
	<td>
		<a href="/operation/listOperations"><img src="/img/pda/menuOperations.gif" alt="" width="24" height="24" /></a>
                <br><a href="/operation/listOperations">журнал</a>
	</td>
	<td>
		<a href="/info"><img src="/img/pda/menuAccounts.gif" alt="" width="24" height="24" /></a>
                <br><a href="/info">счета</a>
	</td>
        <td>
		<a href="/category"><img src="/img/pda/menuCategories.gif" alt="" width="24" height="24" /></a>
                <br><a href="/category">категории</a>
	</td>
</tbody></tr></table>
	<?php
}
?>
<div align="center" id="copyright" class="line">&copy; EasyFinance.ru, 2010</div>
