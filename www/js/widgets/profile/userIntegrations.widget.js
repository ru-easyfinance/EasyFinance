easyFinance.widgets.userIntegrations = function(model){
	var _model = model || easyFinance.models.user;
	var _node;
	var _data = {};
	
	function _validEmail(email){
		return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(email) ? true : false;
	}
	
	function _print(){
		if (typeof(_data.email) == 'string') {
			_node.find('.email .notExist').hide();
			_node.find('.email .exist').show().find('span').text(_data.email);
		} else {
			_node.find('.email .notExist').show();
			_node.find('.email .exist').hide();
		}
	}
	
	function init(){
		_node = $('#integration.profile');
		_node.find('.email .notExist #get').click(function(){
			if (typeof(_data.email) != 'string') {//special for super hackers =)) or not worked load(js-error or other bug)

				var email = (_node.find('.email .notExist #email').val() || '') + '@mail.easyfinance.ru';
				if (_validEmail(email)) {
					_model.setIntegrationEmail(email, function(data){
						if (data && data.result) {
							$.jGrowl(data.result.text, {
								theme: 'green'
							});
							load({
								service_mail: email
							})
						}
						if (!data || data.error) {
							$.jGrowl(data.error.text, {
								theme: 'red'
							});
							return;
						}
						load(data.profile || {});
					});//??
				} else {
					$.jGrowl('Вы ввели некоректный емайл!', {
						theme: 'red'
					});
				}
			}
		});
		_node.find('.email .exist .remove').click(function(){
			if (typeof(_data.email) == 'string') {//special for super hackers =)) or not worked load(js-error or other bug)

				_model.removeIntegrationEmail(function(data){// @todo
					if (data && data.result) {
						delete _data.email;
						_print();
						$.jGrowl(data.result.text, {
							theme: 'green'
						});
					}
					if (!data || data.error) {
						$.jGrowl(data.error.text, {
							theme: 'red'
						});
					}
				});
			}
		});
	}
	function load(data){
		if (typeof(data) != 'object') {
			data = {};
		}
		if (data.service_mail) {
			_data.email = data.service_mail;
		}
		_print();
	}
	return {
		init: init,
		load: load
	}
}(easyFinance.models.user);

