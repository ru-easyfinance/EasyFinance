easyFinance.models.currency = function(){
    var _data =  {};
	var _defaultCurrencyId = -1;
	
	var URL_FOR_SAVE_USER_CURRENCY = '/profile/save_currency/?responseMode=json';
	var URL_FOR_LOAD_ALL_CURRENCY = '/profile/load_currency/?responseMode=json';
    /**
     * Инициирует модель валют
     * @param data {object} - например, из res.currency
     */
    function load(data){
		try {
			_defaultCurrencyId = data['default'];
			delete data['default'];
	        for (var key in data){
	            _data[key] = $.extend({id: key},data[key]);
	        }
		}catch (e){
			_data = {}
			_defaultCurrencyId = -1;
		}
    }
    /**
     * Возвращает валюту по умолчанию
     * @return {cost, name, progress, text , notFound, id}
     */
    function getDefaultCurrency(){
        return _data[_defaultCurrencyId] || {cost : 0, name : '', progress : '', text : '', notFound : true, id: 0};
    }
    /**
     * Возвращает id валюты по умолчанию
     * @return {integer}
     */
    function getDefaultCurrencyId(){
        return _defaultCurrencyId;
    }
    /**
     * Возвращает курс валюты по умолчанию
     * @return {float}
     */
    function getDefaultCurrencyCost(){
        return _data[_defaultCurrencyId] ? _data[_defaultCurrencyId].cost : 0;
    }
    /**
     * Возвращает описание валюты по умолчанию
     * @return {string}
     */
    function getDefaultCurrencyText(){
        return _data[_defaultCurrencyId] ? _data[_defaultCurrencyId].text : '';
    }

    /**
     * Возвращает валюту в зависимости от Id
     * @param id {number}
     * @return {cost, name, progress, text, notFound}
     */
    function getCurrencyById(id){
        return _data[id] || {cost : 0, name : '', progress : '', text : '', notFound : true};
    }
    /**
     * Возвращает название валюты в зависимости от Id
     * @param id {number}
     * @return {string}
     */
    function getCurrencyNameById(id){
        return _data[id] ? _data[id].name : '';
    }
    /**
     * Возвращает описание валюты в зависимости от Id
     * @param id {number}
     * @return {string}
     */
    function getCurrencyTextById(id){
        return _data[id] ? _data[id].text : '';
    }
    /**
     * Возвращает стоимость валюты в зависимости от Id
     * @param id {Number}
     * @return {number}
     */
    function getCurrencyCostById(id){
        return _data[id] ? _data[id].cost : 0;
    }
    /**
     * Возвращает курс валюты Id относительно валюты defaultId
     * @param id {Number}
     * @param defaultId {Number}
     * @return {Number}
     */
    function getCurrencyRelativeCost(id, defaultId){
        return (_data[id] &&  _data[defaultId])? (getCurrencyCostById(id)/getCurrencyCostById(defaultId)) : 0;
    }
    /**
     * Возвращает список валют
     * @return {object}
     */
    function getCurrencyList(){
        return $.extend({}, _data, true);
    }

    /**
     * Сохраняет валюты пользователя
     * @param saveData {object}
     * @param calback {function}
     * @return void
     */
    function setCurrency(saveData, calback){
        $.post(URL_FOR_SAVE_USER_CURRENCY,
            saveData,
            function(data){
                load(saveData);
                if (typeof(calback) == 'function'){
                    calback(data);
                }
        },
        'json');
    }

	function loadAllCurrency(callback){
		$.get(URL_FOR_LOAD_ALL_CURRENCY,
            {},
            function(data){
				if (typeof(callback)=='function'){
					callback(data);
				}
            },
            'json'
        );
	}

    return {
        load : load,
        getDefaultCurrency : getDefaultCurrency,
        getDefaultCurrencyId : getDefaultCurrencyId,
        getDefaultCurrencyText: getDefaultCurrencyText,
        getDefaultCurrencyCost: getDefaultCurrencyCost,
        getCurrencyList : getCurrencyList,
        getCurrencyById : getCurrencyById,
        getCurrencyNameById : getCurrencyNameById,
        getCurrencyTextById :getCurrencyTextById,
        getCurrencyCostById: getCurrencyCostById,
        getCurrencyRelativeCost: getCurrencyRelativeCost,
        setCurrency : setCurrency,
		loadAllCurrency : loadAllCurrency
    };
}();