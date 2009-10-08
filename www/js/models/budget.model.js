
/**
 * @desc Модель бюджета
 * @author rewle
 * @todo init
 */
easyFinance.models.budget = function()
    {
        /**
         * @desc {json} все данные по бюджету сформированные по формату:
            {
                list : {
                    _key{int} : {
                            name : {String},
                            total : {Int},
                            children :[{array}
                                {
                                id : {Int},
                                name : {String},
                                total : {Int},
                                cur : {String},
                                limit_red : {Int},
                                limit_green : {Int},
                                limit_strip : {Int},
                                mean_expenses : {Float},//вроде средний расход
                                type : {0|1}//расходная - 0,доходный-1
                                }
                            ]
                        }
                    },
                main :  {
                    total:{Int},
                    cur : {String},
                    expense_all : {Int},
                    income_all : {Int},
                    balance : {Int}
                }
            });
         */
        var _data;
        /**@deprecated
         * @desc устанавливает список
         * @param data {}
         * @return void
         */
        var setup_list = function (data){
            _data = data;
        };
        /**
         * @desc возвращает сформированный список бюджетов
         * @param {0|1}type //расходная - 0,доходный-1
         * @return {String} html for $().append(html)
         */
        var print_list = function(type){
            type = parseInt(type);
            if (isNaN(type)){return '';}
            var bud_list = _data.list;
            var children,str = '';
            for (var key in bud_list)
            {
                str += '<div class="line open">';
                str += '<a href="#" class="name">'+bud_list[key]['name']+'</a>';
                str += '<div class="amount">'+bud_list[key]['total']+'</div>';
                children = bud_list[key]['children'];
                str += '<table>';
                for (var k in children)
                {
                    if (type==children[k]['type']){
                        str += '<tr id="'+children[k]['id']+'"><td class="w1"><a href="#">';
                        str += children[k]['name']+'</a></td><td class="w2"><div class="cont">';
                        str += '<input type="text" value="'+children[k]['total']+'" /></div></td>';
                        str += '<td class="w3"><div class="indicator">';
                        str += '<div class="green" style="width: '+children[k]['limit_green']+'%;"></div>';
                        str += '<div class="red" style="width: '+children[k]['limit_red']+'%;"></div>';
                        str += '<div class="strip" style="width: '+children[k]['limit_strip']+'%;"></div>';
                        str += '</div></td>';
                        str += '<td class="w4"><span>'+children[k]['mean_expenses']+children[k]['cur']+'</span></td>';
                        str += '</tr>';
                    }
                }
                str+='</table></div>';
            }
            return str;
        };
        /**
         * @desc добавляет бюджет
         * @param month {int} 1 - 12
         * @param year {int} ~ ****
         * @param jQuery - jQuery selector on form
         * @return {String} html for $().append(html)
         */
        var add = function (month, year, jQuery){
            $.post('/budget/add',
                    {
                        month : month,
                        year : year,
                        data : $(jQuery).srialize()
                    },
                    function(data)
                    {
                        _data = data;
                        return print_list();
                    },
                    'json'
                ); //add
        };
        /**
         * @desc удаляет бюджет
         * @param id {int}
         * @return {String} html for $().append(html)
         */
        var del = function(id){
            $.post('/budget/del',
                {
                    id:id
                },
                function(data)
                {
                    _data = data;
                    return print_list();
                },
                'json'
            );//del
        };
        /**
         * @desc редактирует бюджет
         * @param id {int}
         * @param jQuery - jQuery selector on form
         * @return {String} html for $().append(html)
         */
        var edit = function (id, jQuery){
            $.post('/budget/add',
                    {
                        id : id,
                        data : $(jQuery).srialize()
                    },
                    function(data)
                    {
                        _data = data;
                        return print_list();
                    },
                    'json'
                ); //edit
        };
        /**
         * @desc возвращает общее сформированное инфо о бюджете
         * @return {} {total:html,group:html} for $().append(html)
         */
        var print_info = function (){
            var ret = {total:'',group:''};
            ret.total=_data.main.total;
            var str = '<div class="income">Итого доходов: <span><b>'+_data.main.income_all+'</b> '+_data.main.cur+'</span></div>';
                str += '<div class="waste">Итого расходов: <span><b>'+_data.main.expense_all+'</b> '+_data.main.cur+'</span></div>';
                str += '<div class="rest">Остаток: <span><b>'+_data.main.balance+'</b> '+_data.main.cur+'</span></div>';
            ret.group=str;
        };

        return {setup_list:setup_list, print_info:print_info, print_list:print_list, add:add, del:del, edit:edit}
    }