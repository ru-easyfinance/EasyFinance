easyFinance.widgets.userIntegrations = function(model){
	var _model = model || easyFinance.models.user;
	var _node;
	var _data = {};
	
	function _print(){
		if (typeof(_data.email) == 'string'){
			_node.find('.email .notExist').hide();
			_node.find('.email .exist').show().find('span').text(_data.email);
		}else{
			_node.find('.email .notExist').show();
			_node.find('.email .exist').hide();
		}
	}

	function init(){
		_node = $('#integration.profile');
		_node.find('.email .notExist #get').click(function(){
			if (typeof(_data.email) != 'string') {//special for super hackers =)) or not worked load(js-error or other bug)
				_model.getIntegrationEmail(function(){
					if (data && data.result){
						$.jGrowl(data.result.text,{theme : 'green'});
					}
					if(!data || data.error){
						$.jGrowl(data.error.text, {theme : 'red'});
						return;
					}
					load(data.profile || {});
				});//??
			}
		});
		_node.find('.email .exist .remove').click(function(){
			if (typeof(_data.email) == 'string') {//special for super hackers =)) or not worked load(js-error or other bug)
				_model.removeIntegrationEmail(function(data){// @todo
					if (data && data.result){
						delete _data.email;
						_print();
						$.jGrowl(data.result.text,{theme : 'green'});
					}
					if(!data || data.error){
						$.jGrowl(data.error.text, {theme : 'red'});
					}
				});
			}
		});
	}
	function load(data){
		if (typeof(data) != 'object'){
			data = {};
		}
		if(data.service_mail){
			_data.email = data.service_mail;
		}
		_print();
	}
	
	return {
		init: init,
		load: load
	}
}();

$(document).ready(function(){
	easyFinance.widgets.userIntegrations.init();
	easyFinance.widgets.userIntegrations.load({});
})
