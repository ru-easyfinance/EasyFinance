<?php
// Note: This file is included from the library/Vanilla/Vanilla.Control.Menu.php class.

echo '<div id="Wrapper"><div id="Session">';
	if ($this->Context->Session->UserID > 0) {
		echo str_replace('//1',	$this->Context->Session->User->Name, $this->Context->GetDefinition('SignedInAsX'))
			. ' (<a href="'
			. FormatStringForDisplay(AppendUrlParameters(
				$this->Context->Configuration['SIGNOUT_URL'],
				'FormPostBackKey=' . $this->Context->Session->GetCsrfValidationKey() ))
			. '">'.$this->Context->GetDefinition('SignOut').'</a>)';
   } else {
	 echo '<div id="Login">
	 <form name="frmSignIn" id="frmSignIn" method="post" action="people.php">
     <input name="PostBackAction" value="SignIn" type="hidden" />
     <input name="ReturnUrl" value="'. GetRequestUri() .'" type="hidden" />
      <div class="Username">
      <input id="txtUsername" name="Username" value="'.$this->Context->GetDefinition('Username').'" class="inputbox" size="10" type="text" onfocus="clearValue(this)" /></div>
     <div class="Password">
      <input id="txtPassword" name="Password" value="'.$this->Context->GetDefinition('Password').'" class="inputbox" type="password" size="10" onfocus="clearValue(this)" /></div>
     <div class="Login-button"><button value="" name="Submit" type="submit" title="'.$this->Context->GetDefinition('Proceed').'"></button></div>
	 <div class="Break"></div>
	 <div class="Lostpassword">
	<a href="people.php?PostBackAction=PasswordRequestForm">'.$this->Context->GetDefinition('ForgotYourPassword').'</a>
		</div>
		<div class="Registration"><a href="people.php?PostBackAction=ApplyForm">'.$this->Context->GetDefinition('ApplyForMembership').'</a></div>
   </form></div>';
   }
   echo '</div>';
	$this->CallDelegate('PreHeadRender');
	echo '<div id="Header">
			<a name="pgtop"></a>
			<div id="Title"><a href="'.GetUrl($this->Context->Configuration, 'index.php').'">'.$this->Context->Configuration['BANNER_TITLE'].'</a></div>';

	$this->CallDelegate('PreBodyRender');	
    echo '<div id="nav"><div class="width"><ul>';
				while (list($Key, $Tab) = each($this->Tabs)) {
					echo '<li'.$this->TabClass($this->CurrentTab, $Tab['Value']).'><a href="'.$Tab['Url'].'" '.$Tab['Attributes'].'>'.$Tab['Text'].'</a></li>';
		      }			
echo '</ul></div></div></div>';
	$this->CallDelegate('PreBodyRender');
	echo '<div id="Main">';
?>