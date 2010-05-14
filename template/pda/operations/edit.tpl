<p><strong><?php echo (isset($operation['id']))?'Редактирование':'Добавление'?> операции</strong></p>
<p class="menu">
    <?php if (isset($operation['id'])) {
        // #876. при редактировании операции
        // менять её тип нельзя, поэтому
        // просто показываем тип текущей операции
        switch($operation['type']) {
            case Operation::TYPE_WASTE:
                echo ("расход");
                break;
            case Operation::TYPE_PROFIT: case 3:
                echo ("доход");
                break;
            case Operation::TYPE_TRANSFER:
                echo ("перевод");
                break;
            case Operation::TYPE_TARGET:
                echo ("цель");
                break;
        }
    } else { ?>
        <a href="/operation/add/waste/<?php echo isset($operation['account'])?'?accountId=' . $operation['account']:''?>"
            class="<?php echo ($operation['type'] == Operation::TYPE_WASTE )?'current':''?>">расход</a> |
        <a href="/operation/add/profit/<?php echo isset($operation['account'])?'?accountId=' . $operation['account']:''?>"
            class="<?php echo ($operation['type'] == Operation::TYPE_PROFIT )?'current':''?>">доход</a> |
        <a href="/operation/add/transfer/<?php echo isset($operation['account'])?'?accountId=' . $operation['account']:''?>"
            class="<?php echo ($operation['type'] == Operation::TYPE_TRANSFER )?'current':''?>">перевод</a> |
        <a href="/operation/add/target/<?php echo isset($operation['account'])?'?accountId=' . $operation['account']:''?>"
            class="<?php echo ($operation['type'] == Operation::TYPE_TARGET )?'current':''?>">цель</a>
    <?php } ?>
</p>
<?php
if( isset($error) && is_array($error) && array_key_exists( 'text', $error ) )
{
    ?><p style="color:red; font-weight:bold; font-size: 12px;"><?php echo $error['text']?></p><?php
}
if( isset($result) && is_array($result) && array_key_exists('text', $result) )
{
    ?><p style="color:green; font-weight:bold; font-size: 12px;"><?php echo $result['text']?></p><?php
    // раз есть результат - скидываем сумму
    $operation['amount'] = null;
}
?><form method="POST">
<div class="wide">
    <input type="hidden" name="id" value=""></input>
    <div class="line"><span class="asterisk">*</span>Сумма:<br />
    <input class="wide" name="amount" value="<?php echo (isset($operation['amount']))?abs($operation['amount']):''?>" inputmode="user digits" />
    </div>
    <div class="line"><span class="asterisk">*</span> <?php echo in_array($operation['type'], array(Operation::TYPE_WASTE, Operation::TYPE_PROFIT))?'Счёт':'Со счёта'?>:<br />
    <select name="account" class="wide">
        <?php
        if( 
            // Если какой то вариант уже выбран, зачем вводить в заблуждение
            (!isset($operation['toAccount']) || !$operation['toAccount']) &&
            // Если есть часто используемые счета - по умолчанию самый используемый
            ( sizeof( $res['accounts'] ) < sizeof($res['accountsRecent']) )
        )
        
        $useOften = false;
        // Если счетов больше чем часто используемых - выводим сверху частоиспользуемые
        if( sizeof( $res['accounts'] ) > sizeof($res['accountsRecent']) )
        {
            while( list($accountId) = each($res['accountsRecent']) )
            {
                $account = $res['accounts'][ $accountId ];
            ?>
            <option <?php echo (isset($operation['account']) && $account['id'] == $operation['account'])?'selected="selected"':''?>
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
            
            ?><option <?php echo (isset($operation['account']) && $account['id'] == $operation['account'])?'selected="selected"':''?>
            value="<?php echo $account['id']?>"><?php echo $account['name']?> (<?php echo $res['currency'][ $account['currency'] ]['text']?>)</option><?php
        }
        ?>
    </select></div>
    
    <?php
    if( in_array($operation['type'], array(Operation::TYPE_WASTE, Operation::TYPE_PROFIT)) )
    {
        ?>
        <div class="line">
        <span class="asterisk">*</span> Категория: <br><select name="category" class="wide" >
            <option>не выбрана</option>
            <?php
            // Выводим список категорий
            echo get_tree_select2(
                isset($operation['category'])?$operation['category']:0
                , $operation['type'] == Operation::TYPE_WASTE?-1:1
            );
            ?>
        </select></div>
        <?php
    }
    
    // Целевой счёт для перевода
    if( $operation['type'] == Operation::TYPE_TRANSFER )
    {
        ?>
        <div class="line"><span class="asterisk">*</span> На счёт: <br><select name="toAccount" class="wide" >
            <?php
            if( 
                // Если какой то вариант уже выбран, зачем вводить в заблуждение
                !isset($operation['toAccount']) || !$operation['toAccount']
            )
            {
                ?><option value="0">не выбран</option><?php
            }
            
            // Если счетов больше чем часто используемых - выводим сверху частоиспользуемые
            reset($res['accountsRecent']);
            if( sizeof( $res['accounts'] ) > sizeof($res['accountsRecent']) )
            {
                while( list($accountId) = each($res['accountsRecent']) )
                {
                    $account = $res['accounts'][ $accountId ];
                ?>
                <option 
                <?php echo (isset($operation['account']) && $account['id'] == $operation['account'])?'selected="selected"':''?>
                value="<?php echo $account['id']?>"><?php echo $account['name']?> 
                (<?php echo $res['currency'][ $account['currency'] ]['text']?>)
                </option>
                <?php
                }
                ?><option>-</option><?php
                $useOften = true;
            }
            
            reset( $res['accounts'] );
            while ( list(,$account) = each($res['accounts']))
            {
                //Пропускаем частоиспользуемые
                if( $useOften && array_key_exists( $account['id'], $res['accountsRecent'] ) ){ continue; }
                
                ?><option 
                <?php echo ( isset($operation['toAccount']) && $account['id'] == $operation['toAccount'] )
                    ?'selected="selected"':''?> 
                    value="<?php echo $account['id']?>">
                    <?php echo $account['name']?> (<?php echo $res['currency'][ $account['currency'] ]['text']?>)
                </option><?php
            }
            ?>
        </select></div>
        <?php
    }
    
    // Целевая финцель
    if( $operation['type'] == Operation::TYPE_TARGET )
    {
        ?>
        <div class="line">
        <span class="asterisk">*</span> На цель: <br><select name="target" class="wide" >
            <?php
            if( !isset($operation['target']) || !$operation['target'] )
            {
                ?><option value="0">не выбрана</option><?php
            }
            
            while ( list(,$target) = each($res['user_targets']))
            {
                ?><option 
                <?php echo ( isset($operation['target']) && $target['id'] == $operation['target'] )?'selected="selected"':''?>
                value="<?php echo $target['id']?>"><?php echo $target['title']?></option><?php
            }
            ?>
        </select></div>
        <?php
    }
    ?>
    
    <?php
    // Преобразуем дату в массив для корректного отображения и сравнения
    $operation['date'] = explode( '.', $operation['date'] );
    ?>
    <div class="line">
    <span class="asterisk">*</span> Дата: <select name="date[day]"><?php
    for( $day = 1; $day <= 31; $day++ )
    {
        ?><option <?php echo ($day == $operation['date'][0])?'selected="selected"':''?>
        ><?php echo str_pad($day,2,'0',STR_PAD_LEFT)?></option><?php
    }
    ?>
    </select>.<select name="date[month]"><?php
    for( $month = 1; $month <= 12; $month ++ )
    {
        ?><option <?php echo ($month==$operation['date'][1])?'selected="selected"':''?>
        ><?php echo str_pad($month,2,'0',STR_PAD_LEFT)?></option><?php
    }
    ?>
    </select>.<select name="date[year]"><?php
    for( $year = 2000; $year <= (date('Y') + 5); $year++ )
    {
        ?><option <?php echo ($year==$operation['date'][2])?'selected="selected"':''?>
        ><?php echo $year?></option><?php
    }
    ?></select></div>
    <div class="line">
    Комментарий: <textarea name="comment" cols="15" rows="3" class="wide" inputmode="user" ><?php echo isset($operation['comment'])?$operation['comment']:''?></textarea><br/>
    </div>
    <?
    if (isset($operation['id'])) {
    ?>
        <table class="wide" cellspacing="2" cellpadding="0"><tbody><tr>
            <td class="wide"><input id="btnSave" type="submit" style="width:100%" value="Изменить"></td>
            <td>&nbsp;&nbsp;&nbsp;<a href="/operation/del/<?php echo $operation['id']?>" class="red">удалить</a></td>
        </tbody></table>
    <?
    } else {
    ?>
        <input id="btnSave" type="submit" style="width:100%" value="Добавить">
    <?
    }
    ?>
</div>
</form>
