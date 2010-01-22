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
        $id = $args['id'];
        $ids = (string)$args['ides'];

        $image_id = $args['general_img'];

        $arrayId = explode( ';' , $ids );

        $date = (string)$args['date'];
        $title = (string)$args['title'];
        $description = (string)$args['meta_desc'];
        $keywords = (string)$args['meta_key'];
        $announce = strip_tags((string)$args['preview'], '<p>');
        $body = strip_tags((string)$args['text'], '<p><b><i><u><h3><h4><h5><h6><a><img><ul><li><span>');

        $status = 0;
        if ( $args['public'] )
            $status = 1;
        if (!$id){
            $sql = "INSERT INTO articles (date, title, description, keywords, announce, body, status, image_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $article = $this->db->query($sql, $date, $title, $description, $keywords, $announce, $body, $status, $image_id);
        }
        else{
            $sql = "UPDATE articles SET date=?, title=?, description=?, keywords=?, announce=?, body=?, status=?, image_id=? WHERE id=?";
            $article = $this->db->query($sql, $date, $title, $description, $keywords, $announce, $body, $status, $image_id, $id);
        }

        foreach ($arrayId as $k=>$v)
            if ($v != 0){
                if (!$id){
                    //$this->clearConnection($article);
                    $this->imageArticleConnection($v, $article);
                }
                else{
                    //$this->clearConnection($id);
                    $this->imageArticleConnection($v, $id);
                }
            }
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
        
        $sql = "SELECT id, url FROM images WHERE id IN (SELECT image_id FROM images_articles WHERE article_id=?)
            OR parent_id IN (SELECT image_id FROM images_articles WHERE article_id=?)";
        $imageArray = $this->db->query($sql, $id, $id);
        $imageRes = array();
        foreach ( $imageArray as $k=>$v ){
            if ( fmod($k, 2) == 0 ){
                $imageRes[ $v['id'] ]['link'] = $imageArray[$k]['url'];
                $imageRes[ $v['id'] ]['previewLink'] = $imageArray[$k+1]['url'];
                $number++;
            }
        }
        $sql = "SELECT a.image_id as id, i.url as link FROM articles a, images i WHERE a.id=? AND a.image_id=i.id";
        $imageMain = $this->db->query($sql, $id);
        $img = array(
            'id'=>$imageMain[0]['id'],
            'link'=>$imageMain[0]['link']
        );
        $ret = array(
            images => $imageRes,
            article => array(
                'id'=>$article[0]['id']
                ,'title'=>$article[0]['title']
                ,'img'=>$img
                ,'date'=>$article[0]['date']
                ,'author'=>$article[0]['authorName']
                ,'url'=>$article[0]['authorUrl']
                ,'meta_desc'=>$article[0]['description']
                ,'meta_key'=>$article[0]['keywords']
                ,'preview'=>$article[0]['announce']
                ,'text'=>$article[0]['body']
            )
        );
           // die(print_r($ret));
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

    function deleteImage( $args )
    {
        $id = $args['id'];
        $sql = "SELECT path FROM images WHERE id=? OR parent_id=?";
        $images = $this->db->query($sql, $id, $id);
        foreach ($images as $k=>$v){
            if ( !unlink($v['path']) )
                die('Не получилось удалить картинку');
        }
        $sql = "DELETE FROM images WHERE id=? OR parent_id=?";
        $images = $this->db->query($sql, $id, $id);
        $sql = "DELETE FROM images_articles WHERE image_id=? ";
        $images = $this->db->query($sql, $id);
    }

    function clearConnection( $id )
    {
        $sql = "DELETE FROM images_articles WHERE article_id=?";
        $this->db->query($sql, $id);
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

            $put = substr( $name , 0 , 2);

            @mkdir(DIR_UPLOAD . 'articles/' . $put . '/');
            $image->save( DIR_UPLOAD . 'articles/' . $put . '/' . $name .'.jpg' );
            $path = DIR_UPLOAD . 'articles/' . $put . '/' . $name .'.jpg';
            $url = 'http://' .DIR_UPLOAD . 'articles/' . $put . '/' . $name .'.jpg';
            $parent = $art->saveImageInfo( 0, $path , $url );
            $image->resize(50);



            $name = md5( time()+1, $ext );//навсякий. а вдруг время поменяется

            $image->save( DIR_UPLOAD . 'articles/' . $put . '/' . $name .'.jpg');
            $path2 = DIR_UPLOAD . 'articles/' . $put . '/' . $name .'.jpg';
            $url2 = 'http://' . DIR_UPLOAD . 'articles/' . $put . '/' . $name .'.jpg';
            $little = $art->saveImageInfo( $parent, $path2 , $url2 );

            die (json_encode( array (
                'id' => $parent,
                'link' => $url,
                //'child_id' => $little,
                'previewLink' => $url2,
            ) )) ;

            //die('fjsdfk');
            break;
        case "ImageDel":
            $art = new Articles();
            $art->deleteImage($_POST);
            die( array('result' => 'ok'));
            break;
    }
//$art = new Articles();
//$art->save($_POST);
//echo('<pre>');
//die(print_r($art));

?>
