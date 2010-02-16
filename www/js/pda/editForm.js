function inputTypeIsText(input){
    var attrs = input.attributes;
    for(var key in attrs){
        if(typeof(attrs[key]) == 'object' &&
            attrs[key].nodeName == 'type' &&
            attrs[key].nodeValue != 'text'){
            return false;
        }
    }
    return true;
}
window.onload = function(){
    var inputList = document.getElementsByTagName('input');
    for(var key in inputList){
        if(inputTypeIsText(inputList[key])){
            var e;
            inputList[key].onkeypress = function(e){
                var eKey = typeof(e) == 'object' ? e.keyCode : window.event.keyCode
                if (eKey == 13){
                    return false;
                }
                return true;
            };
        }
    }
};