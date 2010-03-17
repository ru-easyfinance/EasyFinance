easyFinance.widgets.report = function(){
    function init(){
        var reportType = '';
		
		$('#dateFrom,#dateTo,#dateFrom2,#dateTo2').datepicker({dateFormat: 'dd.mm.yy'});

        //Показ и скрытие дополнительных полей при выборе типа отчёта
        $('#report').change(function(){
            switch ($('#report :selected').val()) {
                case "graph_profit": //Доходы":
                case "graph_loss": //"Расходы":
                    $('#Period21,#Period22').hide();
                    $('#itogo').show();
                    break;
                case "graph_profit_loss": //"Сравнение расходов и доходов":
                case "txt_profit"://"Детальные доходы":
                case "txt_loss"://"Детальные расходы":
                    $('#Period21,#Period22').hide();
                    $('#itogo').hide();
                    break;
                case "txt_loss_difference"://"Сравнение расходов за периоды":
                case "txt_profit_difference"://"Сравнение доходов за периоды":
                case "txt_profit_avg_difference": //"Сравнение доходов со средним за периоды":
                case "txt_loss_avg_difference"://"Сравнение расходов со средним за периоды":
                    $('#Period21,#Period22').show();
                    $('#itogo').hide();
                    break;
            }
            
        });
        $('#btnShow').click(function(){
            load($('#report').val());
        });
        
    }
    function load(reportType){
        var accountsList = [];
        for (var key in res.accounts) {
            if (typeof(res.accounts[key]) == 'object') {
                accountsList.push(key);
            }
        }
        var currencyId = $('#currency :selected').val();
        var requestData = {
            report: reportType,
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            account: $('#account :selected').val(),
            currency: currencyId,
            acclist: accountsList.toString()
        }
        switch (reportType) {
            case "txt_loss_difference"://"Сравнение расходов за периоды":
            case "txt_profit_difference"://"Сравнение доходов за периоды":
            case "txt_profit_avg_difference": //"Сравнение доходов со средним за периоды":
            case "txt_loss_avg_difference"://"Сравнение расходов со средним за периоды":
                requestData.dateFrom2 = $('#dateFrom').val();
                requestData.dateTo2 = $('#dateTo').val();
                break;
        }
        easyFinance.models.report.load(requestData, function(data){
            switch (reportType) {
                case "graph_profit":
                case "graph_loss":
                    ShowGraph(data, currencyId);
                    break;
                case "graph_profit_loss":
                    ShowCompareGraph(data, currencyId);
                    break;
                case "txt_profit":
                    ShowDetailedIncome(data, currencyId);
                    break;
                case "txt_loss":
                    ShowDetailedWaste(data, currencyId);
                    break;
                case "txt_loss_difference":
                    ShowCompareWaste(data, currencyId);
                    break;
                case "txt_profit_difference":
                    ShowCompareWaste(data, currencyId);
                    break;
                    
                case "txt_profit_avg_difference":
                    ShowAverageIncome(data, currencyId);
                    break;
                case "txt_loss_avg_difference":
                    ShowAverageIncome(data, currencyId);
                    break;
            }
        });
    }
    
    function ShowGraph(data, currencyId){
        $('#chart').empty();
        $('#chart').show();
        $('#Period21,#Period22,.operation_list').hide();
        var key, money;
        //        var cur = easyFinance.models.currency.getCurrencyList();
        var multiplier = 0;//курс в знаменателе. в чём отображаем
        var totalAmount = 0;
        var other = 0;
        /*
         * @deprecated присылать с сервера хотябы id валюты
         */
        multiplier = easyFinance.models.currency.getCurrencyCostById(currencyId);
        for (var mainKey in data[0]) {
            multiplier = easyFinance.models.currency.getCurrencyCostById(data[0][mainKey].cur_id) / multiplier;
            totalAmount += data[0][mainKey]['money'] * multiplier;
            data[0][mainKey].money *= multiplier;
        }
        $('#chart').html("<div id='chart1div'>FusionCharts</div>");
        var chart = "<chart numberPrefix='" + (easyFinance.models.currency.getCurrencyTextById(currencyId)) + " '>";
        for (key in data[0]) {
            money = new Number(data[0][key]['money']);
            if ((money / totalAmount) > 0.02) {
                chart += "<set label='" + data[0][key]['cat'] + "' value='" + money.toFixed(2) + "' />";
            }
            else {
                other += money;
            }
        }
        if (other) {
            chart += "<set label='Прочее' value='" + (other.toFixed(2)) + "' />";
        }
        chart += "</chart>";
        var chartObj = new FusionCharts("/swf/fusioncharts/Pie3D.swf", "chart1Id", "500", "400", "0", "1");
        chartObj.addParam("WMode", "Transparent");
        chartObj.setDataXML(chart);
        chartObj.render("chart1div");
        
        var total = '<table id="itogostroka" width="200px" align="right">' +
        '<tr><td width="50px">Итого : </td><td width="150px">' +
        formatCurrency(totalAmount) +
        ' ' +
        (easyFinance.models.currency.getCurrencyTextById(currencyId)) +
        '</td></tr></table><br>';
        $('#itogo').html(total);
        if (other !== 0) {
            $('#commentRest').show();
            $('#commentRest').html('<h5> * Категория прочее включает в себя Ваши категории, операции по которым за выбранный период не превысили 2% от общего объёма операций за этот период. Отчёты по этим категориям вы можете посмотреть в детальных отчётах</h5>');
        }
        
    }
    
    function ShowCompareGraph(data, currencyId){
        $('#chart').empty();
        var key, tempValue;
        
        var multiplier = 0;//курс в знаменателе. в чём отображаем
        multiplier = easyFinance.models.currency.getCurrencyCostById(currencyId);
        for (var mainKey in data[0]) {
            multiplier = easyFinance.models.currency.getCurrencyCostById(data[0][mainKey].cur_id) / multiplier;
            data[0][mainKey].money *= multiplier;
        }
        
        $('#chart').html("<div id='chart1div'>FusionCharts</div>");
        
        var categories = '<categories>';
        var datasetProfit = "<dataset seriesName='Доходы'>";
        var datasetIncome = "<dataset seriesName='Расходы'>";
        
        var conditionalValue = 0;//половина возвращаемых данных. индекс.
        var noshow = 'showValues="1"';
        for (key in data[0]) {
            if (data[0][key]['was'] != 0 || data[0][key]['in'] != 0) {
                conditionalValue++;
                categories += "<category label='" + data[0][key]['lab'] + "' />";
                
                tempValue = new Number(data[0][key]['in']);
                datasetProfit += "<set value='" + (tempValue.toFixed(2)) + "' />";
                tempValue = new Number(data[0][key]['was']);
                datasetIncome += "<set value='" + (tempValue.toFixed(2)) + "' />";
            }
        }
        
        if (conditionalValue > 4) {
            noshow = 'showValues="0"';
        }
        
        var stri = "<chart " + noshow + " numberPrefix='" + (easyFinance.models.currency.getCurrencyTextById(currencyId)) + " '>";
        stri += categories + "</categories>";
        stri += datasetProfit + "</dataset>";
        stri += datasetIncome + "</dataset>";
        stri += "</chart>";
        var chart1 = new FusionCharts("/swf/fusioncharts/MSColumn3D.swf", "chart1Id", "500", "400", "0", "1");
        chart1.addParam("WMode", "Transparent");
        chart1.setDataXML(stri);
        chart1.render("chart1div");
        $('#chart').show();
        $('#Period21,#Period22').hide();
        $('.operation_list').hide();
    }
    
    function ShowDetailedIncome(data, currencyId){
        var multiplier = easyFinance.models.currency.getCurrencyCostById(currencyId);
        for (var mainKey in data[0]) {
            multiplier = easyFinance.models.currency.getCurrencyCostById(data[0][mainKey].cur_id) / multiplier;
            data[0][mainKey].money *= multiplier;
        }
        var th = '<tr><th>&nbsp;</th><th><span>Дата</span></th><th><span>Счёт</span></th><th><span>Сумма</span></th><th>&nbsp;</th></tr>';
        var tr = '';
        for (var key in data[0]) {
            if (key > 0) {
                if (data[0][key].cat_name != data[0][key - 1].cat_name && data[0][key].account_name != null) {
                    tr += '<tr><td><span><b>' + data[0][key].cat_name +
                    '</span></b></td><td></td><td></td><td></td></tr>';
                }
            }
            else {
                tr += '<tr><td><span><b>' + data[0][0].cat_name +
                '</span></b></td><td></td><td></td><td></td></tr>';
            }
            if (data[0][key].account_name != null) {
                tr += "<tr>" +
                '<td>&nbsp;</td>' +
                '<td><span>' +
                data[0][key].date +
                '</span></td>' +
                '<td class="light"><span>' +
                data[0][key].account_name +
                '</span></td>' +
                '<td class="' +
                (data[0][key].money >= 0 ? 'sumGreen' : 'sumRed') +
                '"><span>' +
                formatCurrency(data[0][key].money) +
                '</span></td>' +
                '</tr>';
            }
        }
        
        $('tr:not(:first)', '#reports_list').remove();
        
        $('#reports_list_header').html(th);
        $('#reports_list').html(tr);
        
        $('#chart').hide();
        $('#Period21,#Period22').hide();
        $('.operation_list').show();
    }
    
    function ShowDetailedWaste(data, currencyId){
        var multiplier = easyFinance.models.currency.getCurrencyCostById(currencyId);
        for (var mainKey in data[0]) {
            multiplier = easyFinance.models.currency.getCurrencyCostById(data[0][mainKey].cur_id) / multiplier;
            data[0][mainKey].money *= multiplier;
        }
        
        var th = '<tr><th>&nbsp;</th><th><span>Дата</span></th><th><span>Счёт</span></th><th><span>Сумма</span></th><th>&nbsp;</th></tr>';
        var tr = '';
        
        for (var key in data[0]) {
            if (key > 0) {
                if (data[0][key].cat_name != data[0][key - 1].cat_name && data[0][key].account_name != null) {
                    tr += '<tr><td><span><b>' + data[0][key].cat_name +
                    '<span><b></td><td></td><td></td><td></td></tr>';
                }
            }
            else {
                tr += '<tr><td><span><b>' + data[0][0].cat_name +
                '<span><b></td><td></td><td></td><td></td></tr>';
            }
            
            if (data[0][key].cat_name != null && data[0][key].account_name != null) {
                tr += "<tr>" +
                '<td>&nbsp;</td>' +
                '<td class="repdate"><span>' +
                data[0][key].date +
                '</span></td>' +
                '<td class="repname"><span>' +
                data[0][key].account_name +
                '</span></td>' +
                '<td class="' +
                (data[0][key].money >= 0 ? 'sumGreen' : 'sumRed') +
                '"><span>' +
                formatCurrency(data[0][key].money) +
                '</span></td>' +
                '</tr>';
            }
        }
        $('tr:not(:first)', '#reports_list').remove();
        
        $('#reports_list_header').html(th);
        $('#reports_list').html(tr);
        
        $('#chart').hide();
        $('#Period21,#Period22').hide();
        $('.operation_list').show();
    }
    
    
    /*
     * @TODO with server
     */
    function ShowCompareWaste(data, currencyId){
        cur = res['currency'];
        nowcur = 0;//курс в знаменателе. в чём отображаем
        oldcur = 0;//курс в числителе. курс валюты счёта
        for (key in cur) {
            cost = cur[key]['cost'];
            name = cur[key]['name'];
            if (name == data[1][0].cur_char_code) {
                nowcur = cur[key]['cost'];
            }
        }
        
        var th = '<tr><th>&nbsp;</th><th><span>Период 1</span></th><th><span>Период 2</span></th><th><span>Разница</span></th><th></th></tr>';
        
        var tr = '';
        
        var sum1 = 0;
        var sum2 = 0;
        var delta = 0;
        var ssum1 = 0;
        var ssum2 = 0;
        var sdelta = 0;
        for (c in data[0]) {
            if (data[0][c].su != null) {
                if (data[0][c].per == 1) {
                    sum1 = data[0][c].su;
                    sum2 = 0;
                    for (v in data[0]) {
                        if (data[0][v].cat_name == data[0][c].cat_name) 
                            if (data[0][v].per == 2) {
                                sum2 = data[0][v].su;
                                data[0][v].su = null;
                            }
                    }
                }
                else {
                    sum2 = data[0][c].su;
                    sum1 = 0;
                    for (v in data[0]) {
                        if (data[0][v].cat_name == data[0][c].cat_name) 
                            if (data[0][v].per == 1) {
                                sum1 = data[0][v].su;
                                data[0][v].su = null;
                            }
                    }
                }
                delta = sum2 - sum1;
                if (data[0][c].cat_name != null && data[0][c].cur_char_code != null) {
                    cur = res['currency'];
                    for (key in cur) {
                        cost = cur[key]['cost'];
                        name = cur[key]['name'];
                        if (name == data[0][c].cur_char_code) {
                            oldcur = cur[key]['cost'];
                        }
                    }
                    tr += '<tr><td ><span><b>' + data[0][c].cat_name +
                    '<span><b></td>' +
                    '<td class="' +
                    (sum1 * oldcur / nowcur >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(sum1 * oldcur / nowcur) +
                    '</span></td>' +
                    '<td class="' +
                    (sum2 * oldcur / nowcur >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(sum2 * oldcur / nowcur) +
                    '</span></td>' +
                    '<td class="' +
                    (delta * oldcur / nowcur >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(delta * oldcur / nowcur) +
                    '</span></td>' +
                    '</tr>';
                    ssum1 = parseFloat(ssum1) + parseFloat(sum1 * oldcur / nowcur);
                    ssum2 = parseFloat(ssum2) + parseFloat(sum2 * oldcur / nowcur);
                    sdelta += Math.round(delta);
                }
            }
        }
        tr += '<tr><td ><span><b>Итого:</span><b></td>' +
        '<td class="' +
        (ssum1 >= 0 ? 'sumGreen' : 'sumRed') +
        '"><span>' +
        formatCurrency(ssum1) +
        '</span></td>' +
        '<td class="' +
        (ssum2 >= 0 ? 'sumGreen' : 'sumRed') +
        '"><span>' +
        formatCurrency(ssum2) +
        '</span></td>' +
        '<td class="' +
        (sdelta >= 0 ? 'sumGreen' : 'sumRed') +
        '"><span>' +
        formatCurrency(sdelta) +
        '</span></td>' +
        '</tr>';
        $('tr:not(:first)', '#reports_list').each(function(){
            $(this).remove();
        });
        
        $('#reports_list_header').html(th);
        $('#reports_list').html(tr);
        
        $('#chart').hide();
        $('#Period21,#Period22').show();
        $('.operation_list').show();
        
    }
    
    
    
    function ShowAverageIncome(data, currencyId){
        cur = res['currency'];
        nowcur = 0;//курс в знаменателе. в чём отображаем
        oldcur = 0;//курс в числителе. курс валюты счёта
        for (key in cur) {
            cost = cur[key]['cost'];
            name = cur[key]['name'];
            if (name == data[3][0].cur_char_code) {
                nowcur = cur[key]['cost'];
            }
        }
        
        var th = '<tr><th>&nbsp;</th><th><span>Период 1</span></th><th><span>Период 2</span></th><th><span>Разница</span></th></th>';
        
        var tr = '';
        
        var sum1 = 0;
        var sum2 = 0;
        var delta = 0;
        var ssum1 = 0;
        var ssum2 = 0;
        var sdelta = 0;
        for (c in data[2]) {
            if (data[2][c].su != null) {
                if (data[2][c].per == 1) {
                    sum1 = data[2][c].su;
                    sum2 = 0;
                    for (v in data[2]) {
                        if (data[2][v].cat_name == data[2][c].cat_name) 
                            if (data[2][v].per == 2) {
                                sum2 = data[2][v].su * data[0] / data[1];
                                data[2][v].su = null;
                            }
                    }
                }
                else {
                    sum2 = data[2][c].su * data[0] / data[1];
                    sum1 = 0;
                    for (v in data[2]) {
                        if (data[2][v].cat_name == data[2][c].cat_name) 
                            if (data[2][v].per == 1) {
                                sum1 = data[2][v].su * data[0] / data[1];
                                data[2][v].su = null;
                            }
                    }
                };
                delta = sum2 - sum1;
                if (data[2][c].cat_name != null && data[2][c].cur_char_code != null) {// проверка на валюту чтобы отсеять мусор, те операции что не удалены рпи создании счёта.
                    cur = res['currency'];
                    for (key in cur) {
                        cost = cur[key]['cost'];
                        name = cur[key]['name'];
                        if (name == data[2][c].cur_char_code) {
                            oldcur = cur[key]['cost'];
                        }
                    }
                    tr += '<tr><td ><span><b>' + data[2][c].cat_name +
                    '<span><b></td>' +
                    '<td class="' +
                    (sum1 * oldcur / nowcur >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(sum1 * oldcur / nowcur) +
                    '</span></td>' +
                    '<td class="' +
                    (sum2 * oldcur / nowcur >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(sum2 * oldcur / nowcur) +
                    '</span></td>' +
                    '<td class="' +
                    (delta * oldcur / nowcur >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(delta * oldcur / nowcur) +
                    '</span></td>' +
                    '</tr>';
                    ssum1 = Math.round(parseFloat(ssum1)) + Math.round(parseFloat(sum1));
                    ssum2 = Math.round(parseFloat(ssum2)) + Math.round(parseFloat(sum2));
                    sdelta += Math.round(delta);
                }
            }
        }
        tr += '<tr><td ><span><b>Итого:<span><b></td>' +
        '<td class="' +
        (ssum1 >= 0 ? 'sumGreen' : 'sumRed') +
        '"><span>' +
        formatCurrency(ssum1) +
        '</span></td>' +
        '<td class="' +
        (ssum2 >= 0 ? 'sumGreen' : 'sumRed') +
        '"><span>' +
        formatCurrency(ssum2) +
        '</span></td>' +
        '<td class="' +
        (sdelta >= 0 ? 'sumGreen' : 'sumRed') +
        '"><span>' +
        formatCurrency(sdelta) +
        '</span></td>' +
        '</tr>';
        $('tr:not(:first)', '#reports_list').each(function(){
            $(this).remove();
        });
        
        $('#reports_list_header').html(th);
        $('#reports_list').html(tr);
        
        $('#chart').hide();
        $('#Period21,#Period22').show();
        $('.operation_list').show();
    }
    
    return {
        init: init,
        load: load
    }
}();

$(document).ready(function(){
    easyFinance.widgets.report.init();
});
