<?php
	// If the installed version is lower than 2.0 or new run this whole thing once
	if (empty($Configuration['WHISPERFI_INSTALL_COMPLETE'])) {
		$Errors = 0;
	
		// Create the User column WhisperNotification
		$result = mysql_query("SHOW columns FROM ".$Context->Configuration['DATABASE_TABLE_PREFIX']."User like 'WhisperNotification'");
		if (mysql_num_rows($result) == 0) {
			$WhisperfiCreate = "ALTER TABLE `".$Context->Configuration['DATABASE_TABLE_PREFIX']."User`
					 ADD `WhisperNotification` TINYINT( 1 ) DEFAULT 1;";
			if (!mysql_query($WhisperfiCreate, $Context->Database->Connection)) {
				$Errors = 1;
			}
		}
	
		// Create admin configuration settings which can then be controlled in the Extension Options -> Notification page under the Settings tab
		if (empty($Context->Configuration['WHISPERFI_AUTO_ALL'])) {
			AddConfigurationSetting($Context, 'WHISPERFI_AUTO_ALL', '0');
		}
		if (!$Errors) {
			AddConfigurationSetting($Context, 'WHISPERFI_INSTALL_COMPLETE', '1');
		}
	}

	// Check the Low-Cal Vanilla is installed
	if (!empty($Configuration['LOWCALVANILLA_TOOLS_PATH'])) {
		// Include Low-Cal Vanilla.
		require_once($Configuration['LOWCALVANILLA_TOOLS_PATH']);

		// Add Notifi to be gzipped
		LowCalVanilla_AddScript($Context, $Head, 'extensions/Whisperfi/functions.js');
	} else {
		$Head->AddScript('extensions/Whisperfi/functions.js');
	} 
?>