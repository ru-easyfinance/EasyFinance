<?php

/*
Extension Name: DisplayName
Extension Url: http://lussumo.com/addons
Description: Support for displaying an alternate name to the login names.
Version: 0.0.2
Author: Jason Judge
Author Url: www.consil.co.uk
*/

/**
 * This plugin aims to provide support for an
 * alternative display name to the user login.
 * User logins should be kept secret - they may contain
 * e-mail addresses or information that can be matched up
 * with other sites or applications, posing a security risk
 * to the user.
 */

class DisplayNameManager {
	// List of full names already decoded.
	var $fullnames = array();

	var $Name;
	var $Context;

	// TODO: set this in the constructor - it should be a global override option.
    // 
	var $GlobalShowFullName = true;

	// Constructor
	function DisplayNameManager(&$Context) {
		$this->Name = 'DisplayNameManager';
		$this->Context = &$Context;
	}

	// TODO: Only do the lookup if the user has chosen to display their full name.
	// TODO: Support alternative display names, supplied by the user. It may be necessary to
	// keep the display names in a new table, so they can be checked for uniqueness, to prevent
	// people from masquarading as other people. Also permissions can be used to enforce various
	// rules, depending upon the application, e.g. if name supplied by external system and users
	// not allowed to change it.

	// Get the full name of a user, given their user ID.
	function GetFullName($UserID, $Default = '') {
		if (!isset($this->fullnames[$UserID])) {
			$s = $this->Context->ObjectFactory->NewContextObject($this->Context, 'SqlBuilder');
			$s->SetMainTable('User', 'u');
			$s->AddSelect('UserID', 'u');
			$s->AddSelect('Name', 'u');
			$s->AddSelect('FirstName', 'u');
			$s->AddSelect('LastName', 'u');
			$s->AddSelect('ShowName', 'u');
			$s->AddSelect('Preferences', 'u');

			$s->AddWhere('u', 'UserID', '', $UserID, '=');
			$ResultSet = $this->Context->Database->Select($s, $this->Name, 'GetUserRolesById', 'An error occurred while attempting to retrieve the requested user name.');
			if ($Row = $this->Context->Database->GetRow($ResultSet)) {

			// Get the data into a user object so we can do some standard manipulation.
			// TODO: should we store this in the global scope to save time creating it multiple times
			// on a page? If we do, $u->Clear() needs to be called each time it is used.
			$u = $this->Context->ObjectFactory->NewContextObject($this->Context, 'User');
			$u->GetPropertiesFromDataSet($Row);
			$u->FormatPropertiesForDisplay();
			$this->fullnames[$UserID] = $this->RenderDisplayName($u);
			} else {
                // TODO: how do we handle translation of 'Unknown'?
				$this->fullnames[$UserID] = (empty($Default) ? 'Unknown' : $Default);
			}
		}

		return $this->fullnames[$UserID];
	}

	// Render the display name of a user, given a User object (which may be partially complete).
	function RenderDisplayName(&$User)
	{
		// Only display the FullName if the user has the option set.
		// TODO: here we can also look at the user preferences in case there are further display names
		// e.g. $User->Preferences->DisplayName
		// Also check system-wide options to see if the user actually has a choice.
		if ($this->GlobalShowFullName || $User->ShowName) {
			return $User->FullName;
		} else {
			return $User->Name;
		}
	}
}

// TODO: we could probably handle all of these in one function by checking the
// context in that function. This would allow us to reduce the number of global
// functions we need by passing all the delegates into one function.

// Initialise the object as it will be used many times and caches data.
$DisplayNameObj = $Context->ObjectFactory->NewContextObject($Context, 'DisplayNameManager');

// Set the name in a Discussion object, passed in as a delegate parameter.
function DisplayNameManager_PreSingleDiscussionRender(&$p)
{
	global $DisplayNameObj;
	$Discussion =& $p->DelegateParameters['Discussion'];

	// Set the author name, saving the old login name.
    $Discussion->AuthUsernameLogin = $Discussion->AuthUsername;
	$Discussion->AuthUsername = $DisplayNameObj->GetFullName($Discussion->AuthUserID, $Discussion->AuthUsername);

	// Set "last comment by" user name.
    $Discussion->LastUsernameLogin = $Discussion->LastUsername;
	$Discussion->LastUsername = $DisplayNameObj->GetFullName($Discussion->LastUserID, $Discussion->LastUsername);
}

// Set the name in a Comment object, passed in as a delegate parameter.
function DisplayNameManager_PreCommentRender(&$p)
{
	// Set the author name for the Comment.
	DisplayNameManager_SetCommentDisplayName(&$p->DelegateParameters['Comment']);
}

// Set the name in a User object, passed in as a delegate parameter.
function DisplayNameManager_PreRenderUserSearch(&$p)
{
	global $DisplayNameObj;
	$User =& $p->DelegateParameters['User'];

	// Set the user display name.
    // We already have the full User object,so don't need to look it up again.
    $User->NameLogin = $User->Name;
	$User->Name = $DisplayNameObj->RenderDisplayName($User); 
}

// Set the name in a Comment object, passed in directly.
function DisplayNameManager_SetCommentDisplayName(&$Comment) {
	global $DisplayNameObj;

	// Set the author name, saving the old login name.
	$Comment->AuthUsernameLogin = $Comment->AuthUsername;
	$Comment->AuthUsername = $DisplayNameObj->GetFullName($Comment->AuthUserID, $Comment->AuthUsername);
}


// Set the Delegates.
// TODO: some better context is needed here, since we don't need all
// deletegates to be set for all pages and all contexts. However, we probably
// do need the DisplayName object created for every page, then it is available
// to themes and other extensions which may display comment authors in pages that
// otherwise do not contain author names.

// The discussion grid (discussions).
$Context->AddToDelegate('DiscussionGrid', 'PreSingleDiscussionRender', 'DisplayNameManager_PreSingleDiscussionRender');

// The search results (discussions).
$Context->AddToDelegate('SearchForm', 'PreSingleDiscussionRender', 'DisplayNameManager_PreSingleDiscussionRender');

// The search results (comments).
// Custom delegate required in the theme file search_results_comments.php (put these
// two lines right at the start):-
//   $this->DelegateParameters['Comment'] = &$Comment;
//   $this->CallDelegate('PreCommentRender');
$Context->AddToDelegate('SearchForm', 'PreCommentRender', 'DisplayNameManager_PreCommentRender');

// The search results (users).
$Context->AddToDelegate('SearchForm', 'PreRenderUserSearch', 'DisplayNameManager_PreRenderUserSearch');

// Comments on a discussion.
$Context->AddToDelegate('Comment', 'PreFormatPropertiesForDisplay', 'DisplayNameManager_SetCommentDisplayName');

// The name can be replaced in any script or template using the following code fragment, with
// the variable names appropriately renamed:-
//
//  global $DisplayNameObj;
//  if (isset($DisplayNameObj) && is_object($DisplayNameObj)) {
//	    $UserName = $DisplayNameObj->GetFullName($UserID, $UserName);
//  }

?>
