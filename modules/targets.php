<?php
/**
 * Модуль для работы с Финансовыми целями
 * @package      home-money.ru
 * @author       Max Kamashev <max.kamashev@floscoeli.com>
 * @version      SVN: $Id$
 *
 * vim: set ts=4 sw=4 tw=0:
 */
//TODO Добавить выбор валюты, в какой будет хранится сумма и соответственно, переводы можно делать только на идентичные валютные кошельки

// подключаем все необходимые библиотеки
//FIXME это нужно делать в одном месте
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");
require_once (SYS_DIR_LIBS . "targets.class.php");

//XXX Этот блок коннекта с БД, удачнее смотрелся бы в индексе
try {
    $dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
    $dbs->query("SET character_set_client = 'utf8',
                character_set_connection = 'utf8',
                character_set_results = 'utf8'");
} catch (Exception $e) {
    trigger_error("mysql connect error: ".mysql_error());
}

$dbs->setErrorHandler('databaseErrorHandler');
function databaseErrorHandler($message, $info){
    // Если использовалась @, ничего не делать.
    if (!error_reporting()) return;
    // Выводим подробную информацию об ошибке.
    echo "SQL Error: $message<br><pre>";
    print_r($info);
    echo "</pre>";
    exit();
}
$dbs->setLogger('myLogger');
function myLogger($db, $sql){
    //trigger_error($sql);
}


//TODO Сделать нормальную проверку прав пользователя
if (empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$targets = new TargetsClass($dbs);

// Говорим, какую страницу будем показывать
$tpl->assign('name_page', 'targets/targets');

// получаем действие
$action = html($g_action);
$target_id = abs((int)@$_GET['target_id']);
$index = abs((int)@$_GET['index']);
switch ($action) {
    case "add":
        if ($targets->addTarget()) {
            header("Location: /index.php?modules=targets");
            exit();
        } else {
            $tpl->assign('action','add');
            $tpl->assign('template','form');
        }
        break;

    case "edit":
        if ($targets->editTarget($target_id)) {
            header("Location: /index.php?modules=targets");
            exit();
        } else {
            $tpl->assign('action','edit');
            $tpl->assign('template','form');
        }
        break;

    case "del":
        $targets->delTarget($target_id);
        header("Location: /index.php?modules=targets"); //FIXME Поправить урл
        exit();
        break;

    case "user_list":
        $tpl->assign('user_list_targets',$targets->getLastList($index));
        $tpl->assign('template','pages.list');
        $tpl->assign('view_list','user_list');
        break;

    case "pop_list":
        $tpl->assign('pop_list_targets',$targets->getPopList($index));
        $tpl->assign('template','pages.list');
        $tpl->assign('view_list','pop_list');
        break;

    default:
        // Список ближайших целей пользователя
        $tpl->assign('user_list_targets',$targets->getLastList());

        //Список популярных целей у остальных
        $tpl->assign('pop_list_targets',$targets->getPopList());

        $tpl->assign('template','default');
}