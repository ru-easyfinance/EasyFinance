var emptyArticle = {
    mean: 0,
    plan: 0,
    adhoc: 0,
    calendarAccepted: 0,
    calendarFuture: 0
};

function BudgetArticle(catInfo, children, budget_articles) {
    this.id = catInfo.id;
    this.name = catInfo.name,
    this.isProfit = catInfo.type == '1',
    this.isEditable = function() {
        return !this.children.length && !this.isTopLevel()
    }
    this.isTopLevel = function() {
        return this.id == 'd' || this.id == 'p'
    }

    var myBudgetData = budget_articles[this.id] || emptyArticle;

    this.children = [];

    var fields = 'plan mean adhoc calendarAccepted calendarFuture'.split(' ');
    for (var fieldIndex = 0; fieldIndex < fields.length; fieldIndex++) {
        this[fields[fieldIndex]] = myBudgetData[fields[fieldIndex]]
    }

    this.getFact = function() {
        return this.adhoc + this.calendarAccepted;
    }

    this.getPlan = function() {
        return this.plan;
    }

    this.getCompletePercent = function() {
        if (this.getFact() == 0) {
            return 0;
        }
        if (this.getPlan() == 0) {
            return 100;
        }
        else {
            return Math.abs( Math.round(this.getFact() * 100 / this.getPlan()) );
        }
    }

    this.getTotalCalendar = function() {
        return this.calendarAccepted + this.calendarFuture
    }

    this.isEmpty = function() {
        return !this.getPlan() && !this.getFact() && !this.calendarFuture
    }

    var childArticle;
    for(var childIndex = 0, childrenCount = children.length; childIndex < childrenCount; childIndex++) {
        childArticle = new BudgetArticle(children[childIndex], children[childIndex].children, budget_articles);
        if (childArticle) {
            this.children.push(childArticle);

            for (fieldIndex = 0; fieldIndex < fields.length; fieldIndex++) {
                this[fields[fieldIndex]] += childArticle[fields[fieldIndex]]
            }
        }
    }

    this.getRecomendation = function(viewDate) {
        var currentElapsedRatio = utils.getMonthPartRatio(viewDate);
        var now = new Date();

        var recomendation = {};

        //оставшийся бюджет
        recomendation.budgetLeft = this.getPlan() - this.getFact();

        //сколько останется, если будем тратить с неизменной скоростью и календарем
        recomendation.marginTotal = recomendation.budgetLeft - this.calendarFuture - currentElapsedRatio * this.adhoc;

        //насколько урезать спонтанные траты, чтобы выйти в 0
        recomendation.changeAdhoc = recomendation.marginTotal / (utils.getDaysCount(viewDate) - now.getDate())
        recomendation.canChangeAdhoc = recomendation.changeAdhoc < this.adhoc / now.getDate();

        //насколько урезать календарь, чтобы выйти в 0
        recomendation.changeCalendar = recomendation.marginTotal;
        recomendation.canChangeCalendar = recomendation.changeCalendar < this.calendarFuture;

        recomendation.canChangeBoth = !(recomendation.canChangeAdhoc && recomendation.canChangeCalendar);

        recomendation.budgetOverheaded = recomendation.budgetLeft < 0;
        recomendation.marginZero = recomendation.marginTotal == 0;
        recomendation.marginPositive = recomendation.marginTotal > 0;

        if (this.isProfit) {
            recomendation.color = (recomendation.budgetOverheaded || recomendation.marginTotal < 0) ? 'green' : recomendation.marginTotal == 0 ? 'yellow' : 'red';
        }
        else {
            recomendation.color = (recomendation.budgetOverheaded || recomendation.marginTotal < 0) ? 'red' : recomendation.marginTotal == 0 ? 'yellow' : 'green';
        }

        return recomendation
    }


    return this;
}

/**
 * @desc Модель бюджета
 * @author rewle
 */
easyFinance.models.budget = function(){
        var _data;
        var categoriesModel = easyFinance.models.category;
        var articlesTree;

        function load (data) {
            var currentBudgetArticle;
            
            for (var categoryId in data.list) {
                currentBudgetArticle = data.list[categoryId];

                //приводим все значения к float, т.к. изначально в JSON-е они криво отформатированы
                var fields = 'plan mean adhoc calendarAccepted calendarFuture'.split(' ');
                for (var fieldIndex = 0, fieldsCount = fields.length; fieldIndex < fieldsCount; fieldIndex++) {
                    currentBudgetArticle[fields[fieldIndex]] =  Math.abs( parseFloat(currentBudgetArticle[fields[fieldIndex]]));
                }
            }

            articlesTree = getBudgetArticlesTree(data);

        }

        function getTotal() {
            var total = {
                real_profit: 0,
                real_drain: 0,
                plan_profit: 0,
                plan_drain: 0
            };

            total.realProfit = articlesTree[0].getFact();
            total.realDrain = articlesTree[1].getFact();
            total.planProfit = articlesTree[0].getPlan();
            total.planDrain = articlesTree[1].getPlan();

            return total;
        }

        function getBudgetArticlesTree(budgetData) {
            var rootIndex;

            var tree = categoriesModel.getUserCategoriesTreeOrdered();

            var topChildren = [[], []];

            for (var i = 0, l = tree.length; i < l; i++) {
                rootIndex = tree[i].type == "-1" ? 1 : 0; // вычисляем, в какую из корневых категорий отправить категорию
                topChildren[rootIndex].push(tree[i]);
            }

            budgetData['p'] = emptyArticle;
            budgetData['d'] = emptyArticle;


            var resultTree = [
                new BudgetArticle({name: "Доходы", id: "p", type: "1"}, topChildren[0], budgetData),
                new BudgetArticle({name: "Расходы", id: "d", type: "-1"}, topChildren[1], budgetData)
            ];

            return resultTree;
        }

        function reload (date, callback) {
            var month = date.getMonth() + 1;
            if (month.toString().length == 1) {
                month = '0' + month.toString()
            }
            
            $.post(
                '/my/budget/load/',
                {
                    start: date.getFullYear() + '-' + month + '-01'
                },
                function(data) {
                    load(data);
                    callback && callback()
                },
                'json'
            )
        }

        function getArticlesTree() {
            return articlesTree;
        }

        /**
         * @desc добавляет бюджет
         * @param budget {str} JSON
         * @param date {date}
         * @param callback {function}
         * @return void
         */
        function save (budget, date, callback){
            var month = date.getMonth() + 1;
            if (month.toString().length == 1){
                month = '0' + month.toString()
            }

            $.post('/my/budget/add/',
                {
                    data: budget,
                    start: date.getFullYear() + '-' + month + '-01'
                },
                function(data) {
                    if (!data['error'] || data.error == []) {
                        $.jGrowl("Бюджет сохранён", {theme: 'green'});
                        callback && callback(date)
                    }
                    else{
                        var err = '<ul>';
                        for(var key in data.error) {
                            err += '<li>' + data.error[key] + '</li>';
                        }
                        $.jGrowl(err + '</ul>', {theme: 'red'});
                    }
                },
                'json'
            )
        }

        /**
         * @desc удаляет бюджет
         *
         * @return {String} html for $().append(html)
         */
        function del (date, id, type, callback){
            var month = date.getMonth() + 1;
            if (month.toString().length == 1){
                month = '0' + month.toString()
            }
            $.post('/my/budget/del/',
                {
                    date_start: date.getFullYear() + '-' + month + '-01',
                    category_id: id,
                    type: (type == 'p' ? 0 : 1)
                },
                function(data) {
                    if (!data['error'] || data.error == []) {
                        $.jGrowl("Бюджет удалён", {theme: 'green'});

                        callback && callback()
                    }
                    else {
                        var err = '<ul>';
                        for(var key in data.error) {
                            err += '<li>' + data.error[key] + '</li>';
                        }
                        $.jGrowl(err+'</ul>', {theme: 'red'});
                    }
                },
                'json'
            );//del
        }

        /**
         * @desc редактирует бюджет
         * @param date {date}
         * @param type budget type {'p'||'d'}
         * @param id category id{int}
         * @param value {float} amount
         * @param callback {function}
         * @return {String} html for $().append(html)
         */
        function edit (date, type, id, value, callback){
            var month = date.getMonth() + 1;
            if (month.toString().length == 1){
                month = '0' + month.toString()
            }
            value = value.toString().replace(/[^0-9\.]/gi,'');

            $.post('/my/budget/edit/',
                {
                    type : (type == 'p' ? 0 : 1),
                    category_id: id,
                    value: value,
                    start: date.getFullYear() + '-' + month + '-01'
                },
                function(data) {
                    if (!data['error'] || data.error == []) {
                        $.jGrowl("Бюджет изменён", {theme: 'green'});

                        callback && callback()
                    }
                    else {
                        var err = '<ul>';
                        for(var key in data.error) {
                            err += '<li>' + data.error[key] + '</li>';
                        }
                        $.jGrowl(err + '</ul>', {theme: 'red'});
                    }
                }
                ,'json'
            )
        }

        return {
            reload : reload,
            load : load,
            save : save,
            del : del,
            edit : edit,
            getArticlesTree : getArticlesTree,
            getTotal: getTotal
        }
}();