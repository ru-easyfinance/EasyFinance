<?php
/*
Extension Name: System Message
Extension Url: http://soul-scape.com/
Description: Allows you to give important messages to certain roles or users
Version: 1.7
Author: Fyorl
Author Url: http://soul-scape.com/
*/

	require_once('admin.class.php');

	$Context -> SetDefinition('PERMISSION_SM_WRITE', 'Можно создавать и править системные сообщения');
	$Context -> SetDefinition('ErrSMDatabase', 'Ошибка связанная с базой данных');
	$Context -> SetDefinition('SMAdmin', 'Написать/Редактировать системное сообщение');
	$Context -> SetDefinition('SMNewMessage', 'Создать новое сообщение');
	$Context -> SetDefinition('SMEditMessage', 'Править сообщение');
	$Context -> SetDefinition('SMTitle', 'Заголовок');
	$Context -> SetDefinition('SMClose', 'закрыть');
	$Context -> SetDefinition('SMMessage', 'Сообщение (HTML Разрешён)');
	$Context -> SetDefinition('SMCreate', 'Создать');
	$Context -> SetDefinition('SMEdit', 'Править');
	$Context -> SetDefinition('SMEditMessages', 'Править сообщение');
	$Context -> SetDefinition('SMSendTo', 'Отправить ');
	$Context -> SetDefinition('SMUsers', 'Добавить конкретных пользователей');
	$Context -> SetDefinition('SMUsersShort', 'Пользователи');
	$Context -> SetDefinition('SMRoles', 'Роли');
	$Context -> SetDefinition('SMDelete', 'Удалить');
	$Context -> SetDefinition('SMDeleted', 'Сообщение успешно удалено');
	$Context -> SetDefinition('SMSelectRole', 'Выбрать роли');
	$Context -> SetDefinition('SMSuccess', 'Ваше системное сообщение успешно обновлено');
	$Context -> SetDefinition('SMEditSuccess', 'Сообщение успешно обновлено');
	$Context -> SetDefinition('SMCleanup', 'Очистить прочитанные сообщения');
	$Context -> SetDefinition('SMCleanupSuccess', 'Сообщение, прочитанные всеми, успешно удалены');

	if(!isset($Configuration['SM_INSTALLED'])) {
		$sql = "
			 CREATE TABLE IF NOT EXISTS `$Configuration[DATABASE_TABLE_PREFIX]SysMsg` (
				`MsgID` INT(30) NOT NULL AUTO_INCREMENT,
				`Title` VARCHAR(255) NOT NULL DEFAULT 'Untitled',
				`Msg` TEXT NOT NULL,
				`To` TEXT NOT NULL,
				`Roles` TINYINT(1) NOT NULL,
				`Read` TEXT NULL,
				PRIMARY KEY (`MsgID`)
			)
			ENGINE = MYISAM
			COMMENT = 'Stores system message data'
		";
		$Context -> Database -> Execute($sql, '', '', $Context -> GetDefinition('ErrSMDatabase'));

		AddConfigurationSetting($Context, 'SM_INSTALLED', 1);
		AddConfigurationSetting($Context, 'PERMISSION_SM_WRITE', '0');
	}

	if(!isset($Configuration['PERMISSION_SM_WRITE'])) {
		AddConfigurationSetting($Context, 'PERMISSION_SM_WRITE', '0');
	}

	function SM_AddMootools() {
		global $Head;
		if(!function_exists('MT_AddComponent')) {
			$Head -> AddScript('extensions/SystemMessage/js/mootools.js');
		} else {
			MT_AddComponent('Accordion', 'Ajax', 'Fx.Transitions', 'Fx.Slide', 'Fx.Style', 'Window.DomReady', 'Element.Selectors', 'Window.Size', 'Element.Dimensions');
		}
	}

	function SM_AddPermissions($Role) {
		$Role -> AddPermission('PERMISSION_SM_WRITE');
	}
	$Context -> AddToDelegate('Role', 'DefineRolePermissions', 'SM_AddPermissions');

	if($Context -> SelfUrl != 'people.php') {
		$sql = "
			SELECT *
			FROM `$Configuration[DATABASE_TABLE_PREFIX]SysMsg`
		";
		$data = $Context -> Database -> Execute($sql, '', '', $Context -> GetDefinition('ErrSMDatabase'));

		while($Message = mysql_fetch_assoc($data)) {
			if(!$Message['Read']) $Read = array();
			else $Read = unserialize($Message['Read']);
			$To = unserialize($Message['To']);
			if($Message['Roles']) $To = SMAdmin::GetUserNum(1, $To, true, $Context);

			if(!isset($_SESSION['LussumoReadMsgs'])) $sReadMsgs = array();
			else $sReadMsgs = $_SESSION['LussumoReadMsgs'];

			if(
				(
					in_array($Context -> Session -> UserID, $To)
					&& !in_array($Context -> Session -> UserID, $Read)
				) || (
					$Message['Roles']
					&& in_array('1', $To)
					&& $Context -> Session -> UserID == 0
					&& !in_array($Message['MsgID'], $sReadMsgs)
				)
			) {
				$Message['Found'] = true;
				break;
			}
		}

		if(isset($Message['Found'])) {
			SM_AddMootools();
			$Head -> AddScript('extensions/SystemMessage/js/message.js');
			$Head -> AddStyleSheet('extensions/SystemMessage/css/message.css');

			$Url = $Configuration['WEB_ROOT'] . 'extensions/SystemMessage/ajax.php';
			$Head -> AddString(
				'<script type="text/javascript">'
				. 'SysMsg.Title = "' . str_replace('"', '\"', htmlspecialchars($Message['Title'])) . '";'
				. 'SysMsg.Msg = "' . addslashes($Message['Msg']) . '";'
				. "SysMsg.MsgID = '$Message[MsgID]';"
				. "SysMsg.Url = '$Url';"
				. "SysMsg.Array = '$Message[Read]';"
				. "SysMsg.Close = '" . $Context -> GetDefinition('SMClose') . "'"
				. '</script>'
			);
		}
	}

	if($Context -> SelfUrl == 'settings.php' && $Context -> Session -> User -> Permission('PERMISSION_SM_WRITE')) {
		$xopt = $Context -> GetDefinition('ExtensionOptions');
		$Panel -> AddList($xopt, 10);
		$Panel -> AddListItem(
			$xopt,
			$Context -> GetDefinition('SMAdmin'),
			GetUrl(
				$Context -> Configuration,
				'settings.php',
				'', '', '', '',
				'PostBackAction=SMAdmin'
			)
		);

		if(ForceIncomingString('PostBackAction', '') == 'SMAdmin') {
			$trans = array(
				'SMRoles',
				'SMUsersShort'
			);

			foreach($trans as $k => $str) {
				$trans[$k] = $Context -> GetDefinition($str);
			}

			SM_AddMootools();
			$Head -> AddScript('extensions/SystemMessage/js/admin.js');

			$Fail = '';
			if(ForceIncomingString('Fail', '') != '') $Fail = 'SysMsgAdministrate.Fail = true;';

			$Head -> AddString(
				'<script type="text/javascript">'
				. "SysMsgAdministrate.Translate = ['$trans[0]', '$trans[1]'];"
				. $Fail
				. '</script>'
			);
			$Head -> AddStyleSheet('extensions/SystemMessage/css/admin.css');
			$SMAdmin = $Context -> ObjectFactory -> NewContextObject($Context, 'SMAdmin');
			$Page -> AddRenderControl($SMAdmin, $Configuration['CONTROL_POSITION_BODY_ITEM']);
		}
	}

?>