<?php
// Note: This file is included from the library/Vanilla/Vanilla.Control.SearchForm.php
// class and also from the library/Vanilla/Vanilla.Control.DiscussionForm.php's
// themes/discussions.php include template.

$UnreadUrl = GetUnreadQuerystring($Discussion, $this->Context->Configuration, $CurrentUserJumpToLastCommentPref);
$NewUrl = GetUnreadQuerystring($Discussion, $this->Context->Configuration, 1);
$LastUrl = GetLastCommentQuerystring($Discussion, $this->Context->Configuration, $CurrentUserJumpToLastCommentPref);

$DiscussionList .= '
<li id="Discussion_'.$Discussion->DiscussionID.'" class="Discussion'.$Discussion->Status.($Discussion->CountComments == 1?' NoReplies':'').($this->Context->Configuration['USE_CATEGORIES'] ? ' Category_'.$Discussion->CategoryID:'').($Alternate ? ' Alternate' : '').'">
   <div class="Topics">
	     <div class="TopicTitle">
		 '.DiscussionPrefix($this->Context, $Discussion).'
         <a href="'.$UnreadUrl.'" title="'.$Discussion->Name.'">'.$Discussion->Name.'</a>
      </div>';
	  $DiscussionList .= '<ul class="TopicInfo">
	  <li class="DiscussionStarted">
         '.$this->Context->GetDefinition('StartedBy').'&nbsp;<a href="'.GetUrl($this->Context->Configuration, 'account.php', '', 'u', $Discussion->AuthUserID).'">'.$Discussion->AuthUsername.'</a>
      </li>';
      if ($this->Context->Configuration['USE_CATEGORIES']) {
         $DiscussionList .= '
	   <li class="DiscussionCategory">
            '.$this->Context->GetDefinition('Category').'&nbsp;<a href="'.GetUrl($this->Context->Configuration, 'index.php', '', 'CategoryID', $Discussion->CategoryID).'">'.$Discussion->Category.'</a>
         </li>
      <li class="DiscussionComments">
         '.$Discussion->CountComments.'&nbsp;'.$this->Context->GetDefinition('Comments').'
      </li>
	  <li class="DiscussionLastComment">
         <span><a href="'.$LastUrl.'">'.$this->Context->GetDefinition('LastCommentBy').'</a>&nbsp;</span><a href="'.GetUrl($this->Context->Configuration, 'account.php', '', 'u', $Discussion->LastUserID).'">'.$Discussion->LastUsername.'</a>
      </li>
	  <li class="DiscussionActive">
         '.TimeDiff($this->Context, $Discussion->DateLastActive,mktime()).'
      </li>';
      if ($this->Context->Session->UserID > 0) {
            $DiscussionList .= '
         <li class="DiscussionNew">
		 <a href="'.$NewUrl.'">['.$Discussion->NewComments.'&nbsp;<span>'.$this->Context->GetDefinition('NewCaps').' </span>]</a>
         </li>
         ';
      }
   $this->DelegateParameters['Discussion'] = &$Discussion;
   $this->DelegateParameters['DiscussionList'] = &$DiscussionList;
   
   $this->CallDelegate('PostDiscussionOptionsRender');
   
$DiscussionList .= '</ul>
</div></li>';
}
?>