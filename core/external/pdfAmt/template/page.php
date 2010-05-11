<?php
    if (!$birth_date_split) {
        $birth_date_split = array('','','');
    } else {
        $birth_date_split=explode(".", $birth_date);
    }

    function renderLetters($string)
    {
        for ($i=0, $n=mb_strlen($string); $i<$n; $i++) {
            echo '<span style="padding-right:23px;">'.mb_substr($string, $i, 1).'</span>';
        }
    }
?>

<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<body style="padding:0px; font-family:Arial; font-size:15px;">
<img src="<?php echo dirname(__FILE__); ?>/back.jpg" />

<div style="position:absolute; top: 230px; left:240px;"><b>
<?php renderLetters($lname); ?>
</b></div>
<div style="position:absolute; top: 247px; left:240px;"><b>
<?php renderLetters($fname); ?>
</b></div>
<div style="position:absolute; top: 264px; left:240px;"><b>
<?php renderLetters($mname); ?>
</b></div>
<div style="position:absolute; top: 230px; left:745px;"><b>
<?php
$i=0;
while ($i<mb_strlen($lname_lat)) {
echo '<span style="padding-right:23px;">'.$lname_lat{$i}.'</span>';
$i++;
};
 ?>
</b></div>
<div style="position:absolute; top: 247px; left:745px;"><b>
<?php
$i=0;
while ($i<mb_strlen($fname_lat)) {
echo '<span style="padding-right:23px;">'.$fname_lat{$i}.'</span>';
$i++;
};
 ?>
</b></div>
<div style="position:absolute; top: 304px; left:290px;"><b>
<?php
$i=0;
while ($i<mb_strlen($birth_date_split[0])) {
echo '<span style="padding-right:23px;">'.$birth_date_split[0]{$i}.'</span>';
$i++;
};
 ?>
</b></div>
<div style="position:absolute; top: 304px; left:395px;"><b>
<?php
$i=0;
while ($i<mb_strlen($birth_date_split[1])) {
echo '<span style="padding-right:23px;">'.$birth_date_split[1]{$i}.'</span>';
$i++;
};
 ?>
</b></div>
<div style="position:absolute; top: 304px; left:503px;"><b>
<?php
$i=0;
while ($i<mb_strlen($birth_date_split[2])) {
echo '<span style="padding-right:23px;">'.$birth_date_split[2]{$i}.'</span>';
$i++;
};
 ?>
</b></div>
<div style="position:absolute; top: 323px; left:240px;"><b><?php echo $birth_place ?></b></div>
<?php if (mb_strtolower($gender)=='мужской') { ?><div style="position:absolute; top: 342px; left:350px;"><b>x</b></div><?php }; ?>
<?php if (mb_strtolower($gender)=='женский') { ?><div style="position:absolute; top: 342px; left:550px;"><b>x</b></div><?php }; ?>
<div style="position:absolute; top: 304px; left:820px;"><b><?php echo $citizenship ?></b></div>
<div style="position:absolute; top: 323px; left:820px;"><b><?php echo $inn ?></b></div>

<div style="position:absolute; top: 385px; left:197px;"><b><?php echo $country ?></b></div>
<div style="position:absolute; top: 385px; left:510px;"><b><?php echo $zip ?></b></div>
<div style="position:absolute; top: 405px; left:197px;"><b><?php echo $region ?></b></div>
<div style="position:absolute; top: 440px; left:197px;"><b><?php echo $city ?></b></div>
<div style="position:absolute; top: 491px; left:152px;"><b><?php echo $street ?></b></div>
<div style="position:absolute; top: 528px; left:152px;"><b><?php echo $house ?></b></div>
<div style="position:absolute; top: 528px; left:332px;"><b><?php echo $building ?></b></div>
<div style="position:absolute; top: 528px; left:532px;"><b><?php echo $app ?></b></div>

<div style="position:absolute; top: 600px; left:197px;"><b><?php echo $country_reg ?></b></div>
<div style="position:absolute; top: 600px; left:510px;"><b><?php echo $zip_reg ?></b></div>
<div style="position:absolute; top: 630px; left:197px;"><b><?php echo $region_reg ?></b></div>
<div style="position:absolute; top: 665px; left:197px;"><b><?php echo $city_reg ?></b></div>
<div style="position:absolute; top: 716px; left:197px;"><b><?php echo $street_reg ?></b></div>
<div style="position:absolute; top: 754px; left:152px;"><b><?php echo $house_reg ?></b></div>
<div style="position:absolute; top: 754px; left:332px;"><b><?php echo $building_reg ?></b></div>
<div style="position:absolute; top: 754px; left:532px;"><b><?php echo $app_reg ?></b></div>

<div style="position:absolute; top: 385px; left:820px;"><b><?php echo $passport_serie ?></b></div>
<div style="position:absolute; top: 385px; left:1000px;"><b><?php echo $passport_number ?></b></div>
<div style="position:absolute; top: 440px; left:820px; width:400px;"><b><?php echo $passport_given ?></b></div>
<div style="position:absolute; top: 491px; left:820px;"><b><?php echo $passport_code ?></b></div>
<div style="position:absolute; top: 491px; left:1085px;"><b><?php echo $passport_date ?></b></div>

<div style="position:absolute; top: 600px; left:790px;"><b><?php echo $doc_title ?></b></div>
<div style="position:absolute; top: 630px; left:790px;"><b><?php echo $doc_serie ?></b></div>
<div style="position:absolute; top: 630px; left:1015px;"><b><?php echo $doc_number ?></b></div>
<div style="position:absolute; top: 665px; left:790px; width:400px;"><b><?php echo $doc_given ?></b></div>
<div style="position:absolute; top: 716px; left:790px;"><b><?php echo $doc_date ?></b></div>
<div style="position:absolute; top: 754px; left:865px;"><b><?php echo $doc_valid ?></b></div>


<div style="position:absolute; top: 805px; left:290px;"><b><?php echo $contact_phone ?></b></div>
<div style="position:absolute; top: 805px; left:865px;"><b><?php echo $contact_mobile ?></b></div>
<div style="position:absolute; top: 821px; left:290px;"><b><?php echo $contact_email ?></b></div>
<div style="position:absolute; top: 821px; left:865px;"><b><?php echo $contact_other ?></b></div>

<div style="position:absolute; top: 860px; left:200px; width:400px;"><b><?php echo $work_company ?></b></div>
<div style="position:absolute; top: 860px; left:850px; width:400px;"><b><?php echo $work_address ?></b></div>
<div style="position:absolute; top: 892px; left:200px; width:400px;"><b><?php echo $work_title ?></b></div>
<div style="position:absolute; top: 892px; left:850px; width:400px;"><b><?php echo $work_phone ?></b></div>

<?php if ((bool)$card_mode === true) { ?><div style="position:absolute; top: 940px; left:310px;"><b>v</b></div><?php }; ?>
<?php if ((bool)$card_mode === false) { ?><div style="position:absolute; top: 940px; left:590px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_currency===0) { ?><div style="position:absolute; top: 940px; left:830px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_currency===1) { ?><div style="position:absolute; top: 940px; left:1005px;"><b></b></div><?php }; ?>
<?php if ((int)$card_currency===2) { ?><div style="position:absolute; top: 940px; left:1190px;"><b>v</b></div><?php }; ?>
<?php if (in_array((int)$card_type, array(0,1,2,3))) { ?><div style="position:absolute; top: 970px; left:355px;"><b>v</b></div><?php }; ?>
<?php if (in_array((int)$card_type, array(4,5))) { ?><div style="position:absolute; top: 970px; left:978px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_type === 0) { ?><div style="position:absolute; top: 1003px; left:170px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_type === 1) { ?><div style="position:absolute; top: 1003px; left:318px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_type === 2) { ?><div style="position:absolute; top: 1003px; left:485px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_type === 3) { ?><div style="position:absolute; top: 1003px; left:605px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_type === 4) { ?><div style="position:absolute; top: 1003px; left:870px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_type === 5) { ?><div style="position:absolute; top: 1003px; left:1210px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_urgency === 0) { ?><div style="position:absolute; top: 1035px; left:590px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_urgency === 1) { ?><div style="position:absolute; top: 1035px; left:1210px;"><b>v</b></div><?php }; ?>
<?php if ((int)$card_sms === 1) { ?><div style="position:absolute; top: 1070px; left:830px;"><b>v</b></div><?php }; ?>
<?php if (trim($card_receipt_office!='')) { ?><div style="position:absolute; top: 1110px; left:590px;"><b>v</b></div><?php }; ?>
<?php if (trim($card_email!='')) { ?><div style="position:absolute; top: 1102px; left:864px;"><b><?php echo str_replace("@mail.easyfinance.ru", "", $card_email); ?></b></div><?php }; ?>

<div style="position:absolute; top: 1198px; left:455px;"><b><?php echo $add_name ?></b></div>
<div style="position:absolute; top: 1233px; left:320px;"><b>
<?php
$i=0;
while ($i<mb_strlen($add_number)) {
    echo '<span style="padding-right:35px;">'.$add_number{$i}.'</span>';
    $i++;
};
?>
</b></div>
<div style="position:absolute; top: 1270px; left:320px;"><b><?php echo $add_limit ?></b></div>

<div style="position:absolute; top: 1157px; left:100px;"><b>
<?php
for ($i=0, $n=mb_strlen($password); $i<$n; $i++) {
    echo '<span style="padding-right:50px;">'.mb_substr($password, $i, 1).'</span>';
}
?>
</b></div>

<?php if ((int)$add_14_type === 0) { ?><div style="position:absolute; top: 1395px; left:100px;"><b>v</b></div><?php }; ?>
<?php if ((int)$add_14_type === 1) { ?><div style="position:absolute; top: 1395px; left:520px;"><b>v</b></div><?php }; ?>
<?php if ((int)$add_14_type === 2) { ?><div style="position:absolute; top: 1395px; left:940px;"><b>v</b></div><?php }; ?>
<div style="position:absolute; top: 1423px; left:280px;"><b><?php echo $add_14_given ?></b></div>

<!-- #1313. вторая страница с памяткой -->
<!--NewPage-->
<div style="page-break-after: always; position:absolute; top: 1900px; left: 100px;">
    ВТОРАЯ СТРАНИЦА
</div>

</body>
</html>
