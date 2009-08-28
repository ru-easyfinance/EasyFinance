// {* $Id$ *}
$(window).load(function() {
    $('#dateFrom,#dateTo').datepicker();
    $('#btnShow').click(function(){
        l = $.getJSON('/report/getData/', {
            report: $('#report'),
            dateFrom: $('#dateFrom'),
            dateStart: $('#dateStart'),
            account: $('#account'),
            currency: $('#currency')
        }, function(d) {
            tmp = findSWF("chart");
            x = tmp.load( JSON.stringify(d) );
        });
    });
});

swfobject.embedSWF("/swf/open-flash-chart.swf", "chart", "500", "500", "9.0.0");
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
    alert('asd');
    return JSON.stringify(data);
}

function findSWF(movieName) {
  if (navigator.appName.indexOf("Microsoft")!= -1) {
    return window[movieName];
  } else {
    return document[movieName];
  }
}


