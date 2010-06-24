<div class="ramka3">
    <div class="ct">
        <div class="head">
            <h2>Список услуг</h2>
        </div>
    </div>
    <div id="servicesDescription">
        Список всех возможных услуг
    </div>
    <?php if ( $status == 1 ):?><center><span class="success_msg">Оплата произведена успешно</span></center><?php endif;?>
    <?php if ( $status == 2 ):?><center><span class="fail_msg">Ошибка во время оплаты</span></center><?php endif;?>
    <table width="100%" class="services_table">
    <tr>
        <td class="header">Услуга</td>
        <td class="header">Цена&nbsp;руб./мес.</td>
        <td class="header">Оплачена&nbsp;до</td>
        <td class="header">Продлить</td>
    </tr>
    </tr>
    <?php foreach ( $userServices as $service ):?>
    <tr>
        <td align="left"><?php echo $service['service_name'];?></td>
        <td align="center"><?php echo $service['service_price']; ?></td>
        <td align="center">
            <?php echo ( $service['is_active'] ) ? $service['till'] : 'Не оплачена'; ?><br />
        </td>
        <td align="center">
            <div class="">
                <?php echo form_tag('robokassa/init'); ?>
                    <?php echo tag('input', array('name' => 'service', 'type' => 'hidden', 'value' => $service['id'] )); ?>

                    <select name="term" class="term_select" id="term_<?php echo $service['id'];?>_<?php echo $service['service_price'];?>">
                        <option value="1">1 мес.</option>
                        <option value="2">2 мес.</option>
                        <option value="3">3 мес.</option>
                        <option value="6" selected>6 мес.</option>
                        <option value="12">12 мес.</option>
                    </select>
                    <?php echo tag('input', array('class' => 'service_submit_button', 'id' => 'butt_' . $service['id'], 'type' => 'submit', 'value' => 'Оплатить' )); ?>
                </form>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>
</div>
<script language="javascript">
//<![CDATA[
$(document).ready( function() {

    // Перебираем все селекты периода и прописываем необходимые обработчики
    $('.term_select').each(function() {
        this.updateTotalCost = function(){
            var params = this.id.split("_");
            $('#butt_' + params[1]).val( "Оплатить " + $(this).val() * params[2] + " руб." );
        };

        $(this).click(function(){
            this.updateTotalCost();
        });

        $(this).change(function(){
            this.updateTotalCost();
        });

        this.updateTotalCost();
    });
});
//]]>
</script>
