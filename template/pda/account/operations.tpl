
<div class="line"><table width="100%" ><tbody>
<tr>
    <td>
    <strong><?php echo $res['accounts'][$accountId]['name']?></strong>
        <a href="/accounts/edit/<?php echo $accountId?>">ред.</a>
    </td>
    <td align="right" nowrap="nowrap">
        <span class="<?php echo ($res['accounts'][$accountId]['totalBalance']<0)?'red':'green'?>">
        <?php echo Helper_Money::format($res['accounts'][$accountId]['totalBalance'])?>
        </span>
        <?php echo $res['currency'][ $res['accounts'][ $accountId]['currency'] ]['text']?>
    </td>
</tr>
</tbody></table></div>

<?php echo $this->display('blocks/operationsList.tpl')?>
