<?php
	if ($Configuration['WHISPERFI_AUTO_ALL'] == '0') {
		class WhisperfiControl extends PostBackControl {
			var $Context;

			function WhisperfiControl($Context) {
				$this->ValidActions = array("Whisperfi");
				$this->Constructor($Context);
				$this->Context = $Context;
			}

			function Render() {
				if ($this->IsPostBack) {
					$u = $this->Context->Session->UserID;
					echo '
						<div id="Form" class="Account Preferences Whisperfications">
							<form method="post" action="">
								<fieldset>
									<legend>'.$this->Context->GetDefinition("WhisperfiUserSettings").'</legend>
									<p class="Description"><strong>Changes will be made <strong style="color:#c00;">instantly</strong> when you check/uncheck the boxes.<br />There is <strong style="color:#c00;">no submit button</strong></strong></p>
									<h2>'.$this->Context->GetDefinition("WhisperfiOptions").'</h2>
									<ul>
										<li>';
											$Active = ' ';
											if (whisperfiCheck($this->Context,'WhisperNotification') == 1) {
												$Active = 'checked="checked" ';
											}
											echo '
											<p id="WhisperfiCont"'.$SubscribedEntireForum.$SubscribedComment.'>
												<span>
													<label for="WhisperfiField">
														<input type="checkbox" value="1" id="WhisperfiField" '.$Active.' onclick="Whisperfi();" /> '.$this->Context->GetDefinition("WhisperfiUserPreference").'
													</label>
												</span>
											</p>
										</li>
									</ul>
								</fieldset>
							</form>
						</div>
					';
				}
			}
		}
	}
?>