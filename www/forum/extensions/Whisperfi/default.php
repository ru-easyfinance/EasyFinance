<?php
	/*
	Extension Name: Whisperfi
	Extension Url: subjunk@gmail.com
	Description: An extension that displays a notification at the top of the Discussions page when you have been whispered to inside a public discussion.
	Version: 1.0.2
	Author: Klaus Burton
	Author Url: http://www.redskiesdesign.com/
	*/

	// Include the file that handles installations and upgrades. Separated just for the sake of organisation.
	include('_includes/install.php');

	CreateArrayEntry($Context->Dictionary, 'WhisperfiUserSettings',   'Whisper Notification');
	CreateArrayEntry($Context->Dictionary, 'WhisperfiUserPreference', 'Display the "new whispers" notification');
	CreateArrayEntry($Context->Dictionary, 'AdminAutoAll',            'Force whisper notifications for all users');
	CreateArrayEntry($Context->Dictionary, 'WhisperfiSettings',       'Whisperfi Settings');
	CreateArrayEntry($Context->Dictionary, 'WhisperfiOptions',        'Options');
	CreateArrayEntry($Context->Dictionary, 'WhisperfiAdminNotes',     'This page controls how Whisperfi works on your forum');
	CreateArrayEntry($Context->Dictionary, 'NotificationManagement',  'Notification Management');
	CreateArrayEntry($Context->Dictionary, 'TellWhenWhisper',         'Tell me when someone whispers a comment to me');
	CreateArrayEntry($Context->Dictionary, 'YouHaveBeenWhisperedIn',  'You have been whispered in the following discussions:');
	CreateArrayEntry($Context->Dictionary, 'XNew',                    '//1'); // The number of new whispers you have in that discussion

	if ($Context->Configuration['ENABLE_WHISPERS']) {
		if (in_array($Context->SelfUrl, array('comments.php', 'index.php', 'categories.php'))) {
			if ($Context->Session->User->UserID > 0 && (whisperfiCheck($Context,'WhisperNotification') == 1 || $Context->Configuration['WHISPERFI_AUTO_ALL'])) {
				$s = $Context->ObjectFactory->NewContextObject($Context, 'SqlBuilder');
				$s->SetMainTable('Discussion', 't');
				$s->AddSelect(array('DiscussionID', 'Name'), 't');
				$s->AddJoin('Comment', 'co', 'DiscussionID', 't', 'DiscussionID', 'left join');
				$s->AddWhere('co', 'WhisperUserID', '', $Context->Session->User->UserID, '=');
				$s->AddSelect('CommentID', 'co');
				$s->AddSelect('CommentID', 'co', 'WhisperCount', 'count');
				$s->AddGroupBy('DiscussionID', 't');
				$s->AddJoin('UserDiscussionWatch', 'tw', 'DiscussionID', 't', 'DiscussionID', 'left join', 'and tw.'.$DatabaseColumns['UserDiscussionWatch']['UserID'].' = '.$Context->Session->User->UserID);
				$s->AddWhere('co', 'DateCreated', 'tw', 'LastViewed', '>', 'and', '', 0);
				$result = $Context->Database->Select($s, '', '', 'An error occurred while querying the database.');
				$countRows = $Context->Database->RowCount($result);
				if ($countRows != 0) {
					$msg = "";
					$showNotice = "no";
					while ($row = $Context->Database->GetRow($result)) {
						// Get the last viewed comment in this discussion
						$result = mysql_query("SELECT CountComments FROM ".$Context->Configuration['DATABASE_TABLE_PREFIX']."UserDiscussionWatch WHERE UserID = '".$Context->Session->User->UserID."' AND DiscussionID = '".$row['DiscussionID']."'",$Context->Database->Connection);
						$row2 = mysql_fetch_row($result);
						$lastViewedComment = $row2[0];
						
						// Work out which page and comment to take the user to
						$commentsPerPage = $Context->Configuration['COMMENTS_PER_PAGE'];
						$pageNumber = ceil($lastViewedComment/$commentsPerPage);
						$jumpToComment = $lastViewedComment-($pageNumber.'0'-10);

						$xnew = str_replace('//1', $row['WhisperCount'], $Context->GetDefinition('XNew'));
						
						// Initialise temp variables
						$tempDiscussionID = "";
						$tempPageNumber   = "";

						if (!empty($_GET['DiscussionID'])) {
							$tempDiscussionID = $_GET['DiscussionID'];
							if (!empty($_GET['page'])) {
								$tempPageNumber = $_GET['page'];
							}
						}
						if (!($tempDiscussionID == $row['DiscussionID'] && $tempPageNumber == $pageNumber)) {
							$msg .= '<br />' . '<a href="'.$Context->Configuration['BASE_URL'].'comments.php?DiscussionID='.$row['DiscussionID'].'&page='.$pageNumber.'#Item_'.$jumpToComment.'">'.$row['Name'].'</a> ('.$xnew.')';
							$showNotice = "yes";
						}
					}
					if ($showNotice == "yes") {
						$NoticeCollector->AddNotice($Context->GetDefinition('YouHaveBeenWhisperedIn').$msg);
					}
				}
			}
		}
	}

	function whisperfiCheck($Context,$Target) {
		$result = mysql_query("SELECT $Target FROM ".$Context->Configuration['DATABASE_TABLE_PREFIX']."User WHERE UserID = '".$Context->Session->UserID."'",$Context->Database->Connection);
		$row = mysql_fetch_row($result);
		return $row[0];
	}

	function whisperfiSwitch($Context,$Switch,$UserID,$Target) {
		mysql_query("UPDATE `".$Context->Configuration['DATABASE_TABLE_PREFIX']."User` SET $Target = $Switch WHERE UserID = $UserID",$Context->Database->Connection);
	}

	if (in_array($Context->SelfUrl, array('account.php'))) {
		if (!@$UserManager) {
			unset($UserManager);
		}
		$UserManager = $Context->ObjectFactory->NewContextObject($Context, "UserManager");
		$AccountUserID = ForceIncomingInt("u", $Context->Session->UserID);
		if (!@$AccountUser) {
			$AccountUser = $UserManager->GetUserById($AccountUserID);
		}
		if ($Context->Session->User) {
			if (($AccountUser->UserID == $Context->Session->UserID OR $Context->Session->User->Permission("PERMISSION_EDIT_USERS")) AND $Context->Configuration['WHISPERFI_AUTO_ALL'] == 0) {
				if (isset($_GET['u'])) {
					if ($_GET['u'] == $AccountUser->UserID) {
						include('_includes/usersettings.php');
						$Panel->AddListItem($Context->GetDefinition('AccountOptions'), $Context->GetDefinition('WhisperfiUserSettings'), GetUrl($Configuration, $Context->SelfUrl, "", "", "", "", "u=".ForceIncomingInt('u',$Context->Session->UserID)."&amp;PostBackAction=Whisperfi"), "", "", 92);
						$Page->AddRenderControl($Context->ObjectFactory->NewContextObject($Context, "WhisperfiControl"), $Configuration["CONTROL_POSITION_BODY_ITEM"]);
					}
				} else {
					include('_includes/usersettings.php');
					$Panel->AddListItem($Context->GetDefinition('AccountOptions'), $Context->GetDefinition('WhisperfiUserSettings'), GetUrl($Configuration, $Context->SelfUrl, "", "", "", "", "u=".ForceIncomingInt('u',$Context->Session->UserID)."&amp;PostBackAction=Whisperfi"), "", "", 92);
					$Page->AddRenderControl($Context->ObjectFactory->NewContextObject($Context, "WhisperfiControl"), $Configuration["CONTROL_POSITION_BODY_ITEM"]);
				}
			}
		}
		$Head->AddStyleSheet('extensions/Whisperfi/style.css');
	}

	if ($Context->SelfUrl == "settings.php" && $Context->Session->User->Permission('PERMISSION_MANAGE_EXTENSIONS')) {
		include('_includes/adminsettings.php');
	}
?>