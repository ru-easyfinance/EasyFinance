<?php
// Note: This file is included from the library/Framework/Framework.Control.Panel.php class.
echo '<div id="Panel">';
// Add the start button to the panel
if ($this->Context->Session->UserID > 0 && $this->Context->Session->User->Permission('PERMISSION_START_DISCUSSION')) {
   $CategoryID = ForceIncomingInt('CategoryID', 0);
	if ($CategoryID == 0) $CategoryID = '';
	echo '<ul class="Lists"><li><a href="'.GetUrl($this->Context->Configuration, 'post.php', 'category/', 'CategoryID', $CategoryID).'">'
      .$this->Context->GetDefinition('StartANewDiscussion')
      .'</a></li></ul>';
}
// Start socialbookmarks
echo '<div id="SocialBookmarks">
         <div class="AddTo">
		 <a target="_blank" href="http://del.icio.us/post?v=4&amp;noui&amp;jump=close&amp;url='.GetUrl($this->Context->Configuration, 'index.php').'&amp;title='.$this->Context->Configuration['APPLICATION_TITLE'].'"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'delicious.png" width="16" height="16" alt="Add to delicious" title="Add to delicious" /></a></div>
		 <div class="AddTo">
		 <a target="_blank" href="http://technorati.com/faves?add='.GetUrl($this->Context->Configuration, 'index.php').'"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'technorati.png" width="16" height="16" alt="Add to technorati" title="Add to technorati" /></a></div>
		 <div class="AddTo">
		 <a target="_blank" href="http://www.google.com/bookmarks/mark?op=edit&amp;bkmk='.GetUrl($this->Context->Configuration, 'index.php').'&amp;title='.$this->Context->Configuration['APPLICATION_TITLE'].'"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'google.gif" width="16" height="16" alt="Add to technorati" title="Add to Google" /></a></div>
		 <div class="AddTo">
         <a target="_blank" href="http://digg.com/submit?phase=2&amp;url='.GetUrl($this->Context->Configuration, 'index.php').'&amp;title='.$this->Context->Configuration['APPLICATION_TITLE'].'"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'digg.png" width="16" height="16" alt="Digg it" title="Add to digg" /></a></div>
		 <div class="AddTo">
		 <a target="_blank" href="http://www.furl.net/storeIt.jsp?t='.$this->Context->Configuration['APPLICATION_TITLE'].'&amp;u='.GetUrl($this->Context->Configuration, 'index.php').'"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'furl.gif" width="16" height="16" alt="furl" title="Add to furl" /></a></div>
		 <div class="AddTo">
		 <a target="_blank" href="http://myweb2.search.yahoo.com/myresults/bookmarklet?t='.$this->Context->Configuration['APPLICATION_TITLE'].'&amp;u='.GetUrl($this->Context->Configuration, 'index.php').'"><img src="'.$this->Context->Configuration['DEFAULT_STYLE'].'yahoo.png" width="16" height="16" alt="Yahoo" title="Add to Yahoo my web" /></a></div>
		 </div>';
$this->CallDelegate('PostStartButtonRender');

while (list($Key, $PanelElement) = each($this->PanelElements)) {
   $Type = $PanelElement['Type'];
   $Key = $PanelElement['Key'];
   if ($Type == 'List') {
      $sReturn = '';
      $Links = $this->Lists[$Key];
      if (count($Links) > 0) {
         ksort($Links);
         $sReturn .= '<ul class="Lists">
            <li>
               <h2>'.$Key.'</h2>
               ';
               while (list($LinkKey, $Link) = each($Links)) {
                  $sReturn .= '
                     <a '.($Link['Link'] != '' ? 'href="'.$Link['Link'].'"' : '').' '.$Link['LinkAttributes'].'>'
                        .$Link['Item'];
                        if ($Link['Suffix'] != '') $sReturn .= ' <span>'.$this->Context->GetDefinition($Link['Suffix']).'</span>';
                     $sReturn .= '</a>';
                  $sReturn .= '';
               }
               $sReturn .= '
            </li>
         </ul>';
      }
      echo $sReturn;
   } elseif ($Type == 'String') {
      echo $this->Strings[$Key];
   }
}

$this->CallDelegate('PostElementsRender');

echo '</div>
<div id="Corner-left"></div><div id="Content">';
if ($this->Context->Session->UserID > 0) {
      echo str_replace('//1',
         $this->Context->Session->User->Name,
         $this->Context->GetDefinition('')).'';
   } 
?>