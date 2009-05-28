<?php
/*
Extension Name: Preset Avatars
Extension Url: http://software.twotoasts.de
Description: Allow users to choose an avatar from a predefined set.
Version: 1.0
Author: Christian Dywan
Author Url: http://software.twotoasts.de

* Copyright 2006 Matt Brown
* Copyright 2006-2007 Maurice Krijtenberg
* Copyright 2008 Christian Dywan <christian@twotoasts.de>
* This extension is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* See the file gpl.txt distributed with Vanilla for the full license text.

Version 1.0 - 2008-02-14
- Initial release, based on AccountPictures and AutoHide
*/

// Make sure this file was not accessed directly
// and prevent register_globals configuration array attack
if (!defined('IN_VANILLA')) exit();

include('language.php');

CreateArrayEntry($Configuration, 'PERMISSION_USE_CUSTOM_AVATAR', 0);
CreateArrayEntry($Configuration, 'PERMISSION_USE_PRESET_AVATAR', 0);

// Initialize settings
if (!array_key_exists('PRESETAVATARS_SETUP', $Configuration)) {
        AddConfigurationSetting($Context, 'PRESETAVATARS_ICON_WIDTH', '32');
        AddConfigurationSetting($Context, 'PRESETAVATARS_ICON_HEIGHT', '32');
        AddConfigurationSetting($Context, 'PRESETAVATARS_MAX_FILESIZE', '512000');
        AddConfigurationSetting($Context, 'PRESETAVATARS_SETUP', '1');
}

if (isset($Head) ) {
	$AddStyle  = "
		.ProfileTitle.WithIcon .ProfileIcon { width: {IconWidth}px; height: {IconHeight}px; }
		#AccountProfile .Picture { width: {IconWidth}px; height: {IconHeight}px; }
		#Comments .CommentHeader { line-height: {IconHeight}px; }
		#Comments .CommentHeader li div.CommentIcon { background: transparent center center no-repeat; padding: {IconHeight}px 0px {IconHeight}px {IconWidth}px; }
		li.User.Name .UserIcon { padding: {IconHeight}px 0px {IconHeight}px {IconWidth}px !important; }
		li.User.Name.WithIcon { line-height: {IconHeight}px; }
	";
	$AddStyle = str_replace('{IconWidth}',		$Context->Configuration['PRESETAVATARS_ICON_WIDTH'], $AddStyle);
	$AddStyle = str_replace('{IconHeight}',		$Context->Configuration['PRESETAVATARS_ICON_HEIGHT'], $AddStyle);
	$Head->AddString("<style type=\"text/css\">".$AddStyle."</style>");
}

// Register our group permissions.
function PresetAvatars_AddRolePermissionKeys($Role) {
    $Role->AddPermission('PERMISSION_USE_CUSTOM_AVATAR');
    $Role->AddPermission('PERMISSION_USE_PRESET_AVATAR');
}
$Context->AddToDelegate('Role','DefineRolePermissions', 'PresetAvatars_AddRolePermissionKeys');

if ($Context->SelfUrl == 'account.php' && $Context->Session->UserID > 0) {

	class PresetAvatarsForm extends PostBackControl {
		var $UserID;
		var $IconWidth;
		var $IconHeight;
                var $PictureWidth;
		var $PictureHeight;
		var $DisplayIcon;
		var $BaseUrl;
                var $BaseFolder;
                var $MaxFilesize;

		function PresetAvatarsForm(&$Context) {
			$this->Name = 'PresetAvatarsForm';
			$this->ValidActions = array('PresetAvatars', 'ProcessPresetAvatars');
			$this->Constructor($Context);

			$this->UserID = $this->Context->Session->UserID;
			$this->IconWidth = $this->Context->Configuration['PRESETAVATARS_ICON_WIDTH'];
			$this->IconHeight = $this->Context->Configuration['PRESETAVATARS_ICON_HEIGHT'];
			$this->PictureWidth = $this->Context->Configuration['PRESETAVATARS_ICON_WIDTH'];
			$this->PictureHeight = $this->Context->Configuration['PRESETAVATARS_ICON_HEIGHT'];
                        $this->BaseUrl = $Context->Configuration['BASE_URL'].'extensions/'.basename(dirname(__FILE__ ));
                        $this->BaseFolder = $Context->Configuration['APPLICATION_PATH'].'extensions/'.basename(dirname(__FILE__ )).'/';
                        $this->MaxFilesize = $this->Context->Configuration['PRESETAVATARS_MAX_FILESIZE'];

			if ($this->IsPostBack) {
				$UserManager = $Context->ObjectFactory->NewContextObject($Context, 'UserManager');
				$User = $UserManager->GetUserById($this->UserID);
				if( $this->PostBackAction == 'ProcessPresetAvatars' ) {
                                        // Preset avatar
                                        $Choice = $_POST['AvatarImage'];
                                        if ($Choice == '_custom_') {
                                            // Custom avatar
                                            $FileName = $this->UploadImage('CustomAvatar', $this->UserID);
                                            if( $FileName != '' && $this->Context->WarningCollector->Count() == 0 ) {
                                                    $User->Icon = $this->BaseUrl.'/custom/'.$FileName;
                                            }
                                            else {
                                                    $User->Icon = '';
                                            }
                                        }
                                        else if ($Choice != '_none_' && substr($Choice, 0, 2) != '..') {
                                            $User->Icon = $this->BaseUrl.'/avatars/'.$Choice;
                                        }
                                        else {
                                            $User->Icon = '';
                                        }
                                        $UserManager->SaveIdentity($User);

				}
				$this->DisplayIcon = $User->Icon ? $User->Icon.'?'.time() : '';
			}

			$this->CallDelegate('Constructor');
		}

		function UploadImage($InputName, $UserID) {
			if (array_key_exists($InputName, $_FILES)) {
				$FileName = basename($_FILES[$InputName]['name']);
				$FilePieces = explode('.', $FileName);
				$FileExtension = $FilePieces[count($FilePieces)-1];
				if ($FileName != '') {
					$Uploader = $this->Context->ObjectFactory->NewContextObject($this->Context, "Uploader");
					$Uploader->MaximumFileSize = $this->MaxFilesize;
					$Uploader->AllowedFileTypes = array(
						'image/gif'		=> array('gif', 'GIF'),
						'image/png'		=> array('png', 'PNG'),
						'image/jpeg'	=> array('jpg', 'jpeg', 'JPG', 'JPEG'),
						'image/pjpeg'	=> array('jpg', 'jpeg', 'JPG', 'JPEG'),
					);
					return $Uploader->Upload($InputName, $this->BaseFolder.'custom', md5($InputName . $UserID).'.'.strtolower($FileExtension), '0', '1');
				}
			}
		}

		function Render() {
			if ($this->IsPostBack) {
				$this->CallDelegate('PreRender');
				$this->PostBackParams->Set('PostBackAction', 'ProcessPresetAvatars');
				$this->PostBackParams->Set('u', $this->UserID);
				echo '
				<div id="Form" class="Account PresetAvatars">
				<fieldset>
					<legend>'.$this->Context->GetDefinition('ChangePresetAvatars').'</legend>';
				$this->CallDelegate('PreWarningsRender');
				echo $this->Get_Warnings()
					.$this->Get_PostBackForm('frmPresetAvatars', 'post', $this->Context->SelfUrl, 'multipart/form-data');
				$this->CallDelegate('PreInputsRender');
				echo '
					<h2>'.$this->Context->GetDefinition('UserAvatar').'</h2>
                                                <p class="Description">'.$this->Context->GetDefinition('AvatarUsageNotes').'</p>
                                                <p class="Description">
                                                        '.$this->Context->GetDefinition('CurrentAvatar').': ';
                                echo ($this->DisplayIcon ?
                                '<div style="background: url(\''.$this->DisplayIcon.
                                '\') center center no-repeat; width: '.$this->IconWidth.
                                'px; height: '. $this->IconHeight .'px"></div>' :
                                '<p>&nbsp; '.$this->Context->GetDefinition('NoAvatar').'</p>');
                                echo '
                                                </p>
                                        <h2>'.$this->Context->GetDefinition('ChooseAvatar').'</h2>
                                        <ul>
                                                <li>
                                                        <input type="radio" name="AvatarImage" value="_none_" style="width: auto;" /> '.$this->Context->GetDefinition('NoAvatar').'
                                                </li>
                                                <li>
                                        ';

                                if($this->Context->Session->User->Permission('PERMISSION_USE_PRESET_AVATAR')) {

                                if ($Images = opendir($this->BaseFolder.'avatars'))
                                while ($FileName = readdir($Images))
                                        if ($FileName != '.' && $FileName != '..')
                                        echo '<span style=" white-space: nowrap;"><input type="radio" name="AvatarImage" value="'.$FileName.'" style="width: auto;" /> <img src="'.$this->BaseUrl.'/avatars/'.$FileName.'" /></span> &nbsp; ';
                                echo '
                                                </li>
                                        </ul>
                                ';
                                }

                                if($this->Context->Session->User->Permission('PERMISSION_USE_CUSTOM_AVATAR')) {

                                if (is_writable($this->BaseFolder.'custom')) {
                                        echo '
                                                <ul>
                                                	<li>
                                                        <input type="radio" name="AvatarImage" value="_custom_" style="width: auto;" /> '.$this->Context->GetDefinition('CustomAvatar').'
                                                	<input type="file" name="CustomAvatar" value="" class="SmallInput" style="width: 300px;" id="CustomAvatarButton" />
                                                	 <p class="Description">'.str_replace(array('//1', '//2'), array($this->IconWidth, $this->IconHeight), $this->Context->GetDefinition('CustomAvatarNotes')).'</p>
                                                	</li>
                                                </ul>
                                                ';
                                }
                                }

                                echo '
                                        <div class="Submit">
                                                	<input type="submit" name="Save" value="'.$this->Context->GetDefinition('Save').'" class="Button SubmitButton" />
                                                	<a href="'.GetUrl($this->Context->Configuration, 'account.php').'" class="CancelButton">'.$this->Context->GetDefinition('Cancel').'</a>
                                        </div>
                                        </form>
				</fieldset>
				</div>
				';

				$this->CallDelegate('PostRender');
			}
		}
	}

	$PresetAvatarsForm = $Context->ObjectFactory->NewContextObject($Context, 'PresetAvatarsForm');
	$Page->AddRenderControl($PresetAvatarsForm, $Configuration['CONTROL_POSITION_BODY_ITEM'] + 1);

        if($Context->Session->User->Permission('PERMISSION_USE_PRESET_AVATAR')
        || $Context->Session->User->Permission('PERMISSION_USE_CUSTOM_AVATAR')) {
	$AccountOptions = $Context->GetDefinition('AccountOptions');
	$Panel->AddList($AccountOptions, 10);
	$Panel->AddListItem($AccountOptions, $Context->GetDefinition('ChangePresetAvatars'), GetUrl($Context->Configuration, 'account.php', '', '', '', '', 'PostBackAction=PresetAvatars'));
        }

}

if ($Context->SelfUrl == "settings.php" && $Context->Session->User->Permission('PERMISSION_CHANGE_APPLICATION_SETTINGS')) {

        // This control presents auto-hide tips to administrators on
        // the form for editing roles.
        class PresetAvatarsRoleEditNotice extends PostBackControl {

            function PresetAvatarsRoleEditNotice(&$Context) {
                $this->Name = 'RoleEditNotice';
                $this->Constructor($Context);
            }

            function Render() {
                if ($this->PostBackAction != "Role") {
                return;
                }
            $NoticeCollector = $this->Context->ObjectFactory->NewContextObject(
             $this->Context,'NoticeCollector');
            $NoticeCollector->Render();
            }

        }
        $RoleEditNotice = $Context->ObjectFactory->NewContextObject($Context, 'PresetAvatarsRoleEditNotice');
        $Page->AddRenderControl($RoleEditNotice, $Configuration["CONTROL_POSITION_BODY_ITEM"] + 1);

	class PresetAvatarsSettingsForm extends PostBackControl {
		var $ConfigurationManager;

		function PresetAvatarsSettingsForm(&$Context) {
			$this->Name = 'PresetAvatarsSettingsForm';
			$this->ValidActions = array('PresetAvatars', 'ProcessPresetAvatars');
			$this->Constructor($Context);
			if (!$this->Context->Session->User->Permission('PERMISSION_CHANGE_APPLICATION_SETTINGS')) {
				$this->IsPostBack = 0;
			} elseif( $this->IsPostBack ) {
				$SettingsFile = $this->Context->Configuration['APPLICATION_PATH'].'conf/settings.php';
				$this->ConfigurationManager = $this->Context->ObjectFactory->NewContextObject($this->Context, 'ConfigurationManager');
				if ($this->PostBackAction == 'ProcessPresetAvatars') {
					$this->ConfigurationManager->GetSettingsFromForm($SettingsFile);
					// And save everything
					if ($this->ConfigurationManager->SaveSettingsToFile($SettingsFile)) {
						header('location: '.GetUrl($this->Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=PresetAvatars&Success=1'));
					} else {
						$this->PostBackAction = 'PresetAvatars';
					}
				}
			}
			$this->CallDelegate('Constructor');
		}

		function Render() {
			if ($this->IsPostBack) {
				$this->CallDelegate('PreRender');
				$this->PostBackParams->Clear();
				if ($this->PostBackAction == 'PresetAvatars') {
					$this->PostBackParams->Set('PostBackAction', 'ProcessPresetAvatars');
					echo '
					<div id="Form" class="Account AttachmentSettings">';
					if (ForceIncomingBool('Success',0)) echo '<div id="Success">'.$this->Context->GetDefinition('ChangesSaved').'</div>';
					echo '
					<fieldset>
						<legend>'.$this->Context->GetDefinition("PresetAvatarsSettings").'</legend>
						'.$this->Get_Warnings().'
						'.$this->Get_PostBackForm('frmPresetAvatars').'
						<h2>'.$this->Context->GetDefinition("PresetAvatarsGeneralSettings").'</h2>
						<p>'.$this->Context->GetDefinition("PresetAvatarsGeneralSettingsNotes").'</p>
						<ul>
							<li>
								<label for="txtIconSize">'.$this->Context->GetDefinition("PresetAvatarsIconSize").'</label>
								<input type="text" name="PRESETAVATARS_ICON_WIDTH" id="txtIconWidth"  value="'.$this->ConfigurationManager->GetSetting('PRESETAVATARS_ICON_WIDTH').'" maxlength="200" class="SmallInput" style="width: 40px" />
								x <input type="text" name="PRESETAVATARS_ICON_HEIGHT" id="txtIconHeight"  value="'.$this->ConfigurationManager->GetSetting('PRESETAVATARS_ICON_HEIGHT').'" maxlength="200" class="SmallInput" style="width: 40px" /> px
							</li>
							<li>
								<label for="txtMaxFilesize">'.$this->Context->GetDefinition("PresetAvatarsMaxFilesize").'</label>
								<input type="text" name="PRESETAVATARS_MAX_FILESIZE" id="txtMaxFilesize"  value="'.$this->ConfigurationManager->GetSetting('PRESETAVATARS_MAX_FILESIZE').'" />
							</li>
						</ul>
						<div class="Submit">
							<input type="submit" name="btnSave" value="'.$this->Context->GetDefinition('Save').'" class="Button SubmitButton" />
							<a href="'.GetUrl($this->Context->Configuration, $this->Context->SelfUrl).'" class="CancelButton">'.$this->Context->GetDefinition('Cancel').'</a>
						</div>						
						</form>
					</fieldset>
					</div>';
				}
			}
			$this->CallDelegate('PostRender');
		}
	}

	$PresetAvatarsSettingsForm = $Context->ObjectFactory->NewContextObject($Context, 'PresetAvatarsSettingsForm');
	$Page->AddRenderControl($PresetAvatarsSettingsForm, $Configuration['CONTROL_POSITION_BODY_ITEM'] + 1);

	$ExtensionOptions = $Context->GetDefinition('ExtensionOptions');
	$Panel->AddList($ExtensionOptions, 10);
	$Panel->AddListItem($ExtensionOptions, $Context->GetDefinition('PresetAvatarsSettings'), GetUrl($Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=PresetAvatars'));

}
?>