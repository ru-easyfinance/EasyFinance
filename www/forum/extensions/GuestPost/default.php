<?php
/*
Extension Name: Guest Post
Extension Url: http://lussumo.com/addons/index.php?PostBackAction=AddOn&AddOnID=182
Description: Adds ability for users to post as a Guest user, with reCAPTCHA for spam prevention if wanted. Also adds a username/password box for unauthenticated users to login and post at the same time.
Version: 1.5
Author: Andrew Miller (Spode)
Author Url: http://www.spodesabode.com
*/

/*
Original extension written by Gerrard Cowburn, which was based on Mark O'Sullivan's "Add Comment" Extension.
*/

/*
Please read the README file before modifying this document.
*/

//Replace "Guest" with your guest account username (keep the single quotes).
define('GuestUsername', 'Гость');
//Replace "guest" with your guest account password (keep the single quotes).
define('GuestPassword', '');
//Do you want to use reCAPTCHA?  1 for yes, 0 for no.
define('GuestPostCaptcha','0');

//Replace "key" with your Public and Private Keys that you were given when you signed up on reCAPTCHA (keep the single quotes).
define('PublicKey', '6Ld4fQYAAAAAAKcUO5dD0s28urdjXT8iKIYtINXX');
define('PrivateKey', '6Ld4fQYAAAAAAPyC3us73V0Kb_ZNCtd5EHTTatYt');

//Feel free to change the message presented to the user.
$Context->SetDefinition('GuestPostWarning', 'Авторизируйтесь. Иначе, автором ваших комментариев будет "Гость".');

// Extension path
$GuestPath = dirname(__FILE__);

// Libraries
include($GuestPath . '/library/Function.GuestPost.php');

//check if it's been included by another extension first, to play nice with the reCAPTCHA extension
if (!function_exists('_recaptcha_qsencode')) {require_once($GuestPath . '/recaptcha-php-1.10/recaptchalib.php');}

if 	(in_array($Context->SelfUrl, array('comments.php', 'post.php')))
	{
	$GU = GuestUsername;
	$GP = GuestPassword;

	$Password = ForceIncomingString('Password', $GP);

	if	($Password == $GP)
		{
		$Username = $GU;
		}
	else
		{
		$Username = ForceIncomingString('Username','');
		}


	$Context->AddToDelegate('CommentGrid', 'Constructor', 'CommentGrid_ShowGuestPostForm');

	if 	($Context->Session->UserID <= 0)
		{
		$Head->AddStyleSheet('extensions/GuestPost/style.css');
		$Context->AddToDelegate('DiscussionForm', 'CommentForm_PreWhisperInputRender', 'CommentForm_AddGuestPostInfo');
		$Context->AddToDelegate('DiscussionForm', 'PreSaveComment', 'DiscussionForm_SignInGuest');
         	$Context->AddToDelegate('DiscussionForm','PostSaveComment','DiscussionForm_SignOutGuest');
		}
	}
?>
