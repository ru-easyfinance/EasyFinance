<div class="line">
<table class="wide"><tbody><tr>
<td><strong>Журнал</strong></td>
<td align="right" nowrap="nowrap" class="wide">
    <form action="/operation/listOperations" method="GET">
    <input type="hidden" name="period" value="<?php echo $period?>"/>
    <select name="account" style="width:100px;">
    <option value="-1">все</option>
    <?php
    $useOften = false;
    // Если счетов больше чем часто используемых - выводим сверху частоиспользуемые
    if( sizeof( $res['accounts'] ) > sizeof($res['accountsRecent']) )
    {
        while( list($id) = each($res['accountsRecent']) )
        {
            $account = $res['accounts'][ $id ];
        ?>
        <option <?php echo ( $account['id'] == $accountId )?'selected="selected"':''?>
        value="<?php echo $account['id']?>"><?php echo $account['name']?> (<?php echo $res['currency'][ $account['currency'] ]['text']?>)
        </option>
        <?php
        }
        ?><option>-</option><?php
        $useOften = true;
    }
    
    while ( list(,$account) = each($res['accounts']))
    {
        //Пропускаем частоиспользуемые
        if( $useOften && array_key_exists( $account['id'], $res['accountsRecent'] ) ){ continue; }
        
        ?><option <?php echo ( $account['id'] ==$accountId)?'selected="selected"':''?>
        value="<?php echo $account['id']?>"><?php echo $account['name']?> (<?php echo $res['currency'][ $account['currency'] ]['text']?>)</option>
        <?php
    }
    ?>
    </select><input type="submit" value="ok"/>
    </form>
</td>
</tr></tbody></table>
</div>
<div class="menu">
    <a href="/operation/listOperations/?period=day" 
        class="<?php echo ($period == 'day')?'current':''?>">сутки</a> | 
    <a href="/operation/listOperations/?period=week" 
        class="<?php echo ($period == 'week')?'current':''?>">неделя</a> | 
    <a href="/operation/last/"
        class="">посл. изменения</a>
</div>

<?php echo $this->display('blocks/operationsList.tpl')?>
