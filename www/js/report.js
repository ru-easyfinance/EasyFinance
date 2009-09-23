// {* $Id$ *}
swfobject.embedSWF("/swf/open-flash-chart.swf", "chart", "500", "500", "9.0.0" ,null, null, {menu:"false", wmode:"opaque"});
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
        //delete reportList;
        //reportList = $.extend(data);
        var tr = '';
        for (c in data){
            if (c>0)
            if (data[c].cat_name != data[c-1].cat_name) {
                tr += "<tr>" + '<td class="summ"><span><b>'+data[c].cat_name+
                    '<span><b></td></tr>';
            }
            tr += "<tr>"
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

    /*var tr = '';
    tr += "<tr value='1'>"
                        + '<td class="summ"><span><b>'+"123"+'</b></span></td>'
                        + '<td class="light"><span>'+"234"+'</span></td>'
                        + '<td class="big"><span>'+"345"+'</span></td>'
                        '</tr>';
    $('#reports_list').append(tr);*/
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
        //delete reportList;
        //reportList = $.extend(data);
        //$courseUp = Core::getInstance()->currency[2]['value'];
        var tr = '';
        for (c in data){
            if (c>0)
            {if (data[c].cat_name != data[c-1].cat_name) {
                tr += "<tr>" + '<td class="summ"><span><b>'+data[c].cat_name+
                    '<span><b></td></tr>';
            }} else {
                tr += "<tr>" + '<td class="summ"><span><b>'+data[0].cat_name+
                    '<span><b></td></tr>';
            }
            tr += "<tr>"
                        + '<td class="repdate"><span>'+data[c].date+'</span></td>'
                        + '<td class="repname"><span>'+data[c].account_name+'</span></td>'
                        + '<td class="repsumm"><span>'+data[c].money+'</span></td>'
                        + '</tr>';
        }
        $('tr:not(:first)','#reports_list').each(function(){
                    $(this).remove();
                });
        $('#reports_list').append(tr);
    },'json');
}


function BaseReport(){
    switch ( $('#report :selected').val() ) {
        case "Детальные доходы":
            ShowDetailedIncome();
        break;
        case "Детальные расходы":
            ShowDetailedWaste();
        break;

        case "Сравнение расходов за периоды":
            
        break;
        case "Сравнение доходов за периоды":
            
        break;

        case "Сравнение доходов со средним за периоды":
            
        break;
        case "Сравнение расходов со средним за периоды":
            
        break;
    }
}

$(window).load(function() {
    $('#Period21').hide();
    $('#Period22').hide();
    //var reportList;
    $('#dateFrom,#dateTo').datepicker({dateFormat: 'dd.mm.yy'});
    //$('#buttonShow').click(function(){ BaseReport();})
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