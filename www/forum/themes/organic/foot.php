<?php
// Note: This file is included from the library/Vanilla/Vanilla.Control.Foot.php class.
// Feel free to modifiy the theme in any way you want BUT don't remove my credit link!
echo '<a id="pgbottom" name="pgbottom">&nbsp;</a></div><div class="ClearBoth"></div></div>';
// END LEFT, CONTENT, WRAPPER & STRATR FOOTER
echo '<div id="Footer">
    <p>&copy; Yoursite.com All rights reserved.&nbsp;Theme by <a href="http://www.ventswap.com/">Vent Swap</a></p>
	</div>
	</div>';
// End Wrapper
$AllowDebugInfo = 0;
if ($this->Context->Session->User) {
   if ($this->Context->Session->User->Permission('PERMISSION_ALLOW_DEBUG_INFO')) $AllowDebugInfo = 1;
}
if ($this->Context->Mode == MODE_DEBUG && $AllowDebugInfo) {
   echo '<div class="DebugBar" id="DebugBar">
   <b>Debug Options</b> | Resize: <a href="javascript:window.resizeTo(800,600);">800x600</a>, <a href="javascript:window.resizeTo(1024, 768);">1024x768</a> | <a href="'
   ."javascript:HideElement('DebugBar');"
   .'">Hide This</a>';
   echo $this->Context->SqlCollector->GetMessages();
   echo '</div>';
}
?>