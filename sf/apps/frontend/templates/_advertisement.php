<?php
/**
 * Подключение баннерокрутилки, сплошной хардкод
 * вынести это все в app.yml
 */

$templateUrl = URL_ROOT_BANNERS . "adjs.php?what=format:web,_717x86,_client_id:%d,";
$clientId = 0;

if ($sf_user->isAuthenticated() && IS_DEMO == false) {
    $randomize = mt_rand(0,2);

    switch ($randomize) {
        case 1: // AMT 1
            $clientId = 420;
            break;
        case 2: // AMT 2
            $clientId = 421;
            break;
        default: // Тиньков
            $clientId = 427;
            break;
    }
} else {
    $randomize = mt_rand(0,1);

    switch ($randomize) {
        case 1: // Регистрация + AMT
            $clientId = 418;
            break;
        default: // Тиньков
            $clientId = 427;
            break;
    }
}

?>

<dl id="advertisement">
    <dt>реклама</dt>
    <dd>
        <div id="bannerWrapper">
            <div class="inside" style="text-align:center;">
                <?php echo javascript_include_tag(sprintf($templateUrl, $clientId)) ?>
            </div>
        </div>
    </dd>
</dl>
