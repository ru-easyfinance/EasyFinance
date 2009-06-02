<?php
	session_start();
	
	$UserID = $_SESSION['LussumoUserID'];
	if(!$UserID) {
		if(!isset($_SESSION['LussumoReadMsgs'])) {
			$_SESSION['LussumoReadMsgs'] = array();
		}
		$_SESSION['LussumoReadMsgs'][] = $_POST['MsgID'];
		exit;
	}
	
	require_once('../../appg/settings.php');
	require_once('../../conf/settings.php');
	require_once('../../conf/database.php');
	
	$MsgID = $_POST['MsgID'];
	$Read = $_POST['Read'];
	
	if(!$Read) $Read = serialize(array($UserID));
	else {
		$Read = unserialize($Read);
		$Read[] = $UserID;
		$Read = serialize($Read);
	}
	
	mysql_pconnect(
		$Configuration['DATABASE_HOST'],
		$Configuration['DATABASE_USER'],
		$Configuration['DATABASE_PASSWORD']
	);
	mysql_select_db($Configuration['DATABASE_NAME']);
	
	$Query = "
		UPDATE `$Configuration[DATABASE_TABLE_PREFIX]SysMsg`
		SET `Read`='$Read'
		WHERE `MsgID`='$MsgID'
	";
	mysql_query($Query);
?>