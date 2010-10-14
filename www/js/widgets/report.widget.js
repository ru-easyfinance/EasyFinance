easyFinance.widgets.report = function(){
    function init(){
        var reportType = '';

        $('#dateFrom,#dateTo,#dateFrom2,#dateTo2').datepicker({dateFormat: 'dd.mm.yy'});

        //Показ и скрытие дополнительных полей при выборе типа отчёта
        $('#report').change(function(){
            switch ($('#report :selected').val()) {
                case "graph_profit": //Доходы":
                case "graph_loss": //"Расходы":
                    $('.js-compare-fields').addClass('hidden');
                    $('#itogo').removeClass('hidden');
                    break;
                case "graph_profit_loss": //"Сравнение расходов и доходов":
                case "txt_profit"://"Детальные доходы":
                case "txt_loss"://"Детальные расходы":
                    $('.js-compare-fields').addClass('hidden');
                    $('#itogo').addClass('hidden');
                    break;
                case "txt_loss_difference"://"Сравнение расходов за периоды":
                case "txt_profit_difference"://"Сравнение доходов за периоды":
                case "txt_profit_avg_difference": //"Сравнение доходов со средним за периоды":
                case "txt_loss_avg_difference"://"Сравнение расходов со средним за периоды":
                    $('.js-compare-fields').removeClass('hidden');
                    $('#itogo').addClass('hidden')
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

        if (reportType == "txt_loss_difference" || reportType == "txt_profit_difference"
            || reportType == "txt_loss_avg_difference" || reportType == "txt_profit_avg_difference") {
                requestData.dateFrom2 = $('#dateFrom2').val();
                requestData.dateTo2 = $('#dateTo2').val();
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
                    ShowDetailed(data, 'profit');
                    break;
                case "txt_loss":
                    ShowDetailed(data, 'drain');
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

    function showReports(type) {
        var reports = {
            all: '.js-reports-graphs, .js-reports-table',
            graphs: '.js-reports-graphs',
            tables: '.js-reports-table'
        }

        $('#commentRest').addClass('hidden');
        $(reports.all).addClass('hidden');
        $(reports[type]).removeClass('hidden');
    }

    function showReportTables(type) {
        var reportTables = {
            all: '.js-report-bodies',
            detail: '.js-detailreport',
            compare: '.js-comparereport'
        }
        $(reportTables.all).addClass('hidden');
        $(reportTables[type]).removeClass('hidden');
    }

    function ShowGraph(data, currencyId){
        $('#chart').empty();

        var key, money;
        var totalAmount = 0;
        var other = 0;

        for (var mainKey in data[0]) {
            totalAmount += data[0][mainKey]['money'];
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
        var chartObj = new FusionCharts("/swf/fusioncharts/Pie3D.swf", "chart1Id", "650", "400", "0", "1");
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
        showReports('graphs');
        if (other !== 0) {
            $('#commentRest').removeClass('hidden');
            $('#commentRest').html('<h5> * Категория прочее включает в себя Ваши категории, операции по которым за выбранный период не превысили 2% от общего объёма операций за этот период. Отчёты по этим категориям вы можете посмотреть в детальных отчётах</h5>');
        }
    }

    function ShowCompareGraph(data, currencyId){
        $('#chart').empty();

        var key, tempValue;

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
        var chart1 = new FusionCharts("/swf/fusioncharts/MSColumn3D.swf", "chart1Id", "650", "400", "0", "1");
        chart1.addParam("WMode", "Transparent");
        chart1.setDataXML(stri);
        chart1.render("chart1div");

        showReports('graphs');
    }

    function CreateDetailedRow(parentCategoryName, categoryName, date, accountName, type, money)
    {
    	var row = 
        	'<tr><th>' +
        	parentCategoryName +
            '</th><td>' + 
            categoryName + 
            '</td><td>' +
            date + 
            '</td><td>' +
            accountName + 
            '</td><td class="' +
            (type == 'profit' ? 'sumGreen' : 'sumRed') +
            '">' +
            formatCurrency(money) + 
            '</td></tr>';
    	
    	return row;
    }
    
    function ShowDetailed(data, type){

        var tableContent = '';
        var totalSum = 0;
        
        //TODO: если эта хрень окажется нужной в новых отчетах и доживет до рефакторинга,
        // нужно грамотному JS'еру переделать ее на нормальный рекурсивный обход дерева, чтобы убрать явную обработку 
        // стоп-случаев смены категорий и родительских
        // например, сделать так: 
        // ПоРодительскимКатегориям {ПоКатегориям {ПоОперациям; ИтогПоКатегории}; ИтогПоРодительскойКатегории}
    	
        var categorySum = 0;
        var categoryContent = '';
        var categoryName;
        var categoryId = null;
    	
        var parentCategorySum = 0;
        var parentCategoryContent = '';
        var parentCategoryName;
        var parentCategoryId = null;
        
        var tableData = data[0];
        
        if (tableData.length > 0)
        {
        	for (var key in data[0]) {
        	
	        	var currentCategoryId = tableData[key].category_id;
	        	var currentParentCategoryId = tableData[key].parent_category_id;
	        	var currentCategoryName = tableData[key].cat_name;
	        	var currentParentCategoryName = tableData[key].parent_cat_name;
	        	var currentDate = tableData[key].date;
	        	var currentAccountName = tableData[key].account_name;
	        	var currentMoney = tableData[key].money;       	
	        	
	
	        	//Если сменилась дочерняя категория, занесем ее в родительскую
	        	if (currentCategoryId != categoryId) {
	        		
	        		//проверяем, что какая-то предыдущая категория есть, т.е. мы не в начале массива
	        		if(categoryId != null) {
	        			parentCategoryContent += CreateDetailedRow('', categoryName, '', '', type, categorySum) +
		                    categoryContent;
	        		}
	        		categoryName = currentCategoryName;
	        		categoryId = currentCategoryId;
	
	        		categoryContent = '';
	        		categorySum = 0;
	            } 	
	        	
	        	//Если сменилась родительская категория, занесем ее в общий результат
	        	if (currentParentCategoryId != parentCategoryId) {
	        		
	        		//проверяем, что есть предыдущая родительская категория, и тогда выводим все данные по ней
	        		if(parentCategoryId != null) {
	        			tableContent += CreateDetailedRow(parentCategoryName, '', '', '', type, parentCategorySum) +
	        			parentCategoryContent;
	        		}
	        		parentCategoryId = currentParentCategoryId;
	        		parentCategoryName = currentParentCategoryName;
	        		
	        		parentCategoryContent = '';
	        		parentCategorySum = 0;
	        		
	        		//сбросим categoryId при нахождении новой родительской
	        		//categoryId = null;
	            }
	        	
	        	categorySum += currentMoney;
        		parentCategorySum += currentMoney;
        		totalSum += currentMoney;
	           
	        	categoryContent += CreateDetailedRow('', '', currentDate, currentAccountName, type, currentMoney);
	        }

			//сформируем строку итоговой суммы для повторного использования
			var totalRow = CreateDetailedRow('Всего', '', '', '', type, totalSum);
        	
			//выведем итоговую сумму и хвостовые категории, которые не вывели в самом теле for 
			tableContent = 
				totalRow +
				tableContent +
				CreateDetailedRow(parentCategoryName, '', '', '', type, parentCategorySum) +
				parentCategoryContent +
				CreateDetailedRow('', categoryName, '', '', type, categorySum) +
                categoryContent +				
				totalRow;			
		}

        $('table.js-reports-body tbody.js-comparereport').html('');
        $('table.js-reports-body tbody.js-detailreport').html(tableContent);

        showReports('tables');
        showReportTables('detail');
    }

    function ShowCompareWaste(data, currencyId){
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
                if (data[0][c].cat_name != null) {
                    tr += '<tr><th>' + data[0][c].cat_name +
                    '</th>' +
                    '<td class="' +
                    (sum1 >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(sum1) +
                    '</span></td>' +
                    '<td class="' +
                    (sum2 >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(sum2) +
                    '</span></td>' +
                    '<td class="' +
                    (delta >= 0 ? 'sumGreen' : 'sumRed') +
                    '"><span>' +
                    formatCurrency(delta) +
                    '</span></td>' +
                    '</tr>';
                    ssum1 = parseFloat(ssum1) + parseFloat(sum1);
                    ssum2 = parseFloat(ssum2) + parseFloat(sum2);
                    sdelta += Math.round(delta);
                }
            }
        }
        tr += '<tr><th>Итого:</th>' +
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

        $('table.js-reports-body tbody.js-comparereport').html(tr);
        $('table.js-reports-body tbody.js-detailreport').html('');

        showReports('tables');
        showReportTables('compare');
    }

    function ShowAverageIncome(data, currencyId){ /* кажется, не используется */
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
                } else {
                    sum2 = data[2][c].su * data[0] / data[1];
                    sum1 = 0;
                    for (v in data[2]) {
                        if (data[2][v].cat_name == data[2][c].cat_name)
                            if (data[2][v].per == 1) {
                                sum1 = data[2][v].su * data[0] / data[1];
                                data[2][v].su = null;
                            }
                    }
                }

                delta = sum2 - sum1;
            }
        }
        tr += '<tr><th>Итого:</th>' +
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

        $('table.js-reports-body tbody.js-comparereport').html(tr);
        $('table.js-reports-body tbody.js-detailreport').html('');

        showReports('tables');
        showReportTables('compare');
    }

    return {
        init: init,
        load: load
    }
}();

$(document).ready(function(){
    easyFinance.widgets.report.init();
});
