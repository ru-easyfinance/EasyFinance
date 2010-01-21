<?php

define ('INDEX', true);
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/config.php');
//include (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/common.php');

require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/functions.php');
spl_autoload_register('__autoload');


// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

class Articles{
    function __construct()
    {  
        $this->db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
    }
    
    function listAll (){
        $sql = "SELECT * FROM articles ";
        $articleList = $this->db->query($sql);
        return ($articleList);
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
switch ($args[0])
	{
            case "listAll":
                $art = new Articles();
                return $art->listAll();
                //break;
            case "save":
                $acc = new Articles_Controller();
                $acc->save($args);
                break;
            case "editor":
                $acc = new Articles_Controller();
                $acc->editor($args);
                break;
            case "ArticleDel":
                $acc = new Articles_Controller();
                $acc->ArticleDel($args);
                break;
	}
//$art = new Articles();
//$art->listAll();
//die(print_r($art));

?>
