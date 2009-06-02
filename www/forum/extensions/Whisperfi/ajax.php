<?php
	include('../../appg/settings.php');
	include('../../conf/settings.php');
	include('../../appg/init_vanilla.php');

	$PostBackAction = ForceIncomingString('PostBackAction','');
	$Type           = ForceIncomingString('Type', '');
	$ElementID      = ForceIncomingInt('ElementID', 0);
	$Value          = ForceIncomingInt('Value',0);

	if ($PostBackAction == 'ChangeWhisperfi') {
		whisperfiSwitch($Context,$Value,$Context->Session->UserID,'WhisperNotification');
	}
	echo 'Complete';

	$Context->Unload();
?>