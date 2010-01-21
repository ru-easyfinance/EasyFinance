<?php

define ('INDEX', true);
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/config.php');
//include (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/common.php');

require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/functions.php');
spl_autoload_register('__autoload');


// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

class SeoText{
    function __construct()
    {
        $this->db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
        
    }
    
    function listAll (){
        $sql = "SELECT * FROM articles ";
        $articleList = Core::getInstance()->db->query($sql);
        return array('result'=>$articleList);
    }

    function save($args){
        $article = new Article();
        $title = (string)$_POST('title');
        $announce = (string)$_POST('preview');
        $body = (string)$_POST('text');
        $article->create($title, $announce, $body);
        return array('result' => 'ok');
    }

    function editor($args){
        $article = new Article();
        $id = $_POST('id');
        $title = (string)$_POST('title');
        $announce = (string)$_POST('preview');
        $body = (string)$_POST('text');
        $article->edit($id, $title, $announce, $body);
        return array ('result' => 'ok');
    }

    function ArticleDel($args)
    {
        $article = new Article();
        $id = $_POST('id');
        $article->delete($id);
        return array ('result' => 'ok');
    }
    
}
//die('fjhwkf');

$art = new SeoText();
$art->listAll();
die($art);

?>
