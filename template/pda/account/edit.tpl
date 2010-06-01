<strong><?php echo (false)?'Редактирование счёта' . $accountName : 'Добавление счёта';?></strong>
<br /><br />
<form method="POST" action="/my/account.pda">
<table width="220px"><tbody>
<tr>
    <td><span class="asterisk">*</span> Тип:</td>
    <td>
        <select id="acc_type" name="type_id">
            <option value="1" <?php if (1 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Наличные</option>
            <option value="2" <?php if (2 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Дебетовая карта</option>
            <option value="9" <?php if (9 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Кредит</option>
            <option value="5" <?php if (5 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Депозит</option>
            <option value="6" <?php if (6 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Займ выданный</option>
            <option value="7" <?php if (7 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Займ полученый</option>
            <option value="8" <?php if (8 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Кредитная карта</option>
            <option value="15" <?php if (15 == (isset($acc['type'])?$acc['type']:0) ) { ?> selected="selected" <?php } ?> >Электронный кошелек</option>
        </select>
    </td>
</tr>
<tr>
    <td><span class="asterisk">*</span> Название:</td>
    <td><input name="name" value="<?php echo(isset($acc['name'])?$acc['name']:'') ?>"></td>
</tr>
<tr>
    <td>Начальный баланс&nbsp;:</td>
    <td><input name="initPayment" value="<?php echo(isset($acc['money'])?$acc['money']:'') ?>"></td>
</tr>
<tr>
    <td><span class="asterisk">*</span> Валюта:</td>
    <td>
    <select name="currency_id">
        <?php
        /*while( list(,$currency) = each($res['currency']) )
        {
            if( !is_array($currency) )
            {
                continue;
            }
            ?><option value="<?php echo $currency?>"><?php echo $currency['text']?></option>
            <?php
        }*/
                foreach ($res['currency'] as $k=>$v){
                    if ($k !='default' ) {
                    $currN = (isset($acc['currency'])?$acc['currency']:0);
                    ?><option  <?php if ( $k == ($currN) ) { ?> selected <?php } ?> value="<?php echo $k ?>"><?php echo $v['text']?></option>
                    <?php
                    }
                }
        ?>
    </select>
    </td>
</tr>
<tr>
    <td>Комментарий:</td>
    <td><textarea style="width: 100%;" rows="3" cols="20" name="description"><?php echo(isset($acc['description'])?$acc['description']:'') ?></textarea></td>
</tr>
<tr>
    <td><input type="submit" value="Сохранить" id="btnSave">
        <?php if (isset($acc['description'])) { ?><a href="/accounts/delete/<?php echo($acc['id']) ?>">удалить</a></td><?php } ?>
    <td></td>
</tr>
</tbody></table>
</form>
