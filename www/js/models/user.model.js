/**
 * @author rewle
 */
easyFinance.models.user = function(data){
	var URL_FOR_GET_INTEGRATION_EMAIL = '/profile/generate_service_mail?responseMode=json';
	var URL_FOR_REMOVE_INTEGRATION_EMAIL = '/profile/delete_service_mail/?responseMode=json';
	var URL_FOR_LOAD_USER_INFO = "/profile/load_main_settings/?responseMode=json";
	var URL_FOR_SAVE_USER_INFO = '/profile/save_main_settings/?responseMode=json';
	var SETTING_FOR_WRITING_COOKIE = {
		expire: 100,
		path: '/',
		domain: false,
		secure: '1'
	}
	
	
	var _data = data || {};
	/**
	 * Инициирует модель из реса - пока не реализовано со стороны сервера
	 */
	function load(data){
		_data = data;
	}
	/**
	 * Перезагружает данные пользователя
	 * @param calback {function}
	 * @return {void}
	 */
	function reload(calback){
		$.get(URL_FOR_LOAD_USER_INFO, {}, function(data){
			_data = data.profile;//server will be sent 'spamer'&& notify && specialMail
			_data.getNotify = res.getNotify;
			_data.tooltip = $.cookie('tooltip');
			_data.guide = $.cookie('guide') == 'uyjsdhf';//guide
			if (typeof(calback) == 'function') {
				calback($.extend({}, _data));
			}
		}, 'json');
	}
	/**
	 * Возвращает всю пользовательскую информацию
	 * @return {object}
	 */
	function getUserInfo(){
		return _data;
	}
	/**
	 * Сохраняет пользовательскую информацию
	 * @param data {object}
	 * @param calback {function}
	 * @return void
	 */
	function setUserInfo(data, calback){
		if (typeof(data) == 'object') {
			//cookies
			$.cookie('tooltip', (data.tooltip || null), SETTING_FOR_WRITING_COOKIE);
			$.cookie('guide', (data.guide || null), SETTING_FOR_WRITING_COOKIE);
			//Ajax
			$.post(URL_FOR_SAVE_USER_INFO, {
				getNotify: data.getNotify || 0,
				login: data.login || '',
				pass: data.password,
				newpass: data.newPassword,
				//              confirmpass: data.confirmPassword || '', //server will be add confirm password
				mail: data.mail || '',
				guide: data.guide || 0 // server line75
			}, function(data){
				if (typeof(calback) == 'function') {
					calback(data);
				}
			}, 'json');
		}
	}
	/**
	 * Возвращает факт показывания гида
	 * @return {boolean}
	 */
	function isUsedGuide(){
		return _data.guide ? true : false;//Now we use this cookie where not use guide
	}
	/**
	 * Возвращает факт показывания всплывающих подсказок
	 * @return {boolean}
	 */
	function isUsedTooltip(){
		return _data.tooltip ? true : false;
	}
	/**
	 * Создания почты для интеграции с банками
	 * @param callback {function}
	 * @return {void}
	 */
	function getIntegrationEmail(callback){
		$.post(URL_FOR_GET_INTEGRATION_EMAIL, {}, function(data){
			_data.email = data.integrationEmail;
			if (typeof(callback) == 'function') {
				callback(data);
			}
		}, 'json');
	}
	/**
	 * Удаление почты для интеграции с банками
	 * @param callback {function}
	 * @return {void}
	 */
	function removeIntegrationEmail(callback){
		$.post(URL_FOR_REMOVE_INTEGRATION_EMAIL, {}, function(data){
			if (data && data.result) {
				delete _data.email;
			}
			if (typeof(callback) == 'function') {
				callback(data);
			}
		}, 'json');
	}
	
	
	return {
		reload: reload,
		getUserInfo: getUserInfo,
		setUserInfo: setUserInfo,
		getIntegrationEmail: getIntegrationEmail,
		removeIntegrationEmail: removeIntegrationEmail
	}
}();
