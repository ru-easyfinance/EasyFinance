        <table cellspacing="0" cellpadding="0" class="wide"><tbody>
            <tr>
                <td class="wide"><strong>Счета</strong> <a href="/accounts/add">добавить счёт</a></td>
                <td>&nbsp;</td>
            </tr>
        </tbody></table>
        <br />
<table border="0" width="100%" class="accounts" cellpadding="0" cellspacing="0">
          <tbody>
    <?php 
    $totalBalance = 0;
    $row = 1;
    while( list(, $account) = each($res['accounts']) )
    {
            ?><tr class="<?php echo ($row % 2 == 1) ? 'odd' : 'even'?>">
        <td width="50%"><a href="/operation/account/<?php echo $account['id']?>" class="<?php echo ($account['totalBalance']>0)?'green':'red'?>"><?php echo $account['name']?></a></td>
        <td align="right" width="50%">
            <span class="<?php echo ($account['totalBalance']>0)?'green':'red'?>"><?php echo Helper_Money::format($account['totalBalance'])?></span>
                <?php echo $res['currency'][ $account['currency'] ]['text']?> </td>
        </tr>
        <?php
        // Расчёт итоговой суммы
        // формула: (наличные_аккаунта * курс_валюты_аккаунта) / курс_валюты_по_умолчанию
        $totalBalance += ($account['totalBalance'] * $res['currency'][ $account['currency'] ]['cost']) / $res['currency'][ $res['currency']['default'] ]['cost'];
        $row++;
    }
    ?>
          <tr>
            <td width="50%"><strong>ИТОГО:</strong></td>
            <td align="right" width="50%"><strong><span class="<?php echo ($totalBalance>0)?'green':'red'?>"><?php echo Helper_Money::format($totalBalance)?></span> 
            <?php echo $res['currency'][ $res['currency']['default'] ]['text']?> </strong></td>
          </tr>
        </tbody></table>
