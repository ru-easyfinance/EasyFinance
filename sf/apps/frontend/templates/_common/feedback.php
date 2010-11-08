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
    <div class="js-feedback-dialogue b-feedback-container" ondblclick="return {width: 530, height: 'auto', title: 'Поддержка — решение Ваших проблем', modal: false}">
        <div class="l-dialog-content">

            <div class="b-feedback-notice">
                <h6 class="b-feedback-notice-header">Внимание!</h6>
                <p>Идеи, вопросы по функционалу, сообщения об ошибках и отзывы просим оставлять на
                    <a class="b-feedback-notice-reformal" href="http://easyfinanceru.reformal.ru" target="_blank">странице обсуждения</a>.</p>
                <p>Здесь просим сообщать только о Ваших персональных проблемах: например, проблемах со входом или необходимости восстановить данные.</p>
            </div>

            <form class="b-form-skeleton" action="/feedback/add_message/?responseMode=json" method="post">
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
    </div>
</div>
