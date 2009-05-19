<?php
// Note: This file is included from the library/Vanilla/Vanilla.Control.DiscussionGrid.php class.


echo '
	<h1>
		'.$this->Context->PageTitle.'
	</h1>
	<div class="PageScroll"><a href="#pgbottom"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'go-down.gif" alt="Bottom of page arrow" title="'.$this->Context->GetDefinition('BottomOfPage').'" width="13" height="11" /></a></div>
	'.$this->PageJump.'
	<div class="PageInfo">
	<p>'.($PageDetails == '' ? $this->Context->GetDefinition('NoDiscussionsFound') : $PageDetails).'</p>
	'.$PageList.'
</div>
<div id="ContentBody">
	<ol id="Discussions">';

$Discussion = $this->Context->ObjectFactory->NewContextObject($this->Context, 'Discussion');
$FirstRow = 1;
$CurrentUserJumpToLastCommentPref = $this->Context->Session->User->Preference('JumpToLastReadComment');
$DiscussionList = '';
$ThemeFilePath = ThemeFilePath($this->Context->Configuration, 'discussion.php');
$Alternate = 0;
while ($Row = $this->Context->Database->GetRow($this->DiscussionData)) {
   $Discussion->Clear();
   $Discussion->GetPropertiesFromDataSet($Row, $this->Context->Configuration);
   $Discussion->FormatPropertiesForDisplay();
	// Prefix the discussion name with the whispered-to username if this is a whisper
   if ($Discussion->WhisperUserID > 0) {
		$Discussion->Name = @$Discussion->WhisperUsername.': '.$Discussion->Name;
	}

	// Discussion search results are identical to regular discussion listings, so include the discussion search results template here.
	include($ThemeFilePath);
	
   $FirstRow = 0;
	$Alternate = FlipBool($Alternate);
}
echo $DiscussionList.'
	</ol>
</div><div class="ClearBoth"></div>';
if ($this->DiscussionDataCount > 0) {
   echo '<div class="PageInfo">
			<p>'.$pl->GetPageDetails($this->Context).'</p>
			'.$PageList.'
		</div><div class="ClearBoth"></div>
		<div class="PageScroll"><a id="TopOfPage" href="'.GetRequestUri().'#pgtop"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'go-up.gif" alt="Top of page arrow" title="'.$this->Context->GetDefinition('TopOfPage').'" width="13" height="11" /></a></div>
	';
}
?>