easyFinance.models.user = function(){
	var URL_FOR_GET_INTEGRATION_EMAIL = '/?responseMode=json';
	var URL_FOR_REMOVE_INTEGRATION_EMAIL = '/?responseMode=json';
	var _data;
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
		$.get("/profile/load_main_settings/?responseMode=json", {}, function(data){
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
	 * Возвращает почту пользователя
	 * @return {string}
	 */
	function getEmail(){
		return _data.mail;
	}
	/**
	 * Возвращает логин пользователя
	 * @return {string}
	 */
	function getLogin(){
		return _data.login;
	}
	/**
	 * Возвращает имя пользователя
	 * @return {string}
	 */
	function getName(){
		return _data.name;
	}
	//    /**
	//     * Возвращает активную вкладку в левой панели
	//     * @return {str}
	//     */
	//    function getActiveInsertInLeftPanel(){
	//        return _data.activeInsertInLeftPanel;
	//    }
	/**
	 * Сохраняет пользовательскую информацию
	 * @param data {object}
	 * @param calback {function}
	 * @return void
	 *
	 */
	function setUserInfo(data, calback){
		if (typeof(data) == 'object') {
			$.cookie('tooltip', (data.tooltip || null), {
				expire: 100,
				path: '/',
				domain: false,
				secure: '1'
			});
			$.cookie('guide', (data.guide || null), {
				expire: 100,
				path: '/',
				domain: false,
				secure: '1'
			});
			$.post('/profile/save_main_settings/?responseMode=json', {
				getNotify: data.getNotify || 0,
				login: data.login || '',
				pass: data.password,
				newpass: data.newPassword,
				//                    confirmpass: data.confirmPassword || '', //server will be add confirm password
				mail: data.mail || '',
				guide: data.guide || 0 // server line75
			}, function(data){
				calback(data);
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
