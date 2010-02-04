/**
 * @desc Calendar Model
 * @author Alexandr [rewle] Ilichov
 */
easyFinance.models.calendar = function(){
    var _data;
    var _calbacks = {};

    function addCalback(calback,widget){
         _calbacks[widget] = calback;
    }

    function load(data){
        var calback
        if (typeof data != 'object'){
            $.get('/calendar/loadlist/',{},function(data){
                _data = data;
                for (var key in _calbacks){
                    calback = _calbacks[key];
                    easyFinance.widgets[key][calback](data)
                }
            },'json')
        }else{
            _data = data;
            for (var key in _calbacks){
                calback = _calbacks[key];
                easyFinance.widgets[key][calback](data)
            }
        }
    }
    function add(){
        $.post('/calendar/add/',{data:data},function(data){
            load();
        },'json')
    }

    function edit(data){
        $.post('/calendar/edit/',{data:data},function(data){
            load();
        },'json')
    }

    function del(id,chain){
        $.post('/calendar/add/',{id : id ,chain : chain},function(data){
            load();
        },'json')
    }

    function getList(){
        return _data;
    }

}

