
/**
 * @desc Модель бюджета
 * @author rewle
 */
easyFinance.models.budget = function()
    {
        /**
         * @desc {json} все данные по бюджету сформированные по формату:
  list : {
    _key : {                             // {int} Ид родительской категории
        name : {String},                 // Имя!!!!!!!!!!
        total_drain : {Int},             // Итого по расходам
        total_profit : {Int},            // Итого по доходам
        children :[{array}
            {
                id : {Int},              // ид ребёнка (может дублироваться)
                name : {String},         // имя!!!!!!!!!1
                amount : {Int},          // сумма
                cur : {String},          // валюта!!!!!!!!
//                limit_red : {Int},       // Превышение среднего за 3 мес (0 - 100%)
//                limit_green : {Int},     // мес. расх.
                mean_drain : {Float}, //средний расход
                type : {0|1}//расходная - 0,доходный-1
            }
        ]
    }
    }, main :  {
        drain_all :  {Int},
        profit_all:  {Int},
        start:       {dd.mm.yyyy},
        end:         {dd.mm.yyyy}
    }
});
         */
        //var _data = $.extend(bdgt);
        /**
         * @desc устанавливает список
         * @param data {}
         * @return void
         */
        var _data;
        function load (data) {
            _data = data;
        }

        /**
         * @desc возвращает сформированный список бюджетов
         * @param {0|1}type //расходная - 0,доходный-1
         * @return {String} html for $().append(html)
         */
         function print_list(type){
            type = parseInt(type);
            if (isNaN(type)){return '';}
            var bud_list = _data.list;
            var children,str = '';
            
            for (var key in bud_list) {
                str += '<div class="line open" id="'+key+'">';
                str += '<a href="#" class="name">'+bud_list[key]['name']+'</a>';
                str += '<div class="amount">'+bud_list[key]['total'+(type?'_drain':'_profit')]+'</div>';
                children = bud_list[key]['children'];
                str += '<table>';
                for (var k in children) {
                    if (((type*2-1)==res.category[children[k]['c_id']]['type'])||(children[k]['type']==type)){
                        var rgb = children[k]['mean_drain']*100/children[k]['amount'];
                        str += '<tr id="'+children[k]['c_id']+'"><td class="w1"><a href="#">';
                        str += children[k]['name']+'</a></td><td class="w2"><div class="cont">';
                        str += '<input type="text" value="'+children[k]['amount']+'" readonly="readonly" /></div></td>';
                        str += '<td class="w3"><div class="indicator">';
                        str += '<div class="green" style="width: '+((rgb-1)<0)?'100':(rgb-2*(rgb-1))+'%;"></div>';
                        str += '<div class="red" style="width: 100%;"></div>';
                        //str += '<div class="strip" style="width: '+children[k]['limit_strip']+'%;"></div>';
                        str += '</div></td>';
                        str += '<td class="w4"><span>'+children[k]['mean_drain']+' '+children[k]['cur']+'</span></td>';
                        str += '</tr>';
                    }
                }
                str+='</table></div>';
            }
            return str;
        }
        
        /**
         * @desc добавляет бюджет
         * @param month {int} 1 - 12
         * @param year {int} ~ ****
         * @param jQuery - jQuery selector on form
         * @return {String} html for $().append(html)
         */
        function add (month, year, jQuery){
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
        }
        /**
         * @desc удаляет бюджет
         * @param id {int}
         * @return {String} html for $().append(html)
         */
        function del (id){
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
        }
        /**
         * @desc редактирует бюджет
         * @param id {int}
         * @param jQuery - jQuery selector on form
         * @return {String} html for $().append(html)
         */
        function edit (id, jQuery){
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
        }
        /**
         * @desc возвращает общее сформированное инфо о бюджете
         * @return {} {total:html,group:html} for $().append(html)
         */
        function print_info(){
            var ret = {total:'',group:''};
            //ret.total=_data.main.total;
/**
 * @FIXME Откуда тут берётся валюта? Её негде указывать, разве что её нужно брать из валюты по умолчанию
 *
 *            var str = '<div class="income">Итого доходов: <span><b>'+_data.main.profit_all+'</b> '+_data.main.cur+'</span></div>';
 *               str += '<div class="waste">Итого расходов: <span><b>'+_data.main.drain_all+'</b> '+_data.main.cur+'</span></div>';
 *               str += '<div class="rest">Остаток: <span><b>'+(_data.main.profit_all-_data.main.drain_all)+'</b> '+_data.main.cur+'</span></div>';
 */
            var str = '<div class="income">Итого доходов: <span><b>'+_data.main.profit_all+'</b></span></div>';
                str += '<div class="waste">Итого расходов: <span><b>'+_data.main.drain_all+'</b></span></div>';
                str += '<div class="rest">Остаток: <span><b>'+(_data.main.profit_all-_data.main.drain_all)+'</b></span></div>';
            ret.group=str;
            return ret;
        }
        /**
         * @desc возвращает объект для редактирования и тп
         * @return {} {}
         */
        function get_data(p_id,c_id){
            var tmp = _data.list[p_id]['children'];
            for (var key in tmp)
            {
                if( tmp[key]['id'] == c_id ){
                    return tmp[key];
                }
            }
            return {};
        }
        
        return {
            load:load,
            print_info:print_info,
            print_list:print_list,
            add:add,
            del:del,
            edit:edit,
            get_data:get_data
        }
    }