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

        function generateParentCategories(root) {
            var str = '';
            for (var i = 0; i < root.length; i++) {
                str += '<tr class="b-reportstable-row-category">';
                str += '<th>' + root[i].label + '</th>';
                str += generateValues(root[i].flatIndex, data.headerTop, data.matrix)
                str += '</tr>';

                str += generateSubCategories(root[i]);
            }

            return str;
        }

        function generateSubCategories(root) {
            var str = '';
            for (var i = 0; i < root.children.length; i++) {
                str += '<tr class="b-reportstable-row-subcategory">';
                str += '<th>' + root.children[i].label + '</th>';
                str += generateValues(root.children[i].flatIndex, data.headerTop, data.matrix);
                str += '</tr>';
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
            body: generateParentCategories(data.headerLeft)
        }
    }

    var API = {
        graph_loss: "/report/getData/?responseMode=json",
        graph_profit: "/report/getData/?responseMode=json",
        graph_profit_loss: "/report/getData/?responseMode=json",
        matrix: "/my/reports/matrix",
        txt_loss: "/report/getData/?responseMode=json",
        txt_loss_difference: "/report/getData/?responseMode=json",
        txt_profit: "/report/getData/?responseMode=json",
        txt_profit_difference: "/report/getData/?responseMode=json",
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
