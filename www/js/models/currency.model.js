easyFinance.models.currency = function(){
    var _data, _defaultCurrencyId;
    /**
     * Инициирует модель валют
     * @param data == res.currency
     */
    function init(data){
        _data = data;
        _defaultCurrencyId = data['default'];
    }
    /**
     * Возвращает валюту по умолчанию
     * @return {cost : int, name : str, progress : str, text : str, ?notFound : true}
     */
    function getDefaultCurrency(){
        return _data[_defaultCurrencyId] || {cost : 0, name : '', progress : '', text : '', notFound : true};
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
     * @return {cost : int, name : str, progress : str, text : str, ?notFound : true}
     */
    function getCurrencyNameById(id){
        return _data[id] ? _data[_defaultCurrencyId].name : '';
    }
    /**
     * Возвращает описание валюты в зависимости от Id
     * @param id {int}
     * @return {cost : int, name : str, progress : str, text : str, ?notFound : true}
     */
    function getCurrencyTextById(id){
        return _data[id] ? _data[_defaultCurrencyId].text : '';
    }

    return {
        init : init,
        getDefaultCurrency : getDefaultCurrency,
        getCurrencyById : getCurrencyById,
        getCurrencyNameById : getCurrencyNameById,
        getCurrencyTextById :getCurrencyTextById
    }
}