<?php
/*
Extension Name: SignOutRedirect
Extension Url: http://lussumo.com/addons/
Description: Redirects to specific page after signing out instead of displaying successfull sign out message.
Version: 0.1
Author: f(n)=f(n-1)+f(n-2)
Author Url: http://www.niflheim.de/
*/

include_once($Configuration['LIBRARY_PATH'] . 'People/People.Control.Leave.php');

// 20090522 ralf - Don't reinvent the whell, but extend Marks class.
class LeaveAndRedirect extends Leave 
{
    function LeaveAndRedirect(&$Context) 
    {
	parent::Leave($Context);

	// 20090522 ralf - PostBackValidated is marked as private. But as the only
	// way to check if our parent function completed successfully is to look at
	// private members, we don't care.
      
	if ($this->PostBackValidated) {
	    $TargetUrl = $Context->Configuration['SIGNOUT_REDIRECT_URL'] ? $Context->Configuration['SIGNOUT_REDIRECT_URL'] : 'index.php';
            Redirect(substr($TargetUrl, 0, 4) == 'http' ? $TargetUrl : GetUrl($Context->Configuration, $TargetUrl));
        }
    }
}

if (isset($Context)) {
    $Context->ObjectFactory->SetReference('Leave', 'LeaveAndRedirect');
}
