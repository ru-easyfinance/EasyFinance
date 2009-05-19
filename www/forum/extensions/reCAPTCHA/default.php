<?php
/*
Extension Name: reCAPTCHA
Extension Url: http://code.google.com/p/vanilla-friends/
Description: Add reCAPTCHA validation to the registration form.
Version: 1.10.2
Author: squirrel, inspired by Dinoboff's CAPTCHA
Author Url: http://digitalsquirrel.com/
*/

// Set dictionary definitions.
$Context->SetDefinition('reCAPTCHA.Error.Config', 'The reCAPTCHA extension is not configured properly. Please <a href="mailto:' . $Configuration['SUPPORT_EMAIL'] . '">contact the admin</a> and report this problem.');
$Context->SetDefinition('reCAPTCHA.Error.Incorrect', 'The reCAPTCHA was not entered correctly.');
$Context->SetDefinition('reCAPTCHA.Error.NoKey', 'You must enter both a public key and a private key to use reCAPTCHA.');


// Define the path to the reCAPTCHA code.
define('RECAPTCHA_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'recaptcha-php-1.10');

// Avoid an error message if the extension hasn't been set up yet.
if ( !isset($Context->Configuration['reCAPTCHA.ApplyForm']) ) {
	$Context->Configuration['reCAPTCHA.ApplyForm'] = 0;
}

if ( ('people.php' == $Context->SelfUrl)
	&& in_array(ForceIncomingString('PostBackAction', ''), array('ApplyForm', 'Apply'))
	&& $Context->Configuration['reCAPTCHA.ApplyForm'] ) {

	// Bail out if reCAPTCHA was somehow enabled with no keys.
	if ( !isset($Context->Configuration['reCAPTCHA.PublicKey'])
		|| !isset($Context->Configuration['reCAPTCHA.PrivateKey'])
		|| !$Context->Configuration['reCAPTCHA.PublicKey']
		|| !$Context->Configuration['reCAPTCHA.PrivateKey'] ) {
		$Context->WarningCollector->Add($Context->GetDefinition('reCAPTCHA.Error.Config'));
		return;
	}

	// Include the reCAPTCHA library.
	if (!function_exists('recaptcha_get_html')) {
		include(RECAPTCHA_PATH . DIRECTORY_SEPARATOR . 'recaptchalib.php');
	}

	// Add a custom stylesheet to make sure reCAPTCHA fits in the app form.
	$Head->AddStyleSheet('extensions/reCAPTCHA/reCAPTCHA.css', 'screen', 998);
	$Head->AddStyleSheet($Context->StyleUrl.'reCAPTCHA.css', 'screen', 999);

	function reCAPTCHA_RenderCaptcha(&$ApplyForm)
	{
		// Assume white is the default theme.
		$theme = isset($ApplyForm->Context->Configuration['reCAPTCHA.Theme']) ?
			$ApplyForm->Context->Configuration['reCAPTCHA.Theme'] : 'white';

		// Assume English is the default language.
		$lang = isset($ApplyForm->Context->Configuration['reCAPTCHA.Language']) ?
			$ApplyForm->Context->Configuration['reCAPTCHA.Language'] : 'en';

		// Add the script that configures the reCAPTCHA box.
		echo "
<script>
var RecaptchaOptions = {
	theme : '$theme',
	lang : '$lang' 
};
</script>\n";

		// Output the reCAPTCHA box itself.
		echo recaptcha_get_html($ApplyForm->Context->Configuration['reCAPTCHA.PublicKey']);
	}
	$Context->AddToDelegate('ApplyForm', 'PostInputsRender', 'reCAPTCHA_RenderCaptcha');
 
	function reCAPTCHA_CheckCaptcha(&$ApplyForm)
	{
		// Check the submitted answer.
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		$resp = recaptcha_check_answer($ApplyForm->Context->Configuration['reCAPTCHA.PrivateKey'],
			$ip,
			ForceIncomingString('recaptcha_challenge_field', ''),
			ForceIncomingString('recaptcha_response_field', ''));

		// Report errors if necessary.
		if ( !$resp->is_valid ) {
			if ( 'incorrect-captcha-sol' == $resp->error ) {
				$ApplyForm->Context->WarningCollector->Add($ApplyForm->Context->GetDefinition('reCAPTCHA.Error.Incorrect'));
			}
			else {
				$ApplyForm->Context->WarningCollector->Add($ApplyForm->Context->GetDefinition('reCAPTCHA.Error.Config'));
			}
		}
	}
	$Context->AddToDelegate('ApplyForm', 'PreCreateUser', 'reCAPTCHA_CheckCaptcha');
}
else if ( ('settings.php' == $Context->SelfUrl)
	&& in_array(ForceIncomingString('PostBackAction', ''), array('reCAPTCHA', 'ProcessreCAPTCHA')) ) {

	// Include the reCAPTCHA library.
	include(RECAPTCHA_PATH . DIRECTORY_SEPARATOR . 'recaptchalib.php');

	function SetList_Init_reCAPTCHA(&$SetList)
	{
		// Drop any leading '.' from the domain.
		$domain = preg_replace(
			'/^\./',
			'',
			$SetList->Context->Configuration['COOKIE_DOMAIN']
		);

		// Put a reCAPTCHA signup link in the settings form.
		$elements = &$SetList->DelegateParameters['Form']['elements'];
		$elements['PrivateKey']['description'] = str_replace(
			'\\1',
			'"' . recaptcha_get_signup_url($domain, 'Vanilla') . '"',
			$elements['PrivateKey']['description']
		);

	}
	$Context->AddToDelegate('SetList', 'Init_reCAPTCHA', 'SetList_Init_reCAPTCHA');

	function SetList_Process_reCAPTCHA(&$SetList)
	{
		// Don't accept settings if the public or private key is blank.
		$elements = &$SetList->DelegateParameters['Form']['elements'];
		if ( !$elements['PublicKey']['value'] || !$elements['PrivateKey']['value'] ) {
			$SetList->Context->WarningCollector->Add($SetList->Context->GetDefinition('reCAPTCHA.Error.NoKey'));
		}
	}
	$Context->AddToDelegate('SetList', 'Process_reCAPTCHA', 'SetList_Process_reCAPTCHA');
}


// medicating in the mini-van
?>
