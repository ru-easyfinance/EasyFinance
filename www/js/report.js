// {* $Id$ *}
swfobject.embedSWF("/swf/open-flash-chart.swf", "chart", "500", "500", "9.0.0" ,null, null, {menu:"false", wmode:"opaque"});
var data = {
    "elements": [{
        "type": "pie",
        "alpha": 0.6,
        "start-angle": 35,
        "animate": [ { "type": "fade" } ],
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

$(window).load(function() {
    $('#dateFrom,#dateTo').datepicker({dateFormat: 'dd.mm.yy'});
    $('#btnShow').click(function(){
        l = $.get('/report/getData/', {
            report: $('#report :selected').attr('id'),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            account: $('#account :selected').val(),
            currency: $('#currency :selected').val()
        }, function(d) {
            tmp = findSWF("chart");
            x = tmp.load( JSON.stringify(d));
        }, 'json');
    });
});