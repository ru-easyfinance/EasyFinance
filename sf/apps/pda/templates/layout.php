<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html>
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
        <?php include_stylesheets() ?>
    </head>
    <body>
        <table style="width:100%;height:4px; background-color:#93ca57;border-bottom: solid 1px #266823;">
            <tr>
                    <td></td>
            </tr>
        </table>
        <?php if ($sf_user->isAuthenticated()) : ?>
        <table class="topmenu" cellpadding="0" cellspacing="0"><tbody><tr>
            <td>
                <a href="/operation/add/waste/<?php echo isset($accountId)?'?accountId=' . $accountId:''?>"><img src="/img/pda/menuAddOperation.gif" alt="" width="24" height="24" /></a>
                        <br><a href="/operation/add/waste/<?php echo isset($accountId)?'?accountId=' . $accountId:''?>">добавить</a>
            </td>
            <td>
                <a href="/operation/listOperations"><img src="/img/pda/menuOperations.gif" alt="" width="24" height="24" /></a>
                        <br><a href="/operation/listOperations">журнал</a>
            </td>
            <td>
                <a href="/info"><img src="/img/pda/menuAccounts.gif" alt="" width="24" height="24" /></a>
                        <br><a href="/info">счета</a>
            </td>
            <td class="last">
                <a href="/category"><img src="/img/pda/menuCategories.gif" alt="" width="24" height="24" /></a>
                        <br><a href="/category">категории</a>
            </td>
        </tbody></tr></table>
        <?php endif; ?>
        <div style="padding: 0px 5px 10px 5px;">
            <?php if (!$sf_user->isAuthenticated()) : ?>
            <table cellpadding="0" cellspacing="0" class="wide">
                <tbody>
                    <tr>
                        <td class="wide"><a href="<?php echo url_for('homepage'); ?>">Система управления<br>личными финансами</a></td>
                        <td class="logo"><a href="<?php echo url_for('homepage'); ?>"><img src="/img/logo.gif" alt="EasyFinance.ru" border="0" /></a></td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
            <?php echo $sf_content ?>
        </div>
        <?php if ($sf_user->isAuthenticated()) : ?>
        <table cellpadding="0" cellspacing="0" width="100%" style="text-align: center; font-size: smaller;">
            <tbody>
                <tr>
                    <td>
                        <a href="/operation/add/waste"><img src="/img/pda/menuAddOperation.gif" alt="" width="24" height="24" /></a>
                        <br /><a href="/operation/add/waste">добавить</a>
                    </td>
                    <td>
                        <a href="/operation/listOperations"><img src="/img/pda/menuOperations.gif" alt="" width="24" height="24" /></a>
                        <br /><a href="/operation/listOperations">журнал</a>
                    </td>
                    <td>
                        <a href="/info"><img src="/img/pda/menuAccounts.gif" alt="" width="24" height="24" /></a>
                        <br /><a href="/info">счета</a>
                    </td>
                    <td>
                        <a href="/category"><img src="/img/pda/menuCategories.gif" alt="" width="24" height="24" /></a>
                        <br /><a href="/category">категории</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>
        <table style="width:100%;height:20px; background-color:#93ca57; margin-top: 4px; border-top: solid 1px #266823;"><tr>
            <td style="padding-left: 5px; padding-top:1px;">
                <a href="<?php echo isset($res['user'])?'/info':'/login'?>" style="color:white; font-size: small; font-weight: bold; text-decoration:none;">&copy; EasyFinance, 2010</a><br>
                <a href="https://<?php echo URL_ROOT?>" style="font-size: small; font-weight: bold; text-decoration:none;">Основная версия</a>
            </td>
            <td width="80" style="text-align: right;">
            <?php if ($sf_user->isAuthenticated()) : ?>
                <!--<a href="/logout" style="font-size: smaller; vertical-align: middle;">выход</a>-->
                <a href="<?php echo url_for('logout') ?>" style="font-size: smaller; display:inline"><img src="/img/pda/menuLogout.gif" alt="выход" width="24" height="24" style="display: inline; color: #000000; padding-right: 0px;"></a>
            <?php endif; ?>
            </td>
        </tr></table>
    </body>
</html>