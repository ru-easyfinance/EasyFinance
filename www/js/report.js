// {* $Id$ *}
//swfobject.embedSWF("/swf/open-flash-chart.swf", "chart", "500", "500", "9.0.0" ,null, null, {menu:"false", wmode:"opaque"});
swfobject.embedSWF("/swf/open-flash-chart.swf", "chart", "500", "500", "9.0.0", "expressInstall.swf" );
var data = {
    "elements": [{
        "type": "pie",
        "alpha": 0.6,
        "start-angle": 35,
        "animate": [ {"type": "fade"} ],
        "colours": [ "#1C9E05", "#FF368D" ],
        "values": []
    }
  ]
};

function ofc_ready() {
    //alert('ofc_ready');
}

function open_flash_chart_data() {
    return JSON.stringify(data);
}

function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}

function ShowDetailedIncome(){
    $.get('/report/getData/',{
        report: $('#report :selected').attr('id'),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val(),
        account: $('#account :selected').val(),
        currency:$('#currency :selected').val()
        //dateFrom2: $('#dateFrom').val(),
        //dateTo2: $('#dateTo').val()
     }, function(data) {
        var tr = '';
        tr += '<tr><th>&nbsp;</th>\n\
                    <th><span class="sort" title="отсортировать">Дата</span></th>\n\
                    <th><span class="sort" title="отсортировать">Счёт</span></th>\n\
                    <th><span class="sort" title="отсортировать">Сумма</span></th>\n\
               </tr>';//*/
        for (c in data){
            if (c>0)
            if (data[c].cat_name != data[c-1].cat_name) {
                tr += "<tr>" + '<td class="summ"><span><b>'+data[c].cat_name+
                    '<span><b></td></tr>';
            }
            tr += "<tr>"
                        + '<td>&nbsp;</td>'
                        + '<td class="summ"><span>'+data[c].date+'</span></td>'
                        + '<td class="light"><span>'+data[c].account_name+'</span></td>'
                        + '<td class="big"><span>'+data[c].money+'</span></td>'
                        + '</tr>';
        }
        $('tr:not(:first)','#reports_list').each(function(){
                    $(this).remove();
                });
        $('#reports_list').append(tr);
    },'json');

}
function ShowDetailedWaste(){
    $.get('/report/getData/',{
        report: $('#report :selected').attr('id'),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val(),
        account: $('#account :selected').val(),
        currency:$('#currency :selected').val()
        //dateFrom2: $('#dateFrom').val(),
        //dateTo2: $('#dateTo').val()
     }, function(data) {
        var tr = '';
        tr += '<tr><th>&nbsp;</th>\n\
                    <th><span class="sort" title="отсортировать">Дата</span></th>\n\
                    <th><span class="sort" title="отсортировать">Счёт</span></th>\n\
                    <th><span class="sort" title="отсортировать">Сумма</span></th>\n\
               </tr>';//*/

        for (c in data){
            if (c>0)
            {if (data[c].cat_name != data[c-1].cat_name) {
                tr += "<tr>" + '<td class="summ"><span><b>'+data[c].cat_name+
                    '<span><b></td></tr>';
            }} else {
                tr += "<tr>" + '<td class="summ"><span><b>'+data[0].cat_name+
                    '<span><b></td></tr>';
            }
            if (data[c].cat_name != null){
            tr += "<tr>"
                        + '<td>&nbsp;</td>'
                        + '<td class="repdate"><span>'+data[c].date+'</span></td>'
                        + '<td class="repname"><span>'+data[c].account_name+'</span></td>'
                        + '<td class="repsumm"><span>'+data[c].money+'</span></td>'
                        + '</tr>';
            }
        }
        $('tr:not(:first)','#reports_list').each(function(){
                    $(this).remove();
                });
        $('#reports_list').append(tr);
    },'json');
}

function ShowCompareWaste(){
    $.get('/report/getData/',{
        report: $('#report :selected').attr('id'),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val(),
        account: $('#account :selected').val(),
        currency:$('#currency :selected').val(),
        dateFrom2: $('#dateFrom2').val(),
        dateTo2: $('#dateTo2').val()
     }, function(data) {
        var tr = '';
        tr += '<tr><th>&nbsp;</th>\n\
                    <th><span class="sort" title="отсортировать">Период 1</span></th>\n\
                    <th><span class="sort" title="отсортировать">Период 2</span></th>\n\
                    <th><span class="sort" title="отсортировать">Разница</span></th>\n\
               </tr>';//*/
        var sum1=0;
        var sum2=0;
        var delta=0;
        var ssum1=0;
        var ssum2=0;
        var sdelta=0;
        for (c in data){
            if (data[c].su != null){
            if (data[c].per == 1) {
                sum1=data[c].su;
                sum2=0;
                for (v in data){
                    if (data[v].cat_name == data[c].cat_name) 
                        if (data[v].per == 2){
                            sum2=data[v].su   ;
                            data[v].su = null;
                        }
                }
            } else {
                sum2 = data[c].su;
                sum1=0;
                for (v in data){
                    if (data[v].cat_name == data[c].cat_name)
                        if (data[v].per == 1){
                           sum1=data[v].su;
                           data[v].su = null;
                        }
                }
            };         
            delta=sum2-sum1;
           if (data[c].cat_name != null){
            tr +=        '<tr><td ><span><b>'+data[c].cat_name+
                        '<span><b></td>'+
                        '<td class="repdate"><span>'+sum1+'</span></td>'
                        + '<td class="repname"><span>'+sum2+'</span></td>'
                        + '<td class="repsumm"><span>'+delta+'</span></td>'
                        + '</tr>';
            ssum1 = parseFloat(ssum1) + parseFloat(sum1);
            ssum2 = parseFloat(ssum2) + parseFloat(sum2);
            sdelta += Math.round(delta);
           }
        }
        }
        tr +=        '<tr><td ><span><b>Итого:</span><b></td>'+
                        '<td class="repdate"><span>'+ssum1+'</span></td>'
                        + '<td class="repname"><span>'+ssum2+'</span></td>'
                        + '<td class="repsumm"><span>'+sdelta+'</span></td>'
                        + '</tr>';
        $('tr:not(:first)','#reports_list').each(function(){
                    $(this).remove();
                });
        $('#reports_list').append(tr);
    },'json');
}

function ShowCompareIncome(){
 
  }
       
function ShowIncome(){
    l = $.get('/report/getData/',{
        report: $('#report :selected').attr('id'),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val(),
        account: $('#account :selected').val(),
        currency:$('#currency :selected').val()
     }, function(data) {
            //var tmp = findSWF("chart");
            
            var tmp = findSWF('chart');
            
            alert(tmp.toString());
            //alert(d);
            x = tmp.load(JSON.stringify(data));
                //BaseReport();

    },'json');
}

function ShowAverageIncome(){
    $.get('/report/getData/',{
        report: $('#report :selected').attr('id'),
        dateFrom: $('#dateFrom').val(),
        dateTo: $('#dateTo').val(),
        account: $('#account :selected').val(),
        currency:$('#currency :selected').val(),
        dateFrom2: $('#dateFrom2').val(),
        dateTo2: $('#dateTo2').val()
     }, function(data) {
        var tr = '';
        tr += '<tr><th>&nbsp;</th>\n\
                    <th><span class="sort" title="отсортировать">Период 1</span></th>\n\
                    <th><span class="sort" title="отсортировать">Период 2</span></th>\n\
                    <th><span class="sort" title="отсортировать">Разница</span></th>\n\
               </tr>';//*/
        var sum1=0;
        var sum2=0;
        var delta=0;
        var ssum1=0;
        var ssum2=0;
        var sdelta=0;
        for (c in data[2]){
            if (data[2][c].su != null){
            if (data[2][c].per == 1) {
                sum1=data[2][c].su;
                sum2=0;
                for (v in data[2]){
                    if (data[2][v].cat_name == data[2][c].cat_name)
                        if (data[2][v].per == 2){
                            sum2=data[2][v].su * data[0] / data[1] ;
                            data[2][v].su = null;
                        }
                }
            } else {
                sum2 = data[2][c].su * data[0] * data[1];
                sum1=0;
                for (v in data[2]){
                    if (data[2][v].cat_name == data[2][c].cat_name)
                        if (data[2][v].per == 1){
                           sum1=data[2][v].su * data[0] * data[1];
                           data[2][v].su = null;
                        }
                }
            };
            delta=sum2-sum1;
            if (data[2][c].cat_name != null){
            tr +=        '<tr><td ><span><b>'+data[2][c].cat_name+
                        '<span><b></td>'+
                        '<td class="repdate"><span>'+sum1+'</span></td>'
                        + '<td class="repname"><span>'+sum2+'</span></td>'
                        + '<td class="repsumm"><span>'+delta+'</span></td>'
                        + '</tr>';
            ssum1 = Math.round(parseFloat(ssum1)) + Math.round(parseFloat(sum1));
            ssum2 = Math.round(parseFloat(ssum2)) + Math.round(parseFloat(sum2));
            sdelta += Math.round(delta);
            }
        }
        }
        tr +=        '<tr><td ><span><b>Итого:<span><b></td>'+
                        '<td class="repdate"><span>'+ssum1+'</span></td>'
                        + '<td class="repname"><span>'+ssum2+'</span></td>'
                        + '<td class="repsumm"><span>'+sdelta+'</span></td>'
                        + '</tr>';
        $('tr:not(:first)','#reports_list').each(function(){
                    $(this).remove();
                });
        $('#reports_list').append(tr);
    },'json');
}

function BaseReport(){
    switch ( $('#report :selected').val() ) {
        case "Доходы":
            ShowIncome();
        break;
        case "Расходы":
            ShowIncome();
        break;
        case "Сравнение расходов и доходов":
            ShowIncome();
        break;
        case "Детальные доходы":
            ShowDetailedIncome();
        break;
        case "Детальные расходы":
            ShowDetailedWaste();
        break;

        case "Сравнение расходов за периоды":
            ShowCompareWaste();
        break;
        case "Сравнение доходов за периоды":
            ShowCompareWaste();
        break;

        case "Сравнение доходов со средним за периоды":
            ShowAverageIncome();
        break;
        case "Сравнение расходов со средним за периоды":
            ShowAverageIncome();
        break;
    }
}

$(window).load(function() {
    $('#Period21').hide();
    $('#Period22').hide();
    //var reportList;
    $('#dateFrom,#dateTo,#dateFrom2,#dateTo2').datepicker({dateFormat: 'dd.mm.yy'});
    $('#btnShow').click(function(){
        /*l = $.get('/report/getData/', {
            report: $('#report :selected').attr('id'),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            dateFrom2: $('#dateFrom2').val(),
            dateTo2: $('#dateTo2').val(),
            account: $('#account :selected').val(),
            currency: $('#currency :selected').val()
        }, function(d) {
            //tmp = findSWF("chart");
            //x = tmp.load(JSON.stringify(d));
                BaseReport();
            
        }, 'json');*/
        BaseReport();
    });


//Показ и скрытие дополнительных полей при выборе типа отчёта
    $('#report').change( function(){
    switch ( $('#report :selected').val() ) {
        case "Доходы":
            $('#Period21').show();
            $('#Period22').show();
        case "Расходы":
            $('#Period21').show();
            $('#Period22').show();
        case "Сравнение расходов и доходов":
            $('#Period21').show();
            $('#Period22').show();
        case "Детальные доходы":
            //alert("дох");
            $('#Period21').hide();
            $('#Period22').hide();
        break;
        case "Детальные расходы":
            $('#Period21').hide();
            $('#Period22').hide();
        break;

        case "Сравнение расходов за периоды":
            $('#Period21').show();
            $('#Period22').show();
        break;
        case "Сравнение доходов за периоды":
            $('#Period21').show();
            $('#Period22').show();
        break;

        case "Сравнение доходов со средним за периоды":
            $('#Period21').show();
            $('#Period22').show();
        break;
        case "Сравнение расходов со средним за периоды":
           $('#Period21').show();
            $('#Period22').show();
        break;
    }
    
    });

});