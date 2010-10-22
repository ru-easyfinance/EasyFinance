<?php
    $subject = array(
        'name'  => 'title',
        'label'  => 'Тема'
    );
    $msg = array(
        'name'  => 'msg',
        'label'  => 'Сообщение',
        'taClass' => 'b-feedback-message'
    );
    $mail = array(
        'name'  => 'email',
        'label' => 'e-mail адрес'
    )
?>
<div class="b-feedback js-widget js-widget-feedback hidden">
    <div class="js-feedback-dialogue" ondblclick="return {width: 750, height: 620, title: '«Хочу предложить вам…»'}">
        <div style="margin-top: 1em;">
            <div id="feedbacktabs" class="b-feedback-tabs js-control js-control-tabs">
                <div id="feedbacktabs-1">
                    <script type="text/javascript"><!--
                    reformal_wdg_w    = "713";
                    reformal_wdg_h    = "460";
                    reformal_wdg_domain    = "easyfinance";
                    reformal_wdg_mode    = 5;
                    reformal_wdg_title   = "Сделайте EasyFinance удобнее";
                    reformal_wdg_ltitle  = "Есть идеи? Выскажите их!…";
                    reformal_wdg_lfont   = "Verdana, Geneva, sans-serif";
                    reformal_wdg_lsize   = "12px";
                    reformal_wdg_color   = "#0033ff";
                    reformal_wdg_bcolor  = "#348a2f";
                    reformal_wdg_tcolor  = "#FFFFFF";
                    reformal_wdg_align   = "left";
                    reformal_wdg_charset = "utf-8";
                    reformal_wdg_waction = 0;
                    reformal_wdg_vcolor  = "#9FCE54";
                    reformal_wdg_cmline  = "#eee";
                    reformal_wdg_glcolor  = "#eb6b44";
                    reformal_wdg_tbcolor  = "#FFFFFF";
                    //-->
                    </script>
                    <div class="poxupih_center"><script type="text/javascript" language="JavaScript" src="http://widget.reformal.ru/tabn3v4.js"></script><div class="drsdtf">на платформе <a href="http://reformal.ru" target="_blank" title="Reformal.ru">Reformal.ru</a></div></div>
                </div>
                <div id="feedbacktabs-2">
                    <form class="b-form-skeleton" action="/feedback/add_message/?responseMode=json" method="post">
                        <p>Оставьте заявку на техническую поддержку.</p>
                        <div class="b-row">
                            <div class="b-col">
                                <div class="b-col-indent">
                                    <?php include_partial('global/common/ui/textfield', $subject); ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-row">
                            <div class="b-col">
                                <div class="b-col-indent">
                                    <?php include_partial('global/common/ui/textarea', $msg); ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-row">
                            <div class="b-col">
                                <div class="b-col-indent">
                                    <?php include_partial('global/common/ui/textfield', $mail); ?>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="cheight"/>
                        <input type="hidden" name="cwidth"/>
                        <input type="hidden" name="width"/>
                        <input type="hidden" name="height"/>
                        <input type="hidden" name="colors"/>
                        <input type="hidden" name="plugins"/>
                    </form>
                </div>
                <ul class="b-feedback-tabs-switchers">
                    <li><a href="#feedbacktabs-1">«Хочу предложить вам…»</a></li>
                    <li><a href="#feedbacktabs-2">У меня проблема</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
