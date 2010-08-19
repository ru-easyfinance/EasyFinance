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
        <td align="center" id="price_<?php echo $service['id']; ?>"><?php echo $service['service_price']; ?></td>
        <td align="center">
            <?php echo ( $service['is_active'] ) ? $service['till'] : 'Не оплачена'; ?><br />
        </td>
        <td align="center">
            <div id="service_content_<?php echo $service['id']; ?>">
                <?php echo form_tag('robokassa/init'); ?>
                    <?php echo tag('input', array('name' => 'service', 'type' => 'hidden', 'value' => $service['id'] )); ?>

                    <select name="term" class="term_select" id="term_<?php echo $service['id'];?>">
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

<div class="ramka3 robokassa" id="paymentBlock">
    <div class="ct">
        <div class="head">
            <h2>Оплата услуг</h2>
        </div>
    </div>
    <div id="servicesDescription">
        Выберите более удобный для Вас способ оплаты
    </div>
    <div id="paymentOptions"></div>
    <div class="notifications">
        <dl>
        
			<dt id="title_PCR">Яндекс.Деньги</dt>
			<dd id="notification_PCR">Яндекс.Деньги — удобный и безопасный способ платить за телефон, интернет и многие другие товары и услуги без комиссии и без очередей.</dd>
		    
		    <dt id="title_WMRM">WMR</dt>
            <dd id="notification_WMRM">WMR — средства эквивалентные Российским рублям</dd>
            
		</dl>
    </div>
    <div id="scriptContainer"></div>
</div>

<script language="javascript">
//<![CDATA[
$(document).ready( function() {

	$(".service_submit_button").robokassa({
	    url: "<?php echo url_for("@robokassa_json") ?>"
	});
	
    // Перебираем все селекты периода и прописываем необходимые обработчики
    $('.term_select').each(function() {
        
        this.updateTotalCost = function(){
            var params = this.id.split("_");
            var price = parseFloat($("#price_"+params[1]).text());
            $('#butt_' + params[1]).val( "Оплатить " + $(this).val() * price + " руб." );
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
