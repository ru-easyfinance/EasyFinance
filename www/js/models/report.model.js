easyFinance.models.report = function() {

    function generateMatrixTree(data) {
        var root = data.leftTree;

        function generateValues(index, columns, values) {
            var child,
                arr =[];
            for (var i = 0; i < columns.length; i++) {
                child = columns[i];

                arr.push( formatCurrency(values[index][child.flatIndex]) )
            }

            return '<td>' + arr.join('</td><td>') + '</td>'
        }

        function generateCategories(root, isParent) {
            var str = '';
            var rowClassName = isParent ? 'b-reportstable-row-category' : 'b-reportstable-row-subcategory';
            var iteratible = isParent ? root : root.children

            for (var i = 0; i < iteratible.length; i++) {
                str += '<tr class="' + rowClassName  + '">';
                str += '<th>' + iteratible[i].label + '</th>';
                str += generateValues(iteratible[i].flatIndex, data.headerTop, data.matrix)
                str += '</tr>';

                if (isParent) {
                    str += generateCategories(iteratible[i]);
                }
            }

            return str;
        }

        function generateHeader(tags) {
            var str = '<tr>';
            str += '<th style="text-align: left;">Категория</th>';
            for (var i = 0; i < tags.length; i++) {
                str += '<th>' + tags[i].label + '</th>'
            }
            return str;
        }

        return {
            head: generateHeader(data.headerTop),
            body: generateCategories(data.headerLeft, true)
        }
    }

    var API = {
        graph_loss:             "/report/getData/?responseMode=json",
        txt_loss:               "/report/getData/?responseMode=json",
        txt_loss_difference:    "/report/getData/?responseMode=json",
        matrix_loss:            "/my/reports/matrix?type=0",
        graph_profit:           "/report/getData/?responseMode=json",
        txt_profit:             "/report/getData/?responseMode=json",
        txt_profit_difference:  "/report/getData/?responseMode=json",
        matrix_profit:          "/my/reports/matrix?type=1",
        graph_profit_loss:      "/report/getData/?responseMode=json"
    }

    function load(requestData, callback) {
        $.get(
            API[requestData.report],
            requestData,
            function(data) {
                if (typeof(callback) == 'function'){
                    callback(data);
                }
            },
            'json'
        );
    }

    return {
        load: load,
        generateMatrixTree: generateMatrixTree
    }
}();
