<?php

define ('INDEX', true);
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/config.php');
//include (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/common.php');
include_once('../../../classes/_Core/_Core.php');
new _Core();
//require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/include/functions.php');
//spl_autoload_register('__autoload');


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
        $id = (int)$args['id'];
        $ids = (string)$args['ides'];

        $arrayId = explode( ';' , $ids );

        $date = (string)$args['date'];
        $title = (string)$args['title'];
        $description = (string)$args['meta_desc'];
        $keywords = (string)$args['meta_key'];
        $announce = (string)$args['preview'];
        $body = (string)$args['text'];
        $status = (int)$status['status'];
        if (!$id){
            $sql = "INSERT INTO articles (date, title, description, keywords, announce, body, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $article = $this->db->query($sql, $date, $title, $description, $keywords, $announce, $body, $status);
        }
        else{
            $sql = "UPDATE articles SET date=?, title=?, description=?, keywords=?, announce=?, body=?, status=? WHERE id=?";
            $article = $this->db->query($sql, $date, $title, $description, $keywords, $announce, $body, $status, $id);
        }
        foreach ($arrayId as $k=>$v)
            if ($v != 0)
                $this->imageArticleConnection($v, $article);
        return array('result' => 'ok');
    }

    function publicArt($args){
        $id = (int)$args['id'];
        $sql = "UPDATE articles SET status = 1 WHERE id=?";
        $article = $this->db->query($sql, $id);
    }

    function editor( $args ){
        $id = $args['id'];
        $sql = "SELECT * FROM articles WHERE id=?";
        $article = $this->db->query($sql, $id);
        //$sql = "SELECT "
        $ret = array(
            article => array(
                'id'=>$article[0]['id']
                ,'title'=>$article[0]['title']
                ,'date'=>$article[0]['date']
                ,'author'=>$article[0]['authorName']
                ,'url'=>$article[0]['authorUrl']
                ,'meta_desc'=>$article[0]['description']
                ,'meta_key'=>$article[0]['keywords']
                ,'preview'=>$article[0]['announce']
                ,'text'=>$article[0]['body']
            )
        );
        return  ($ret);
    }

    function ArticleDel($args)
    {
        $id = $args['id'];
        $sql = "DELETE FROM articles WHERE id=?";
        $article = $this->db->query($sql, $id);
        return array ('result' => 'ok');
    }

    function saveImageInfo( $parent , $path , $url )
    {
        $sql = "INSERT INTo images ( parent_id, path , url) VALUES ( ?, ?, ?)";
        $image = $this->db->query($sql, $parent, $path, $url);
        return $image;
    }

    function imageArticleConnection( $image_id , $article_id )
    {
        $sql = "INSERT INTO images_articles ( image_id, article_id) VALUES ( ?, ?)";
        $image = $this->db->query($sql, $image_id, $article_id);
        return $image;
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
        case "public":
            $art = new Articles();
            $art->publicArt($_GET);
            $articleList = $art->listAll();
            require 'articles.list.html';
            break;
        case "addImage":
            $art = new Articles();
            $image = new File_Image( $_FILES['image']['tmp_name'] );
            //$image->upload( 'image' );
            $image->resize(160);

            $ext = substr($_FILES['image']['name'],-3);
            $ext = strtolower ($name) ;
            $name = md5( time(), $ext );

            $image->save( DIR_UPLOAD . 'articles/' . substr($name , 0, 3) );
            $path = DIR_UPLOAD . 'articles/' . $name;
            $url = DIR_UPLOAD . 'articles/' . $name;
            $parent = $art->saveImageInfo( 0, $path , $url );
            $image->resize(50);



            $name = md5( time()+1, $ext );//навсякий. а вдруг время поменяется

            $image->save( DIR_UPLOAD . 'articles/' . substr($name , 0, 3) );
            $path2 = DIR_UPLOAD . 'articles/' . $name;
            $url2 = DIR_UPLOAD . 'articles/' . $name;
            $little = $art->saveImageInfo( $parent, $path2 , $url2 );

            die (json_encode( array (
                'id' => $parent,
                'link' => $url,
                //'child_id' => $little,
                'preview_link' => $url2,
            ) )) ;

            //die('fjsdfk');
            break;
    }
//$art = new Articles();
//$art->save($_POST);
//echo('<pre>');
//die(print_r($art));

?>
