
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
        //var _data = $.extend(bdgt);
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
                callback(_data.main.real_drain,_data.main.real_profit)//@todo main ->budget_info
                
            },
            'json')
        }
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
         * @return void
         */
        function save (budget,date){
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
                        
                        $('#budget li.cur').click();
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
         * @param id {int}
         * @param jQuery - jQuery selector on form
         * @return {String} html for $().append(html)
         */
        function edit (id, jQuery){
            $.post('/budget/add',
                    {
                        id : id,
                        data : $(jQuery).serialize()
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