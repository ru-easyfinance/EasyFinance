
function _getMonthDays(d){
    var m = d.getMonth()
    for (var i = 29;i<32;i++)
    {
        d.setDate(i)
        if (m != d.getMonth()){return (i-1)}
    }
    return (i)
}
/**
 * @desc Модель бюджета
 * @author rewle
 */
easyFinance.models.budget = function()
    {
        

        /**
         * @desc устанавливает список
         * @param data {}
         * @return void
         */
        var _data;
        function load (data) {
            var key, real={p:0,d:0}, plan ={p:0,d:0};
            for(key in data.list.p){
                plan.p += data.list.p[key].amount
                real.p += data.list.p[key].money
            }
            for(key in data.list.d){
                plan.d += data.list.d[key].amount
                real.d += data.list.d[key].money
            }
            _data = {list : data.list,
                    main: {
                        plan_profit : plan.p,
                        real_profit : real.p,
                        plan_drain : plan.d,
                        real_drain : real.d
                    }
                    }
        }

        function reload (date,callback) {
            var month = date.getMonth()+1;
            if (month.toString().length == 1){
                month = '0'+month.toString()
            }
            $.post('/budget/load/',
            {
                start: '01.'+month+'.'+date.getFullYear()
            },
            function(data)
            {
                load(data);
                if(typeof callback == "function"){callback(_data.main.real_drain,_data.main.real_profit)}//@todo main ->budget_info

            },
            'json')
        }
        /**
         * @desc возвращает список бюджетов
         * @return {}
         */
        function returnList(){
            return _data.list;
        }

        /**
         * @deprecated ASGFAGNALKJNBubbgsbnLBAlvILilbvUVBBlbvIVBLNB
         */
         function print_list(type,nomaster){
            type = parseInt(type);
            if (isNaN(type)){return '';}
            var bud_list = _data.list;
            var children,str = '';
            
            for (var key in bud_list) {
                if ((type=='0' && (res.category.user[bud_list[key]['category']]['type']<=0))||(type=='1' && (res.category.user[bud_list[key]['category']]['type']>=0))){

                str += '<div class="line open" id="'+key+'">';
                str += '<a style="text-decoration:underline;cursor:pointer" class="name">'+bud_list[key]['name']+'</a>';
                str += '<div class="amount">'+formatCurrency(bud_list[key]['total'+((!type)?'_drain':'_profit')])+'</div>';
                children = bud_list[key]['children'];
                str += '<table>';
                for (var k in children) {
                    if (children[k]['type'] == type ||(children[k]['type']=='-1'&&((type=='0' && (res.category.user[children[k]['category']]['type']<=0))||(type=='1' && (res.category.user[children[k]['category']]['type']>=0))))){
                    //if(children[k]['type'] == type){
                    //var rgb = parseInt(childreRGGREASGRn[k]['amount']*100/children[k]['mean_drain']);
                    //  //if (isNaN(rgb))RGARGEA                       //    rgb = '0';,SAKFHWSGFA
                        
                        if (nomaster == '1')//hgfWEFGASWKEFHGAWEVGAEUKRGHSEBJSIJLBHR
                        {
                            var drainprc = Math.abs(Math.round(children[k]['money']*100/children[k]['amount']))
                            var date = new Date()
                            var dateprc = Math.round(date.getDate()*100/_getMonthDays(date))
                            var b_color =(dateprc < drainprc)?'red':'green';
                            if (type == '1'){
                                b_color =(dateprc > drainprc)?'red':'green';
                            }
                            str += '<tr id="'+children[k]['category']+'"><td class="w1"><a style="text-decoration:underline;cursor:pointer">';
                            str += children[k]['name']+'</a></td><td class="w2"><div class="cont">';
                            str += '<input type="text" value="'+formatCurrency(children[k]['amount'])+'" readonly="readonly" /></div></td>';
                            str += '<td class="w3"><div class="indicator">';
                            str += '<div class="'+b_color+'" style="width: '+drainprc+'%;"></div>';
                            str += '<div class="strip" style="width: '+dateprc+'%;"></div>'
                            str += '</div></td>';
                            var f = Math.abs(parseFloat(children[k]['amount']))-Math.abs(parseFloat(children[k]['money']));
                            str += '<td class="w4">'+formatCurrency(f)+'</td>';
                            str += '</tr>';
                        }
                        else//KYYGFBVFDVBHVSVBSKVB
                        {
                            var drainprc = Math.abs(Math.round(children[k]['money']*100/children[k]['amount']))
                            var date = new Date()
                            var dateprc = Math.round(date.getDate()*100/_getMonthDays(date))
                            var b_color =(dateprc < drainprc)?'red':'green';
                            str += '<tr id="'+children[k]['category']+'"><td class="w1"><a style="text-decoration:underline;cursor:pointer">';
                            str += children[k]['name']+'</a></td><td class="w2"><div class="cont">';
                            str += '<input type="text" value="'+formatCurrency(children[k]['amount'])+'" readonly="readonly" /></div></td>';
                            str += '<td class="w3"><div class="indicator">';
                            str += '<div class="'+b_color+'" style="width: '+drainprc+'%;"></div>';
                            str += '<div class="strip" style="width: '+dateprc+'%;"></div>'
                            str += '</div></td>';
                            str += '<td class="w4">'+formatCurrency(children[k]['mean_drain'])+'</td>';
                            str += '</tr>';
                        }
                    }
                }
                
                str+='</table></div>';
                }
            }
            return str;
        }
        
        /**
         * @desc добавляет бюджет
         * @param budget {str} JSON
         * @param date {date}
         * @param callback {function}
         * @return void
         */
        function save (budget,date,callback){
            var month = date.getMonth()+1;
            if (month.toString().length == 1){
                month = '0'+month.toString()
            }
            
            $.post('/budget/add/',
                {
                    data: budget,
                    start: '01.'+month+'.'+date.getFullYear()
                },
                function(data){
                    if (!data['errors'] || data.errors == []){
                        $.jGrowl("Бюджет сохранён", {theme: 'green'});
                        if(typeof callback == "function"){callback(date);}
                    }else{
                        var err = '<ul>';
                        for(var key in data.errors)
                        {
                            err += '<li>' + data.errors[key] + '</li>';
                        }
                        $.jGrowl(err+'</ul>', {theme: 'red'});
                    }
                }
            )
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
         * @param date {date}
         * @param type budget type {'p'||'d'}
         * @param id category id{int}
         * @param value {float} amount
         * @param callback {function}
         * @return {String} html for $().append(html)
         */
        function edit (date, type, id, value, callback){
            var month = date.getMonth()+1;
            if (month.toString().length == 1){
                month = '0'+month.toString()
            }

            $.post('/budget/edit/',
                {
                    type: type,
                    id: id,
                    value: value,
                    start: '01.'+month+'.'+date.getFullYear()
                },
                function(data){
                    if (!data['errors'] || data.errors == []){
                        $.jGrowl("Бюджет изменён", {theme: 'green'});

                        if (type =='p'){
                            _data.main.plan_profit = _data.main.plan_profit - _data.list[type][id]['amount'] + value
                            _data.list[type][id]['amount'] = value;
                        }else{
                            _data.main.plan_drain = _data.main.plan_drain - _data.list[type][id]['amount'] + value
                            _data.list[type][id]['amount'] = value;
                        }
                        if(typeof callback == "function"){callback();}
                    }else{
                        var err = '<ul>';
                        for(var key in data.errors)
                        {
                            err += '<li>' + data.errors[key] + '</li>';
                        }
                        $.jGrowl(err+'</ul>', {theme: 'red'});
                    }
                }
            )
        }
        /**
         * @desc возвращает общее сформированное инфо о бюджете
         * @return {} 
         */
        function returnInfo(){
            return _data.main;//.budget_info;
        }
        /**
         * @deprecated
         */
        function print_info(i){
            var ret = {total:'',group:''};
            var str = '<div class="income">Итого доходов: <span><b>'+formatCurrency(_data.main.profit_all)+'</b> руб.</span></div>';
                str += '<div class="waste">Итого расходов: <span><b>'+formatCurrency(_data.main.drain_all)+'</b> руб.</span></div>';
                str += '<div class="rest">Остаток: <span><b>'+formatCurrency(_data.main.profit_all-_data.main.drain_all)+'</b> руб.</span></div>';
            if (i){
                str = '<div class="income">Итого доходов: <span><b>'+formatCurrency(_data.main.profit_all)+'</b> руб.</span></div>';
                str += '<div class="waste">Итого расходов: <span><b>'+formatCurrency(_data.main.drain_all)+'</b> руб.</span></div>';
                str += '<div class="rest">Остаток: <span><b>'+formatCurrency(_data.main.profit_all-_data.main.drain_all)+'</b> руб.</span></div>';
            }
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
            reload : reload,
            load : load,
            print_info : print_info,
            print_list : print_list,
            save : save,
            del : del,
            edit : edit,
            get_data : get_data,
            returnList : returnList,
            returnInfo : returnInfo
        }
    }