// {* $Id$ *}
function formatCurrencyFusion(num) {
    if (num=='undefined') num = 0;
    //num = num.toString().replace(/\$|\,/g,'');
    if(isNaN(num)) num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
        cents = "0" + cents;
    /*for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
        num = num.substring(0,num.length-(4*i+3))+' '+
        num.substring(num.length-(4*i+3));*/
    return (((sign)?'':'-') + '' + num + '.' + cents);
}

$(document).ready(function() {
    acc = new Array();
    for (a in res['accounts']){
                 //alert(a);
                 acc[acc.length] = a;
             }
    /*for (a in acc){
        alert(acc.toString());
    }*/
    function ShowDetailedIncome(){
        $.get('/report/getData/',{
            report: $('#report :selected').attr('id'),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            account: $('#account :selected').val(),
            currency:$('#currency :selected').val(),
            acclist: //res['accounts']
            acc.toString()
            //dateFrom2: $('#dateFrom').val(),
            //dateTo2: $('#dateTo').val()
         }, function(data) {
             /*for (a in res['accounts']){
                 alert(a);
             }*/

             cur = res['currency'];
             nowcur = 0;//курс в знаменателе. в чём отображаем
             oldcur = 0;//курс в числителе. курс валюты счёта
             for(key in cur)
            {
                cost = cur[key]['cost'];
                name = cur[key]['name'];
                if (name == data[1][0].cur_char_code){
                    nowcur = cur[key]['cost'];
                }
            }
            var tr = '';
            tr += '<tr><th>&nbsp;</th>\n\
                        <th><span class="sort" title="отсортировать">Дата</span></th>\n\
                        <th><span class="sort" title="отсортировать">Счёт</span></th>\n\
                        <th><span class="sort" title="отсортировать">Сумма</span></th>\n\
                   </tr>';//*/
            for (c in data[0]){
                if (c>0){
                    if (data[0][c].cat_name != data[0][c-1].cat_name && data[0][c].cat_name!=null ) {
                      if (data[0][c].account_name!=null)
                      tr += "<tr>" + '<td class="summ"><span><b>'+data[0][c].cat_name+
                        '<span><b></td></tr>';
                     }
                }
                else {
                    tr += "<tr>" + '<td class="summ"><span><b>'+data[0][0].cat_name+
                        '<span><b></td></tr>';
                }
                if (data[0][c].account_name != null) {
                    cur = res['currency'];
                    for(key in cur)
                        {
                            cost = cur[key]['cost'];
                            name = cur[key]['name'];
                            if (name == data[0][c].cur_char_code)
                            {
                                oldcur = cur[key]['cost'];
                            }
                        }
                        su = (data[0][c].money*oldcur/nowcur);
                    tr += "<tr>"
                                + '<td>&nbsp;</td>'
                                + '<td class="summ"><span>'+data[0][c].date+'</span></td>'
                                //+ '<td class="summ ' + (data[0][c].date>=0 ? 'sumGreen' : 'sumRed')+'">'+data[0][c].date+'</td>'
                                + '<td class="light"><span>'+data[0][c].account_name+'</span></td>'
                                + '<td class="summ ' + (su>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(su)+'</span></td>'
                                + '</tr>';
                }
            }

            $('tr:not(:first)','#reports_list').each(function(){
                        $(this).remove();
                    });
             $('#reports_list').html(tr);
             //$('.operation_list').jScrollPane();
        },'json');

    }
    function ShowDetailedWaste(){
        $.get('/report/getData/',{
            report: $('#report :selected').attr('id'),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            account: $('#account :selected').val(),
            currency:$('#currency :selected').val(),
            acclist: acc.toString()
            //dateFrom2: $('#dateFrom').val(),
            //dateTo2: $('#dateTo').val()
         }, function(data) {
             cur = res['currency'];
             nowcur = 0;//курс в знаменателе. в чём отображаем
             oldcur = 0;//курс в числителе. курс валюты счёта
             for(key in cur)
            {
                cost = cur[key]['cost'];
                name = cur[key]['name'];
                if (name == data[1][0].cur_char_code){
                    nowcur = cur[key]['cost'];
                }
            }

            var tr = '';
            tr += '<tr><th>&nbsp;</th>\n\
                        <th><span class="sort" title="отсортировать">Дата</span></th>\n\
                        <th><span class="sort" title="отсортировать">Счёт</span></th>\n\
                        <th><span class="sort" title="отсортировать">Сумма</span></th>\n\
                   </tr>';//*/

            for (c in data[0]){
                if (c>0)
                {if (data[0][c].cat_name != data[0][c-1].cat_name) {
                        if (data[0][c].account_name != null)
                    tr += "<tr>" + '<td class="summ"><span><b>'+data[0][c].cat_name+
                        '<span><b></td></tr>';
                }} else {
                    tr += "<tr>" + '<td class="summ"><span><b>'+data[0][0].cat_name+
                        '<span><b></td></tr>';
                }
                if (data[0][c].cat_name != null && data[0][c].account_name != null){
                cur = res['currency'];
                    for(key in cur)
                        {
                            cost = cur[key]['cost'];
                            name = cur[key]['name'];
                            if (name == data[0][c].cur_char_code)
                            {
                                oldcur = cur[key]['cost'];
                            }
                        }
                        su = (data[0][c].money*oldcur/nowcur);
                tr += "<tr>"
                            + '<td>&nbsp;</td>'
                            + '<td class="repdate"><span>'+data[0][c].date+'</span></td>'
                            + '<td class="repname"><span>'+data[0][c].account_name+'</span></td>'
                            + '<td class="summ ' + (su>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(su)+'</span></td>'
                            + '</tr>';
                }
            }
            $('tr:not(:first)','#reports_list').each(function(){
                        $(this).remove();
                    });
            $('#reports_list').html(tr);
            //$('.operation_list').jScrollPane();
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
            dateTo2: $('#dateTo2').val(),
            acclist: acc.toString()
         }, function(data) {
             cur = res['currency'];
             nowcur = 0;//курс в знаменателе. в чём отображаем
             oldcur = 0;//курс в числителе. курс валюты счёта
             for(key in cur)
            {
                cost = cur[key]['cost'];
                name = cur[key]['name'];
                if (name == data[1][0].cur_char_code){
                    nowcur = cur[key]['cost'];
                }
            }
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
            for (c in data[0]){
                if (data[0][c].su != null){
                if (data[0][c].per == 1) {
                    sum1=data[0][c].su;
                    sum2=0;
                    for (v in data[0]){
                        if (data[0][v].cat_name == data[0][c].cat_name)
                            if (data[0][v].per == 2){
                                sum2=data[0][v].su   ;
                                data[0][v].su = null;
                            }
                    }
                } else {
                    sum2 = data[0][c].su;
                    sum1=0;
                    for (v in data[0]){
                        if (data[0][v].cat_name == data[0][c].cat_name)
                            if (data[0][v].per == 1){
                               sum1=data[0][v].su;
                               data[0][v].su = null;
                            }
                    }
                }
                delta=sum2-sum1;
               if (data[0][c].cat_name != null && data[0][c].cur_char_code != null){
                   cur = res['currency'];
                    for(key in cur)
                        {
                            cost = cur[key]['cost'];
                            name = cur[key]['name'];
                            if (name == data[0][c].cur_char_code)
                            {
                                oldcur = cur[key]['cost'];
                            }
                        }
                tr +=        '<tr><td ><span><b>'+data[0][c].cat_name+
                            '<span><b></td>'+
                            '<td class="summ ' + (sum1*oldcur/nowcur>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(sum1*oldcur/nowcur)+'</span></td>'
                            + '<td class="summ ' + (sum2*oldcur/nowcur>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(sum2*oldcur/nowcur)+'</span></td>'
                            + '<td class="summ ' + (delta*oldcur/nowcur>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(delta*oldcur/nowcur)+'</span></td>'
                            + '</tr>';
                ssum1 = parseFloat(ssum1) + parseFloat(sum1*oldcur/nowcur);
                ssum2 = parseFloat(ssum2) + parseFloat(sum2*oldcur/nowcur);
                sdelta += Math.round(delta);
               }
             }
            }
            tr +=        '<tr><td ><span><b>Итого:</span><b></td>'+
                            '<td class="summ ' + (ssum1>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(ssum1)+'</span></td>'
                            + '<td class="summ ' + (ssum2>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(ssum2)+'</span></td>'
                            + '<td class="summ ' + (sdelta>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(sdelta)+'</span></td>'
                            + '</tr>';
            $('tr:not(:first)','#reports_list').each(function(){
                        $(this).remove();
                    });
            $('#reports_list').html(tr);
            //$('.operation_list').jScrollPane();
        },'json');
    }

    function ShowCompareIncome(){
        $('#chart').empty();
        l = $.get('/report/getData/',{
            report: $('#report :selected').attr('id'),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            account: $('#account :selected').val(),
            currency:$('#currency :selected').val(),
            acclist: acc.toString()
         }, function(data) {
            //var plot2 = $.jqplot('chart', [data['p'], data['d']], {
            /*var plot2 = $.jqplot('chart', [data], {
                //legend:{show:true, location:'ne', xoffset:55},
                //title:'Сравнение расходов и доходов',
                seriesDefaults:{
                    //renderer:$.jqplot.BarRenderer,
                    renderer:$.jqplot.PieRenderer,
                    //rendererOptions:{barPadding: 8, barMargin: 20}
                    rendererOptions:{
                        sliceMargin:8
                    }
                },*/
                /*series:[
                    {label:'Расходы'},
                    {label:'Доходы'},
                ],
                axes:{
                    xaxis:{
                        renderer:$.jqplot.CategoryAxisRenderer,
                        ticks: data['labels']
                    },
                    yaxis:{min:0}
                }*/
             /*legend:{show:true}
            });*/
            //возвращаемые данные имеют следующий формат . лейбл и сумма. например:
            //data[c]['lab'] = "Расходы за октябрь"
            //data[c]['sum'] = "4200"
            /*
             * @todo дописать формирование отчётов.
             * работает если есть доходы и расходы в одном и том же месяце.
             * а если например в марте доходы , а в мае расходы то не корректно.
             */
            var cur = res['currency'];
             nowcur = 0;//курс в знаменателе. в чём отображаем
             oldcur = 0;//курс в числителе. курс валюты счёта
             for(key in cur)
            {
                cost = cur[key]['cost'];
                name = cur[key]['name'];
                if (name == data[1][0].cur_char_code){
                    nowcur = cur[key]['cost'];
                }
            }

        var  a=0;//половина возвращаемых данных. индекс.
            for (c in data[0]){
              a = a + 1;
          }
          //a = a/2;

          //stri - строка xml, которую пихаем в fusionchart

         var stri = "<div id='chart1div'>FusionCharts</div>";
          $('#chart').html(stri);

          stri = "<chart numberPrefix='"+$('#currency :selected').attr('abbr')+" '>";
          stri += "<categories>";
          for (c in data[0]){
             if ( data[0][c]['was'] != 0 || data[0][c]['in'] != 0)
                 stri += "<category label='"+data[0][c]['lab']+"'  />";

          }
          stri += "</categories>";
          stri += "<dataset seriesName='Доходы'>";
          for (c in data[0]){
              if ( data[0][c]['was'] != 0 || data[0][c]['in'] != 0){
                  cur = res['currency'];
                    for(key in cur)
                        {
                            cost = cur[key]['cost'];
                            name = cur[key]['name'];
                            if (name == data[0][c]['curs'])
                            {
                                oldcur = cur[key]['cost'];
                            }
                        }
                  stri += "<set value='"+formatCurrencyFusion(data[0][c]['in']*oldcur/nowcur)+"'  />";
              }
          }
          stri += "</dataset>";
          stri += "<dataset seriesName='Расходы'>";
          for (c in data[0]){
             if ( data[0][c]['was'] != 0 || data[0][c]['in'] != 0){
                  cur = res['currency'];
                    for(key in cur)
                        {
                            cost = cur[key]['cost'];
                            name = cur[key]['name'];
                            if (name == data[0][c]['curs'])
                            {
                                oldcur = cur[key]['cost'];
                            }
                        }
                        //alert(data[0][c]['curs']);
                        //alert(oldcur);
                  stri += "<set value='"+formatCurrencyFusion(-data[0][c]['was']*oldcur/nowcur)+"'  />";
             }
          }
          stri += "</dataset>";

          /*for (c in data){
              if (data[c]['cat'] != '')
                stri += "<set label='"+data[c]['lab']+"' value='"+data[c]['sum']+"' />";
                    //stri += "<set label='Пам' value='']+"' />";
          }*/

          stri += "</chart>";
          //alert(stri);
            var chart1 = new FusionCharts("/swf/fusioncharts/MSColumn3D.swf", "chart1Id", "500", "400", "0", "1");
            chart1.addParam("WMode", "Transparent");
            chart1.setDataXML(stri);
            chart1.render("chart1div");//*/MSColumn3D
         }, 'json');
    }

    function ShowIncome(){
        $('#chart').empty();
        l = $.get('/report/getData/',{
            report: $('#report :selected').attr('id'),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            account: $('#account :selected').val(),
            currency:$('#currency :selected').val(),
            acclist: acc.toString()
         }, function(data) {
             cur = res['currency'];
             nowcur = 0;//курс в знаменателе. в чём отображаем
             oldcur = 0;//курс в числителе. курс валюты счёта
             for(key in cur)
            {
                cost = cur[key]['cost'];
                name = cur[key]['name'];
                if (name == data[1][0].cur_char_code){
                    nowcur = cur[key]['cost'];
                }
            }

            /*var plot2 = $.jqplot('chart', [data], {
                seriesDefaults:{
                    renderer:$.jqplot.PieRenderer,
                    rendererOptions:{
                        sliceMargin:8
                    }
                },
                legend:{show:true}*/
         var stri = "<div id='chart1div'>FusionCharts</div>";
          $('#chart').html(stri);
          stri = "<chart numberPrefix='"+$('#currency :selected').attr('abbr')+" '>";
          for (c in data[0]){
              if (data[0][c]['cat'] != ''){
                  cur = res['currency'];
                    for(key in cur)
                        {
                            cost = cur[key]['cost'];
                            name = cur[key]['name'];
                            if (name == data[0][c].cur_char_code)
                            {
                                oldcur = cur[key]['cost'];
                            }
                        }
                  stri += "<set label='"+data[0][c]['cat']+"' value='"+formatCurrencyFusion(data[0][c]['money']*oldcur/nowcur)+"' />";
              }
          }
          stri += "</chart>";
          //alert(stri);
          //stri = "<chart><set label='Домашнее хозяйство' value='500.06' /><set label='Аренда автомобиля' value='55' /><set label='Бонусы' value='1100' /><set label='Доход предпринимателя' value='1.03' /><set label='test' value='1050.07' /><set label='тетс chromeа' value='16' /><set label='rwefesr' value='4' /><set label='fewfew5345' value='1002' /></chart>"
          //stri = "<chart><set label='A' value='10' /><set label='B' value='11' /></chart>";
            var chart1 = new FusionCharts("/swf/fusioncharts/Pie3D.swf", "chart1Id", "500", "400", "0", "1");
            chart1.addParam("WMode", "Transparent");
            chart1.setDataXML(stri);
            chart1.render("chart1div");//*/

            //$('.operation_list').jScrollPane();
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
            dateTo2: $('#dateTo2').val(),
            acclist: acc.toString()
         }, function(data) {
             cur = res['currency'];
             nowcur = 0;//курс в знаменателе. в чём отображаем
             oldcur = 0;//курс в числителе. курс валюты счёта
             for(key in cur)
            {
                cost = cur[key]['cost'];
                name = cur[key]['name'];
                if (name == data[3][0].cur_char_code){
                    nowcur = cur[key]['cost'];
                }
            }
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
                    sum2 = data[2][c].su * data[0] / data[1];
                    sum1=0;
                    for (v in data[2]){
                        if (data[2][v].cat_name == data[2][c].cat_name)
                            if (data[2][v].per == 1){
                               sum1=data[2][v].su * data[0] / data[1];
                               data[2][v].su = null;
                            }
                    }
                };
                delta=sum2-sum1;
                if (data[2][c].cat_name != null && data[2][c].cur_char_code != null){// проверка на валюту чтобы отсеять мусор, те операции что не удалены рпи создании счёта. 
                cur = res['currency'];
                    for(key in cur)
                        {
                            cost = cur[key]['cost'];
                            name = cur[key]['name'];
                            if (name == data[2][c].cur_char_code)
                            {
                                oldcur = cur[key]['cost'];
                            }
                        }
                tr +=        '<tr><td ><span><b>'+data[2][c].cat_name+
                            '<span><b></td>'+
                            '<td class="summ ' + (sum1*oldcur/nowcur>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(sum1*oldcur/nowcur)+'</span></td>'
                            + '<td class="summ ' + (sum2*oldcur/nowcur>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(sum2*oldcur/nowcur)+'</span></td>'
                            + '<td class="summ ' + (delta*oldcur/nowcur>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(delta*oldcur/nowcur)+'</span></td>'
                            + '</tr>';
                ssum1 = Math.round(parseFloat(ssum1)) + Math.round(parseFloat(sum1));
                ssum2 = Math.round(parseFloat(ssum2)) + Math.round(parseFloat(sum2));
                sdelta += Math.round(delta);
                }
            }
            }
            tr +=        '<tr><td ><span><b>Итого:<span><b></td>'+
                            '<td class="summ ' + (ssum1>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(ssum1)+'</span></td>'
                            + '<td class="summ ' + (ssum2>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(ssum2)+'</span></td>'
                            + '<td class="summ ' + (sdelta>=0 ? 'sumGreen' : 'sumRed')+'"><span>'+formatCurrency(sdelta)+'</span></td>'
                            + '</tr>';
            $('tr:not(:first)','#reports_list').each(function(){
                        $(this).remove();
                    });
            $('#reports_list').html(tr);
            //$('.operation_list').jScrollPane();
        },'json');
    }

    $('#Period21,#Period22').hide();
    //$('.operation_list').jScrollPane();
    $('#dateFrom,#dateTo,#dateFrom2,#dateTo2').datepicker({dateFormat: 'dd.mm.yy'});
    $('#report').change();
    $('#btnShow').click(function(){
        switch ( $('#report :selected').val() ) {
            case "Доходы":
                ShowIncome();
            break;
            case "Расходы":
                ShowIncome();
            break;
            case "Сравнение расходов и доходов":
                ShowCompareIncome();
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
    });

//Показ и скрытие дополнительных полей при выборе типа отчёта
    $('#report').change( function(){
    switch ( $('#report :selected').attr('id') ) {
        case "graph_profit": //Доходы":
            $('#chart').show();
            $('#Period21,#Period22').hide();
            $('.operation_list').hide();
            break;
        case "graph_loss": //"Расходы":
            $('#chart').show();
            $('#Period21,#Period22').hide();
            $('.operation_list').hide();
            break;
        case "graph_profit_loss": //"Сравнение расходов и доходов":
            $('#chart').show();
            $('#Period21,#Period22').hide();
            $('.operation_list').hide();
            break;
        case "txt_profit"://"Детальные доходы":
            $('#chart').hide();
            $('#Period21,#Period22').hide();
            $('.operation_list').show();
            break;
        case "txt_loss"://"Детальные расходы":
            $('#chart').hide();
            $('#Period21,#Period22').hide();
            $('.operation_list').show();
            break;
        case "txt_loss_difference"://"Сравнение расходов за периоды":
            $('#chart').hide();
            $('#Period21,#Period22').show();
            $('.operation_list').show();
            break;
        case "txt_profit_difference"://"Сравнение доходов за периоды":
            $('#chart').hide();
            $('#Period21,#Period22').show();
            $('.operation_list').show();
            break;
        case "txt_profit_avg_difference": //"Сравнение доходов со средним за периоды":
            $('#chart').hide();
            $('#Period21,#Period22').show();
            $('.operation_list').show();
            break;
        case "txt_loss_avg_difference"://"Сравнение расходов со средним за периоды":
            $('#chart').hide();
            $('#Period21,#Period22').show();
            $('.operation_list').show();
            break;
    }

    });

});