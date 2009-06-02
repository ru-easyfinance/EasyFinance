<?php
	$Context->SetDefinition('ExtensionOptions', 'Extension Options');
	class WhisperfiForm extends PostBackControl {
		var $ConfigurationManager;

		function WhisperfiForm(&$Context) {
			$this->Name = 'WhisperfiForm';
			$this->ValidActions = array('Whisperfi', 'ProcessWhisperfi');
			$this->Constructor($Context);
			if (!$this->Context->Session->User->Permission('PERMISSION_MANAGE_EXTENSIONS')) {
				$this->IsPostBack = 0;
			} elseif ($this->IsPostBack) {
				$SettingsFile = $this->Context->Configuration['APPLICATION_PATH'].'conf/settings.php';
				$this->ConfigurationManager = $this->Context->ObjectFactory->NewContextObject($this->Context, 'ConfigurationManager');
				if ($this->PostBackAction == 'ProcessWhisperfi') {
					$this->ConfigurationManager->GetSettingsFromForm($SettingsFile);
					$this->ConfigurationManager->DefineSetting('WHISPERFI_AUTO_ALL',         ForceIncomingBool('WHISPERFI_AUTO_ALL',         0), 0);
					if ($this->ConfigurationManager->SaveSettingsToFile($SettingsFile)) {
						header('Location: '.GetUrl($this->Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=Whisperfi&Success=1'));
					} else {
						$this->PostBackAction = 'Whisperfi';
					}
				}
			}
			$this->CallDelegate('Constructor');
		}

		function Render() {
			if ($this->IsPostBack) {
				$this->CallDelegate('PreRender');
				$this->PostBackParams->Clear();
				if ($this->PostBackAction == 'Whisperfi') {
					$this->PostBackParams->Set('PostBackAction', 'ProcessWhisperfi');
					echo '<div id="Form" class="Account WhisperfiSettings">';
					if (ForceIncomingInt('Success', 0)) {
						echo '<div id="Success">'.$this->Context->GetDefinition('ChangesSaved').'</div>';
					}
					echo '
		     <fieldset>
			<legend>'.$this->Context->GetDefinition("WhisperfiSettings").'</legend>
			'.$this->Get_Warnings().'
			'.$this->Get_PostBackForm('frmWhisperfi').'
			<p>'.$this->Context->GetDefinition("WhisperfiAdminNotes").'</p>
			<ul>
			   <li>
			      <p><span>'.GetDynamicCheckBox('WHISPERFI_AUTO_ALL', 1, $this->ConfigurationManager->GetSetting('WHISPERFI_AUTO_ALL'), '', $this->Context->GetDefinition('AdminAutoAll')).'</span></p>
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
				$this->CallDelegate('PostRender');
			}
		}
	}

	$WhisperfiForm = $Context->ObjectFactory->NewContextObject($Context, 'WhisperfiForm');
	$Page->AddRenderControl($WhisperfiForm, $Configuration["CONTROL_POSITION_BODY_ITEM"] + 1);

	$ExtensionOptions = $Context->GetDefinition('ExtensionOptions');
	$Panel->AddList($ExtensionOptions, 20);
	$Panel->AddListItem($ExtensionOptions, $Context->GetDefinition('Whisperfi'), GetUrl($Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=Whisperfi'));
?>