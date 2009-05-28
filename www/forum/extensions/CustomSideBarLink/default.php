<?php

/*
~~~~~~~~~ DO NOT EDIT BELOW OR YOUR EXTENSION WILL NOT WORK ~~~~~~~~~~~~
Extension Name: CustomSideBarLink
Extension Url: http://daynelyons.com
Description: Add endless links to the sidebar of any page of your Forums
Version: 1.5.2
Author: Dayne Lyons
Author Url: http://daynelyons.com
~~~~~~~~~ DO NOT EDIT ABOVE OR YOUR EXTENSION WILL NOT WORK ~~~~~~~~~~~~
*/

    /* Give Your Side Bar Section a title */
$Context->Dictionary["TITLE"] = "Home-money";

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
	
$ListName = $Context->GetDefinition("TITLE");

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

/* Begin Link */

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  
if(in_array($Context->SelfUrl, array("index.php", "discussions.php", "categories.php", "search.php")) AND !empty($_SESSION['user'])) {
 $Panel->AddList($ListName, $Position = '0', $ForcePosition = '0');
 $Panel->AddListItem($ListName, "Добавить операцию", "http://home-money.ru/index.php?modules=operation&area=add" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Счета", "http://home-money.ru/index.php?modules=account" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Категории", "http://home-money.ru/index.php?modules=category" . "\" target=\"_self\" ");

 $Panel->AddListItem($ListName, "Транзакции", "http://home-money.ru/index.php?modules=periodic_transaction" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Бюджет", "http://home-money.ru/index.php?modules=budget" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Финансовые цели", "http://home-money.ru/index.php?modules=targets" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Отчёты", "http://home-money.ru/index.php?modules=report_wizard" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Экспорт", "http://home-money.ru/index.php?modules=export" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Импорт", "http://home-money.ru/index.php?modules=import" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Категории", "http://home-money.ru/index.php?modules=category" . "\" target=\"_self\" ");
} elseif(in_array($Context->SelfUrl, array("index.php", "discussions.php", "categories.php", "search.php"))) {
 $Panel->AddListItem($ListName, "Регистрация", "http://home-money.ru/index.php?modules=reg" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Инструкция", "http://www.home-money.ru/help/help.htm" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Вход", "https://www.home-money.ru/index.php?modules=login" . "\" target=\"_self\" ");
 $Panel->AddListItem($ListName, "Демо-вход", "http://demo.home-money.ru/index.php?modules=demo_registration&action=new_user" . "\" target=\"_self\" ");
}

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

/* End Link */
/* Begin Link */

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  
    /* Give your side bar link its name */
$Context->Dictionary["LINK2"] = "Link Name";

    /* Where is the link going */
$link_to2='http://example.net/folder/page.html';

    /* What page (or pages) is/are your link going on? */
if(in_array($Context->SelfUrl, array("page.php", "page.php")))

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
{
 $Panel->AddListItem($ListName, $Context->GetDefinition("LINK2"), $link_to2 .   
 "\" 
	target=\"_blank\" 
	style=\" padding-left: 27px; 
	background:url(http://example.com/graphics/icon.jpg) 
	no-repeat 5px center;\" ");
}

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

/* End Link */

?>
