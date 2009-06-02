<?php
	class SMAdmin extends PostBackControl {
		var $Context;
		var $Success;
		var $Data = array();
		var $PerPage;
		var $MsgData = array();
	
		function SMAdmin(&$Context) {
			$this -> Context = $Context;
			$this -> Success = 0;
			$this -> PerPage = 10;
			
			$MsgID = ForceIncomingInt('MsgID', 0);
			
			if(ForceIncomingString('SMDelete', '') != '') $this -> RemoveMessage($MsgID);
			if(ForceIncomingString('ProcessSMNew', '') != '') $this -> AddMessage($MsgID);
			
			$sql = "
				SELECT *
				FROM `{$Context->Configuration['DATABASE_TABLE_PREFIX']}SysMsg`
				ORDER BY `MsgID` DESC
			";
			$data = $Context -> Database -> Execute($sql, __CLASS__, 'Constructor', $Context -> GetDefinition('ErrSMDatabase'));
			
			while($Row = mysql_fetch_assoc($data)) {
				$Row['To'] = unserialize($Row['To']);
				if($Row['Read']) $Row['Read'] = unserialize($Row['Read']);
				else $Row['Read'] = array();
				$this -> Data[] = $Row;
				
				if($MsgID == $Row['MsgID']) $this -> MsgData = $Row;
			}
			
			if(ForceIncomingString('Action', '') == 'Cleanup') $this -> Cleanup();
			
			if(!isset($this -> MsgData['MsgID']) || $this -> Success > 1) $this -> ResetMsgData();
		}
		
		function RemoveMessage($MsgID) {
			$sql = "
				DELETE FROM `{$this->Context->Configuration['DATABASE_TABLE_PREFIX']}SysMsg`
				WHERE `MsgID`='$MsgID'
			";
			$this -> Context -> Database -> Execute($sql, __CLASS__, 'RemoveMessage', $this -> Context -> GetDefinition('ErrSMDatabase'));
			
			$this -> Success = 4;
		}
		
		function Cleanup() {
			$Query[] = "
				DELETE FROM `{$this->Context->Configuration['DATABASE_TABLE_PREFIX']}SysMsg`
				WHERE ";
			foreach($this -> Data as $k => $Datum) {
				if(count($Datum['Read']) != $this -> GetUserNum($Datum['Roles'], $Datum['To'])) continue;
				if($Datum['Roles'] && in_array('1', $Datum['To'])) continue;
				
				if(count($Query) > 1) $Query[] = ' OR ';
				$Query[] = "`MsgID`='$Datum[MsgID]'";
				
				unset($this -> Data[$k]);
			}
			
			if(count($Query) < 2) return;
			
			$this -> Data = array_values($this -> Data);
			$this -> Context -> Database -> Execute(implode('', $Query), __CLASS__, 'Cleanup', $this -> Context -> GetDefinition('ErrSMDatabase'));
			$this -> Success = 3;
		}
		
		function ResetMsgData() {
			$this -> MsgData = array(
				'MsgID' => '',
				'To' => array(),
				'Read' => array(),
				'Msg' => '',
				'Title' => '',
				'Roles' => 1
			);
		}
		
		function AddMessage($MsgID) {
			$Title = addslashes(ForceIncomingString('title', 'Untitled'));
			$Message = addslashes(ForceIncomingString('message', ''));
			$To = ForceIncomingString('to', '');
			$Roles = ForceIncomingInt('NotRoles', 0) ^ 1;
			
			$Ids = explode(',', $To);
			foreach($Ids as $k => $Name) {
				$Ids[$k] = trim($Name);
				if($Name == '') unset($Ids[$k]);
			}
			$Ids = array_values($Ids);
			
			if(count($Ids) < 1) return;
			
			if($Roles) $Prefix = 'Role';
			else $Prefix = 'User';
			
			$Query = "
				SELECT `{$Prefix}ID`
				FROM `{$this->Context->Configuration['DATABASE_TABLE_PREFIX']}$Prefix`
				WHERE ";
			foreach($Ids as $Name) {
				if(substr($Query, -1, 1) != ' ') $Query .= ' OR ';
				$Query .= "`Name`='$Name'";
			}
			
			$data = $this -> Context -> Database -> Execute($Query, __CLASS__, 'AddMessage', $this -> Context -> GetDefinition('ErrSMDatabase'));
			$Ids = array();
			while($Row = mysql_fetch_assoc($data)) $Ids[] = $Row[$Prefix . 'ID'];
			
			if(count($Ids) < 1) {
				header('Location: ?PostBackAction=SMAdmin&Fail=To');
				exit;
			}
			
			$Ids = serialize($Ids);
			
			if($MsgID) {
				$sql = "
					UPDATE `{$this->Context->Configuration['DATABASE_TABLE_PREFIX']}SysMsg`
					SET `Title`='$Title', `Msg`='$Message', `To`='$Ids', `Roles`='$Roles'
					WHERE `MsgID`='$MsgID'
				";
				$this -> Success = 2;
			} else {
				$sql = "
					INSERT INTO `{$this->Context->Configuration['DATABASE_TABLE_PREFIX']}SysMsg`
					(`Title`, `Msg`, `To`, `Roles`)
					VALUES('$Title', '$Message', '$Ids', '$Roles')
				";
				$this -> Success = 1;
			}
			$this -> Context -> Database -> Execute($sql, __CLASS__, 'AddMessage', $this -> Context -> GetDefinition('ErrSMDatabase'));
			
			$this -> ResetMsgData();
		}
		
		function GetNames($Table, $Where = '') {
			$sql = "
				SELECT `Name`
				FROM `{$this->Context->Configuration['DATABASE_TABLE_PREFIX']}$Table`
				$Where
			";
			$data = $this -> Context -> Database -> Execute($sql, __CLASS__, 'GetName', $this -> Context -> GetDefinition('ErrSMDatabase'));
			
			$Names = array();
			while($Row = mysql_fetch_assoc($data)) $Names[] = $Row['Name'];
			
			return $Names;
		}
		
		function GetOptions($Table, $Where = '') {
			$Names = $this -> GetNames($Table, $Where);
			$Return = '';
			foreach($Names as $Name) $Return .= "\n\t<option>$Name</option>";
			
			return $Return;
		}
		
		function CreatePageList() {
			$Pages = ceil(count($this -> Data) / $this -> PerPage);
			if($Pages == 1) return "\n\t<li />";
			
			$PageList = array();
			$PageList[] = "\n\t<li><a href=\"javascript:void(0)\" class=\"inactive\" id=\"PageLeft\">&lt;</a></li>";
			$Append = false;
			for($i = 1; $i <= $Pages; $i++) {
				if($i > 9) {
					$PageList[$i - 1] = '';
					$Append = true;
					break;
				}
				if($i > 1) $PageList[] = "\n\t<li><a href=\"javascript:void(0);\" id=\"PageLink_$i\">$i</a></li>";
				else $PageList[] = "\n\t<li><a href=\"javascript:void(0);\" class=\"inactive\" id=\"PageLink_$i\">$i</a></li>";
			}
			
			if($Append) {
				$PageList[] = "\n\t<li>...</li>";
				$PageList[] = "\n\t<li><a href=\"javascript:void(0);\" id=\"PageLink_$Pages\">" . $Pages . "</a></li>";
			}
			$PageList[] = "\n\n<li><a href=\"javascript:void(0);\" id=\"PageRight\">&gt;</a></li>";
			
			return implode('', $PageList);
		}
		
		function ConstructWhere($Ids, $Prefix) {
			$Where[] = 'WHERE ';
			foreach($Ids as $Id) {
				if(count($Where) > 1) $Where[] = ' OR ';
				$Where[] = "`{$Prefix}ID`='$Id'";
			}
			
			return implode('', $Where);
		}
		
		function GetUserNum($Roles, $Ids, $GetIds = false, $Context = false) {
			if(!$Roles) return count($Ids);
			if(!$Context) $Context = & $this -> Context;
			$Where = SMAdmin::ConstructWhere($Ids, 'Role');
			$Select = 'COUNT(*)';
			if($GetIds) $Select = '`UserID`';
			$sql = "
				SELECT $Select
				FROM `{$Context->Configuration['DATABASE_TABLE_PREFIX']}User`
				$Where
			";
			$data = $Context -> Database -> Execute($sql, __CLASS__, 'GetUserNum', $Context -> GetDefinition('ErrSMDatabase'));
			
			if(!$GetIds) return mysql_result($data, 0, 'COUNT(*)');
			else {
				$Return = array();
				while($Row = mysql_fetch_assoc($data)) $Return[] = $Row['UserID'];
				return $Return;
			}
		}
		
		function CreateMessageList( $Context = false  ) {
			$Pages = ceil(count($this -> Data) / $this -> PerPage);
			if(!$Context) $Context = & $this -> Context;
			$MessageList = '';
			$DlOpen = false;
			
			for($i = 0; $i < count($this -> Data); $i++) {
				$Datum = $this -> Data[$i];
			
				$OldPage = $i % $this -> PerPage;
				if(!$OldPage) {
					$Page = ($i / $this -> PerPage) + 1;
					if($DlOpen) $MessageList .= '</dl>';
					$DlOpen = true;
					if($i + 1 <= count($this -> Data)) $MessageList .= "<dl id=\"Page_$Page\">";
				}
				
				$UserNum = $this -> GetUserNum($Datum['Roles'], $Datum['To']);
				if($UserNum > 0) $Percentage = ceil(count($Datum['Read']) / $UserNum * 100) . '%';
				else $Percentage = '0%';
				
				$SentTo = $this -> Context -> GetDefinition('SMSentTo') . ' ' . (($Datum['Roles']) ? $this -> Context -> GetDefinition('SMRoles') : $this -> Context -> GetDefinition('SMUsersShort'));
				
				if($Datum['Roles']) {
					$Table = 'Role';
					$Where = $this -> ConstructWhere($Datum['To'], 'Role');
				} else {
					$Table = 'User';
					$Where = $this -> ConstructWhere($Datum['To'], 'User');
				}
				
				$Names = $this -> GetNames($Table, $Where);
				$NameList = array_splice($Names, 0, 10);
				$NameList = implode(', ', $NameList);
				if(count($Names) > 10) $NameList .= ', ...';
				
				$Class = '';
				if(!$OldPage) $Class = ' first';
				
				$Url = GetUrl(
					$Context -> Configuration,
					'settings.php',
					'', '', '', '',
					'PostBackAction=SMAdmin&MsgID=' . $Datum['MsgID']
				);
				
				$MessageList .= "<dt id=\"Msg_$Datum[MsgID]\" class=\"Message$Class\">";
				$MessageList .= '<ul>';
				$MessageList .= "<li class=\"DiscussionType\">[ $Percentage ]</li>";
				$MessageList .= "<li class=\"DiscussionTopic\"><a href=\"$Url\">$Datum[Title]</a></li>";
				$MessageList .= "<li class=\"DiscussionCategory\"><span>$SentTo </span>$NameList</li>";
				$MessageList .= '</ul>';
				$MessageList .= '</dt>';
				
				$MessageList .= '<dd class="MessageContent">';
				$MessageList .= "<p>$Datum[Msg]</p>";
				$MessageList .= '</dd>';
				
				if($i + 1 == count($this -> Data)) $MessageList .= '</dl>';
			}
			
			return $MessageList;
		}
		
		function Render() {
			$trans = array(
				'SMNewMessage',
				'SMTitle',
				'SMMessage',
				'SMCreate',
				'Cancel',
				'SMEditMessages',
				'SMSendTo',
				'SMUsers',
				'SMRoles',
				'SMSelectRole',
				'SMSuccess',
				'SMEdit',
				'SMEditMessage',
				'SMEditSuccess',
				'SMCleanup',
				'SMCleanupSuccess',
				'SMDelete',
				'SMDeleted'
			);
			
			foreach($trans as $k => $str) {
				$trans[$k] = $this -> Context -> GetDefinition($str);
			}
			
			$Roles = "\n\t<option disabled=\"disabled\" selected=\"selected\">$trans[9]</option><option>[ All ]</option><option>Unauthenticated</option>" . $this -> GetOptions('Role', "WHERE `UnAuthenticated`<'1'");
			$Users = $this -> GetOptions('User', 'ORDER BY `Name` ASC');
			
			$Success = '';
			if($this -> Success == 1) {
				$Success = "<div id=\"Success\">$trans[10]</div>";
			} elseif($this -> Success == 2) {
				
			} else
			switch($this -> Success) {
				case 1: $Success = "<div id=\"Success\">$trans[10]</div>"; break;
				case 2: $Success = "<div id=\"Success\">$trans[13]</div>"; break;
				case 3: $Success = "<div id=\"Success\">$trans[15]</div>"; break;
				case 4: $Success = "<div id=\"Success\">$trans[17]</div>"; break;
			}
			
			$Messages = count($this -> Data);
			$Displayed = ($Messages < $this -> PerPage) ? $Messages : $this -> PerPage;
			$PageList = $this -> CreatePageList();
			$MessageList = $this -> CreateMessageList();
			
			$BackUrl = GetUrl(
				$this -> Context -> Configuration,
				'settings.php'
			);
			$Action = $BackUrl;
			
			$CleanupUrl = GetUrl(
				$this -> Context -> Configuration,
				'settings.php',
				'', '', '', '',
				'PostBackAction=SMAdmin&Action=Cleanup'
			);
			
			$NewButton = '';
			if($this -> MsgData['To']) {
				$trans[3] = $trans[11];
				$trans[0] = $trans[12];
				
				$NewButton = " <input class=\"Button SubmitButton\" type=\"button\" value=\"$trans[16]\" id=\"DeleteButton\" />";
				
				$BackUrl = GetUrl(
					$this -> Context -> Configuration,
					'settings.php',
					'', '', '', '',
					'PostBackAction=SMAdmin'
				);
				
				$Table = ($this -> MsgData['Roles']) ? 'Role' : 'User';
				$trans[8] = implode(
					', ',
					$this -> GetNames(
						$Table,
						$this -> ConstructWhere(
							$this -> MsgData['To'],
							$Table
						)
					)
				);
			}
			
			$Checked = '';
			if(!$this -> MsgData['Roles']) $Checked = ' checked="checked"';
			
			echo <<<HTML
<div id="Form" class="StartDiscussion">
	$Success
	<fieldset>
		<div class="legend">
			<a href="javascript:void(0);" class="active">$trans[0]</a>
			<a href="javascript:void(0);" class="right">$trans[5]</a>
		</div>
		<hr />
		<form name="DeleteData" method="post" id="DeleteForm">
			<input type="hidden" name="MsgID" value="{$this->MsgData['MsgID']}" />
			<input type="hidden" name="SMDelete" value="true" />
			<input type="hidden" name="PostBackAction" value="SMAdmin" />
		</form>
		<form name="NewMessage" method="post" id="CreateTab" action="$Action">
			<input type="hidden" name="PostBackAction" value="SMAdmin" />
			<input type="hidden" name="MsgID" value="{$this->MsgData['MsgID']}" />
			<dl>
				<dt>$trans[1]</dt>
				<dd><input name="title" type="text" class="BigInput" value="{$this->MsgData['Title']}" /></dd>
				<dt>$trans[2]</dt>
				<dd><textarea name="message">{$this->MsgData['Msg']}</textarea></dd>
				<dt>$trans[6] <img src="{$this->Context->Configuration['WEB_ROOT']}extensions/SystemMessage/img/book_open.png" id="ExpandButton" alt="More Options" /></dt>
				<dd><input name="to" type="text" class="BigInput Special" value="$trans[8]" /></dd>
			</dl>
			
			<div id="ExpandedOptions">
				<dl>
					<dd><input type="checkbox" name="NotRoles" value="1"$Checked /> $trans[7]</dd>
				</dl>
				
				<dl id="ChoiceRoles">
					<dd>
						<select name="Irrelevant01" id="SelectRole">$Roles
						</select>
					</dd>
				</dl>
				
				<dl id="ChoiceUsers">
					<dd>
						<input name="Irrelevant02" id="SearchUser" type="text" class="Search" />
					</dd>
					<dd>
						<select name="Irrelevant03" id="UserSelect" size="10">$Users
						</select>
					</dd>
				</dl>
			</div>
			
			<div class="Submit">
				<input class="Button SubmitButton" type="submit" value="$trans[3]" name="ProcessSMNew" />$NewButton
				<a class="CancelButton" href="$BackUrl">$trans[4]</a>
			</div>
		</form>
		
		<div id="EditTab">
			<div class="PageInfo">
				<p><span class="MsgCountStart">1</span> to <span class="MsgCountEnd">$Displayed</span> of $Messages</p>
				<ol class="PageList" id="PageNums">$PageList
				</ol>
				<hr />
			</div>
			<div id="ContentBody">
				$MessageList
			</div>
			<div class="PageInfo">
				<p><span class="MsgCountStart">1</span> to <span class="MsgCountEnd">$Displayed</span> of $Messages</p>
				<ol class="PageList">
					<li>
						<a href="$CleanupUrl">$trans[14]</a>
					</li>
				</ol>
				<hr />
			</div>
		</div>
	</fieldset>
</div>
HTML;
		}
	}
?>
