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
        $sql = "SELECT id, date, title, status FROM articles ORDER BY date";
        $articleList = $this->db->query($sql);
        return ($articleList);
    }

    function save($args){
        
        $date = (string)$args['date'];
        $title = (string)$args['title'];
        $description = (string)$args['meta_desc'];
        $keywords = (string)$args['meta_key'];
        $announce = (string)$args['preview'];
        $body = (string)$args['text'];
        $status = (int)$status['status'];
        $sql = "INSERT INTO articles (date, title, description, keywords, announce, body, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $article = $this->db->query($sql, $date, $title, $description, $keywords, $announce, $body, $status);
        return array('result' => 'ok');
    }

    function editor( $args ){
        $id = $args['id'];
        $sql = "SELECT * FROM articles WHERE id=?";
        $article = $this->db->query($sql, $id);
        return  ($article);
    }

    function ArticleDel($args)
    {
        $id = $args['id'];
        $sql = "DELETE FROM articles WHERE id=?";
        $article = $this->db->query($sql, $id);
        return array ('result' => 'ok');
    }
    
}
switch ($_REQUEST['page'])
    {
        case "listAll":            
            $art = new Articles();
            $articleList = $art->listAll();
            require 'articles.list.html';
            //die(print_r($articleList));
            break;
        case "save":
            $art = new Articles();
            $save = $art->save($_POST);
            //$art = new Articles();
            $articleList = $art->listAll();
            require 'articles.list.html';
            break;
        case "editor":
            $art = new Articles();
            $res = $art->editor($_GET);
            require 'articles.editor.html';
            break;
        case "articleDel":
            $art = new Articles();
           // die('jfk');
            $art->ArticleDel($_GET);
            $articleList = $art->listAll();
            require 'articles.list.html';            
            break;
    }
//$art = new Articles();
//$art->listAll();
//echo('<pre>');
//die(print_r($art));

?>
