easyFinance.models.currency = function(){
    var _data = {}, _defaultCurrencyId;
    /**
     * Инициирует модель валют
     * @param data - например, из res.currency
     */
    function load(data){
        _defaultCurrencyId = data['default'];
        _data = {};
        delete data['default'];
        for (var key in data){
            _data[key] = $.extend({id: key},data[key]);
        }
    }
    /**
     * Возвращает валюту по умолчанию
     * @return {cost : int, name : str, progress : str, text : str, ?notFound : true}
     */
    function getDefaultCurrency(){
        return _data[_defaultCurrencyId] || {cost : 0, name : '', progress : '', text : '', notFound : true, id: 0};
    }
    /**
     * Возвращает id валюты по умолчанию
     * @return {int}
     */
    function getDefaultCurrencyId(){
        return _defaultCurrencyId;
    }
    /**
     * Возвращает курс валюты по умолчанию
     * @return {float}
     */
    function getDefaultCurrencyCost(){
        return _data[_defaultCurrencyId].cost;
    }
    /**
     * Возвращает описание валюты по умолчанию
     * @return {string}
     */
    function getDefaultCurrencyText(){
        return _data[_defaultCurrencyId].text;
    }

    /**
     * Возвращает валюту в зависимости от Id
     * @param id {int}
     * @return {cost : int, name : str, progress : str, text : str, ?notFound : true}
     */
    function getCurrencyById(id){
        return _data[id] || {cost : 0, name : '', progress : '', text : '', notFound : true};
    }
    /**
     * Возвращает название валюты в зависимости от Id
     * @param id {int}
     * @return {str}
     */
    function getCurrencyNameById(id){
        return _data[id] ? _data[id].name : '';
    }
    /**
     * Возвращает описание валюты в зависимости от Id
     * @param id {int}
     * @return {str}
     */
    function getCurrencyTextById(id){
        return _data[id] ? _data[id].text : '';
    }
    /**
     * Возвращает стоимость валюты в зависимости от Id
     * @param id {int}
     * @return {float}
     */
    function getCurrencyCostById(id){
        return _data[id] ? _data[id].cost : 0;
    }
    /**
     * Возвращает курс валюты Id относительно валюты defaultId
     * @param id {int}
     * @param defaultId {int}
     * @return {float}
     */
    function getCurrencyRelativeCost(id, defaultId){
        return (_data[id] &&  _data[defaultId])? (getCurrencyCostById(id)/getCurrencyCostById(defaultId)) : 0;
    }
    /**
     * Возвращает список валют
     * @return {obj}
     */
    function getCurrencyList(){
        return $.extend({}, _data, true);
    }

    /**
     * Сохраняет валюты пользователя
     * @param saveData {obj}
     * @param calback {fn}
     * @return void
     */
    function setCurrency(saveData, calback){
        $.post('/profile/save_currency/?responseMode=json',
            saveData,
            function(data){
                load(saveData);
                if (typeof(calback) == 'function'){
                    calback(data);
                }
        },
        'json');
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
        setCurrency : setCurrency
    };
}();