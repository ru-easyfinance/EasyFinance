<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: index.php 1021 2009-10-29 14:27:15Z ukko $
 */
//include ("../Account/Model.php");
include ("../../classes/Account/Model.php");
$xmlrpc_methods = array();
$xmlrpc_methods['sync.getAuth'] = 'sync_getAuth';
//$xmlrpc_methods['sync.getRecordsMap'] = sync.getRecordsMap;
sync_getAuth();
function sync_getAuth(){
    include ('../../classes/Sync/Sync.php');
    $a = new Sync();
}