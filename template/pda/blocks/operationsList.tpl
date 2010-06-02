<?php
    $row = 1;
    while( list(,$operation) = each($operations) )
    {
        ?><div class="operation <?php echo ($row % 2 == 1) ? 'odd' : 'even'?>"><table class="wide" cellspacing="2" cellpadding="0"><tbody>
            <tr>
                <td valign="top"><?php echo substr($operation['date'],0,-5)?>&nbsp;</td>
                <td class="wide">
                    <a href="/operation/edit/<?php echo $operation['id']?>">
                        <?php
                        // Перевод с текущего счёта
                        if( $operation['type'] == 2 && !$operation['tr_id'])
                        {
                            echo 'перевод "' . $res['accounts'][ $operation['transfer'] ]['name'] . '"';
                        }
                        // Перевод на текущий счёт
                        elseif( $operation['type'] == 2 )
                        {
                            echo 'перевод "' . $res['accounts'][ $operation['transfer'] ]['name'] . '"';
                        }
                        elseif( $operation['type'] == 4 )
                        {
                            echo 'цель "' . $res['user_targets'][ $operation['target_id'] ]['title'] . '"';
                        }
                        elseif ($operation['cat_id'])
                        {
                            // Вывод названия категории
                            echo $res['category']['user'][ $operation['cat_id'] ]['name'];
                        }
                        ?>
                    </a>
                    </td>
                    <td class="<?php echo $operation['drain']?'red':'green'?>" nowrap="nowrap"><?php echo Helper_Money::format($operation['money'])?>&nbsp;</td>
                    <td align="left"><?php echo $res['currency'][ $res['accounts'][ $operation['account_id'] ]['currency'] ]['text']?>
                </td>
                <td>
                    &nbsp;<a href="/operation/del/<?php echo $operation['id']?>" class="red">Х</a>
                </td>
            </tr>
        </tbody></table></div><?php
        $row++;
    }
    ?>
