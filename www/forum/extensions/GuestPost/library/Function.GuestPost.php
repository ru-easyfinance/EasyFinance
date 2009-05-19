<?PHP

function CommentGrid_ShowGuestPostForm(&$CommentGrid)
	{
	if ($CommentGrid->ShowForm == 0
	&& $CommentGrid->Context->Session->UserID == 0
	&& ($CommentGrid->pl->PageCount == 1 || $CommentGrid->pl->PageCount == $CommentGrid->CurrentPage)
	&& ((!$CommentGrid->Discussion->Closed && $CommentGrid->Discussion->Active))
	&& $CommentGrid->CommentData ) $CommentGrid->ShowForm = 1;	}

function CommentForm_AddGuestPostInfo(&$DiscussionForm)
	{
	global $FailedCaptcha;
	echo '<label for="GuestPostWarning">'.$DiscussionForm->Context->GetDefinition('GuestPostWarning').'</label>';
	echo '<label for="Username">'.$DiscussionForm->Context->GetDefinition('Username').'</label>';
	echo '<input type="text" name="Username" value="'.FormatStringForDisplay(ForceIncomingString('Username', '')).'" />';
	echo '<label for="Password">'.$DiscussionForm->Context->GetDefinition('Password').'</label>';
	echo '<input type="password" name="Password" value="'.FormatStringForDisplay(ForceIncomingString('Password', '')).'" />';

	if 	(GuestPostCaptcha == 1)
		{
		echo "<div id=\"recaptcha\">".recaptcha_get_html(PublicKey)."</div>";
		}
	}

function DiscussionForm_SignOutGuest(&$DiscussionForm)
	{
	global $GP;
	global $Password;

	if 	($DiscussionForm->PostBackAction == 'SaveComment' && $Password == $GP)
		{
		$DiscussionForm->Context->Session->End($DiscussionForm->Context->Authenticator);
            	}
	}

function DiscussionForm_SignInGuest(&$DiscussionForm)
	{
	global $GU;
	global $GP;
	global $Password;
	global $Username;

	if 	($DiscussionForm->PostBackAction == 'SaveComment')
		{
		$UserManager = $DiscussionForm->Context->ObjectFactory->NewContextObject($DiscussionForm->Context, 'UserManager');

        if (!isset($_POST['recaptcha_challenge_field']) || !isset($_POST['recaptcha_response_field'])) {
            return FALSE;
        }

		//check reCAPTCHA
		$resp = recaptcha_check_answer (PrivateKey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

		//if the spam check was good, or Captcha is turned off, or you're logging in
		if 	($resp->is_valid || GuestPostCaptcha == 0 || ($GU != $Username && $GP != $Password))
			{
               		if	(!$UserManager->ValidateUserCredentials($Username, $Password, 0))
				{
				$Failed=1;
				}
			}
		else
			{
			$Failed=1;
			}

		//for whatever reason, it's failed - so take it to the failed page.
		if 	($Failed)
			{
			$DiscussionForm->PostBackAction = 'SaveCommentFailed';
			$DiscussionForm->Context->Session->UserID = -1;
			$DiscussionForm->Comment->Clear();
			$DiscussionForm->Comment->GetPropertiesFromForm();
			$DiscussionForm->Comment->DiscussionID = $DiscussionForm->DiscussionID;
			$dm = &$DiscussionForm->DelegateParameters['DiscussionManager'];
			$DiscussionForm->Discussion = $dm->GetDiscussionById($DiscussionForm->Comment->DiscussionID);
			$DiscussionForm->Comment->FormatPropertiesForDisplay(1);
			}
		}
	}
?>
