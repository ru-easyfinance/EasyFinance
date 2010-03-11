easyFinance.models.report = function(){
    function load(requestData, callback){
        $.get(
            '/report/getData/?responseMode=json',
            requestData,
            function(data) {
                if (typeof(callback) == 'function'){
                    callback(data);
                }
            },
            'json'
        );
    }
    return{
        load : load
    }
}();