
	<table width="100%"><tbody>
	<tr>
		<td>
		<strong><?=$res['accounts'][$accountId]['name']?></strong>
		<a href="/accounts/edit/<?=$accountId?>">редактировать</a>
		</td>
		<td align="right" nowrap="nowrap">
			<span class="<?=($res['accounts'][$accountId]['totalBalance']<0)?'red':'green'?>">
				<?=Helper_Money::format($res['accounts'][$accountId]['totalBalance'])?>
			</span>
			<?=$res['currency'][ $res['accounts'][ $accountId]['currency'] ]['text']?>
		</td>
	</tr>
	</tbody></table>

<div class="line">
	<a href="/operation/add/waste?accountId=<?=$accountId?>">добавить операцию</a>
</div>

<?=$this->display('blocks/operationsList.tpl')?>
