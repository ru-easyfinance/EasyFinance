easyFinance.widgets.calendarList = function(){
    var _model;
    function init(model){
        _model = model
        //events
    }

    function print(){
//        var _data = _model.getList();
        var _data = {};
        var n = new Date();
        var eventsList = '', periodicList = '', t = '';
        for(var key in _data){
            var event = _data[key];
            n.setTime(event.date*1000);
            var accept  = event.accept?'+':'-'
            // Если это событие
            if (event.type == 'e') {
                eventsList += '<tr id="ev_+'+event.id+'"><td class="chk"><input type="checkbox" value="" /></td>'
                        +'<td>'+$.datepicker.formatDate('dd.mm.yy',n)+n.toLocaleTimeString().substr(0, 5)+'</td>'
                        +'<td>'+event.title+'</td>'
                        +'<td>'+accept+'</td>'
                        +'</tr>';
            // Если периодическая транзакция
            } else {
                periodicList += '<tr id="ev_+' + event.id + '"><td class="chk"><input type="checkbox" value="" /></td>'
                    + '<td>' + $.datepicker.formatDate('dd.mm.yy',n) + '</td>'
                    + '<td>' + event.title + '</td>'
                    + '<td>' + event.amount + '</td>'
                    + '<td>'+accept+'</td>'
                    + '</tr>';
            }
                
        }
        $('#ev_tabl tbody').html(eventsList);
        $('#per_tabl tbody').html(periodicList);
    }
    return{init: init,
        print: print}
}