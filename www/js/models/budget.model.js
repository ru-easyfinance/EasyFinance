/**
 * @desc Модель бюджета
 * @author rewle
 */
easyFinance.models.budget = function(){
        /**
         * @desc устанавливает список
         * @param data {}
         * @return void
         */
        var _data;
        function load (data) {
            var real = {p: 0, d: 0},
                plan = {p: 0, d: 0};

            for (var key in data.list.p) {
                plan.p += Math.abs(parseFloat(data.list.p[key].amount))
                real.p += Math.abs(parseFloat(data.list.p[key].money))
            }
            for (key in data.list.d) {
                plan.d += Math.abs(parseFloat(data.list.d[key].amount))
                real.d += Math.abs(parseFloat(data.list.d[key].money))
            }

            _data = {
                list : data.list,
                main: {
                    plan_profit : plan.p,
                    real_profit : real.p,
                    plan_drain : plan.d,
                    real_drain : real.d
                }
            }
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
                    if(typeof callback == "function") {
                        callback(_data.main.real_drain,_data.main.real_profit)
                    }
                },
                'json'
            )
        }

        /**
         * @desc возвращает список бюджетов
         * @return {}
         */
        function returnList(){
            return _data.list;
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
                        if (typeof callback == "function") {
                            callback(date);
                        }
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

                        if (type == 'p'){
                            _data.main.plan_profit = _data.main.plan_profit - _data.list[type][id]['amount']
                            _data.main.real_profit = _data.main.real_profit - _data.list[type][id]['money']
                            delete _data.list[type][id];
                        }
                        else {
                            _data.main.plan_drain = _data.main.plan_drain - _data.list[type][id]['amount']
                            _data.main.real_drain = _data.main.real_drain - _data.list[type][id]['money']
                            delete _data.list[type][id]['amount'];
                        }

                        if (typeof callback == "function"){
                            callback();
                        }
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

                        if (type =='p') {
                            _data.main.plan_profit = _data.main.plan_profit - _data.list[type][id]['amount'] + parseFloat(value)
                            _data.list[type][id]['amount'] = value;
                        }
                        else{
                            _data.main.plan_drain = _data.main.plan_drain - _data.list[type][id]['amount'] + parseFloat(value)
                            _data.list[type][id]['amount'] = value;
                        }

                        if (typeof callback == "function"){
                            callback();
                        }
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
        /**
         * @desc возвращает общее сформированное инфо о бюджете
         * @return {}
         */
        function returnInfo(){
            return _data.main;
        }

        /**
         * @desc возвращает объект для редактирования и тп
         * @return {} {}
         */
        function get_data(p_id, c_id){
            var tmp = _data.list[p_id]['children'];
            
            for (var key in tmp) {
                if (tmp[key]['id'] == c_id) {
                    return tmp[key];
                }
            }
            return {};
        }

        return {
            reload : reload,
            load : load,
            save : save,
            del : del,
            edit : edit,
            get_data : get_data,
            returnList : returnList,
            returnInfo : returnInfo
        }
}();