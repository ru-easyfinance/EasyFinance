easyFinance.models.report = function() {

var testMatrix = {
    leftTree: {
        label: "Всего",
        index: "root",
        children: [
            {
                label: "Содержание дома",
                index: "parentCategory[1]",
                children: [
                    {
                        label: "Садовник",
                        index: "parentCategory[1]childCategory[2]",
                        children: [ ]
                    },
                    {
                        label: "Котельная",
                        index: "parentCategory[1]childCategory[3]",
                        children: [ ]
                    }

                ]
            }
        ]
    },

    topTree: {
        label: "Всего",
        index: "root",
        children: [
            {
                label: "Тэг1",
                index: "tag[Тэг1]",
                children: []
            }
        ]
    },

    values: {
        "parentCategory[1]tag[Тэг1]" : 400,
        "parentCategory[1]childCategory[2]tag[Тэг1]" : 250,
        "parentCategory[1]childCategory[3]tag[Тэг1]" : 150
        }
    }


var t1000 = {
    "headerLeft":[
        {"label":"Parent Cat 1","flatIndex":"1","children":[{"label":"Child Cat 1","flatIndex":"18","children":[]}]},
        {"label":"Another Cat","flatIndex":"19","children":[]}
    ],
    "headerTop":[
        {"label":"tag_foo","flatIndex":"tag_foo","children":[]},
        {"label":"tag_bar","flatIndex":"tag_bar","children":[]}
    ],
    "matrix": {"1":{"tag_foo":"-100.00","tag_bar":"-400.00"},"18":{"tag_foo":"-200.00","tag_bar":"-800.00"},"19":{"tag_foo":"-50.00"}}
}

    function MatrixItem(itemData, tagsList) {
        this.name = easyFinance.models.category.getgetUserCategoryNameById(itemData.value)

        function getRowElementByValue(value) {
            for (var rowIndex = 0; rowIndex < itemData.row.lengtn; rowIndex++) {
                if (itemData.row[rowIndex].value == value) {
                    return itemData.row[rowIndex]
                }
            }
            return false
        }

        this.cells = '';
        var rowElement;
        for (var i = 0; i < tagsList.lengtn; i++) {
            rowElement = getRowElementByValue(tagsList[i])
            if (rowElement) {
                this.cells += '<td>' + rowElement.amount + '</td>';
            }
            else {
                this.cells += '<td></td>'
            }
        }

        function render() {
            return utils.templator(this.tplRow, {
                className: 'category_' + itemData.level,
                name: this.name,
                cells: this.cells
            })

        }

        return this;
    }

    function generateMatrixTree(data) {
        data = data || t1000;

        var root = data.leftTree;

        function generateValues(index, columns, values) {
            var child,
                arr =[];
            for (var i = 0; i < columns.length; i++) {
                child = columns[i];

                arr.push( values[index][child.flatIndex] )

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
            str += '<th>Категория</th>';
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

    function load(requestData, callback) {
        var api_url = '/report/getData/?responseMode=json';

        if (requestData.report == 'matrix') {
            api_url = '/my/reports/matrix';
        }
        $.get(
            api_url,
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