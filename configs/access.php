<?php
/**
 * Конфигурация прав доступа к адресам сайта (именно к адресам, а не модулям)
 * работает по принципу: "что не разрешено - запрещено".
 * 
 * Если разрешён верхний адрес (info), автоматически разрешаются все расширяющие (info/graps),
 * если для них не определена своя запись. Главная страница разрешена всегда и всем.
 * 
 * Права определяются константами типов класса _User
 * 
 * так-же для удобства есть константы по группам:
 * всем пользователям: _Core_Access::ALLOW_ALL
 * всем авторизованным: _Core_Access::ALLOW_AUTHORIZED
 * 
 *
 * @author Andrew Tereshko aka mamonth
 * @package _Core_Access
 */

$accessConfig = array(
	// индекс.
	'review' 	=> _Core_Access::ALLOW_ALL,
	'rules'         => _Core_Access::ALLOW_ALL,
	'about'         => _Core_Access::ALLOW_ALL,
	'security'      => _Core_Access::ALLOW_ALL,
	'rules'         => _Core_Access::ALLOW_ALL,
	'feedback' 	=> _Core_Access::ALLOW_ALL,
	'articles' 	=> _Core_Access::ALLOW_ALL,
	'login' 	=> _Core_Access::ALLOW_ALL,
	'logout' 	=> _Core_Access::ALLOW_ALL,
	'registration' 	=> _Core_Access::ALLOW_ALL,
	'restore' 	=> _Core_Access::ALLOW_ALL,
	'redirect'      => _Core_Access::ALLOW_ALL,
	
	'profile' 	=> _Core_Access::ALLOW_AUTHORIZED,
	
	'info' 		=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'report' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'accounts' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'operation' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'category' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'budget' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'targets' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'calendar' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	'periodic' 	=> array( _User::TYPE_COMMON, _User::TYPE_PRO ),
	
	'experts'	=> _User::TYPE_PRO,
	
	'expert' 	=> _User::TYPE_EXPERT,
	
);
