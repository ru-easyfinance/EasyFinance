<?php
if( isset($res['user']) )
{
	?>
<table cellpadding="0" cellspacing="0" width="100%" style="text-align: center; font-size: smaller;"><tbody><tr>
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
		<table style="width:100%;height:20px; background-color:#93ca57; margin-top: 4px; border-top: solid 1px #266823;"><tr>
			<td style="padding-left: 5px; padding-top:1px;">
				<a href="<?=isset($res['user'])?'/info':'/login'?>" style="color:white; font-size: small; font-weight: bold; text-decoration:none;">&copy; EasyFinance, 2010</a><br>
                                <a href="https://easyfinance.ru" style="font-size: small; font-weight: bold; text-decoration:none;">Основная версия</a>
			</td>
                        <td width="80" style="text-align: right;">
			<?php
			if( isset($res['user']) )
			{
				?>
                                <!--<a href="/logout" style="font-size: smaller; vertical-align: middle;">выход</a>-->
                                <a href="/logout" style="font-size: smaller; display:inline"><img src="/img/pda/menuLogout.gif" alt="выход" width="24" height="24" style="display: inline; color: #000000; padding-right: 0px;"></a>
				<?php
			}
			?>
			</td>
		</tr></table>