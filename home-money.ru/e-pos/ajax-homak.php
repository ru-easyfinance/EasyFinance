<?php
/**
 * mobile Ц мобильна€ св€зь,
 * ip Ц IP-телефони€,
 * inet Ц провайдеры »нтернета,
 * tv Ц провайдеры коммерческого “¬,
 * commun Ц провайдеры коммунальных услуг,
 * other Ц провайдеры прочих услуг
 */

include_once 'conf.php';

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest") {

    $login = $e_pos_login;
    $pwd   = $e_pos_pass;

    $curl = true;
    $res = false;

    $op = false;


    if(isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'groupItems':
                $url = "http://www.e-pos.ru/getprovlist.php";
                $params = "group=" . $_POST['group'];
                break;
            case 'operatorProperties':
                $url = "http://www.e-pos.ru/getprovinfo.php";
                $params = "login=$login&pwdmd5=$pwd&opertype=new";
                $params .= "&operator=".$_POST['operator'];
                break;
            case 'postParam':
                $url = "http://www.e-pos.ru/RUR02/paystatus.php";
                $params = "login=$login&pwdmd5=$pwd&opertype=new&currency=RUR";
                $params.= "&operator=".$_POST['operator'];
                $params.= "&sum=".$_POST['sum'];
                $params.= "&account=".$_POST['account'];
                $params.= "&paycurr=".$_POST['paycurr'];
                
                $params.= "&opercode=1";

                // добавл€ем запись в Ѕƒ
                /*$res = @mysql_query("INSERT INTO `$db_tbl` (`opercode`, `date_create`) VALUES (NULL, NOW())");
                if($res) {
                    $params.="&opercode=".mysql_insert_id();
                }*/

                $op = true;

                break;
        }

        if($curl) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $res = curl_exec($ch);
            curl_close($ch);

            $res = trim($res);

            header("Content-type: text/xml; charset=windows-1251");
            print $res;
            //if($op) {
            if($fd=fopen("_ajax-homak.txt", 'w')) {
                fwrite($fd, "@$res@");
                fclose($fd);
            }
            //}
        }
    }
}
?>
