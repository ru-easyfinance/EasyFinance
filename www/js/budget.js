$(document).ready(function() {
    /**
     * @desc объект отвечающий за работу бюджета
     */

var budget =
    {
        jQuery : $('.waste_list form'),
        /**@deprecated
         * @desc устанавливает список
         * @param data {}
         * @return void
         */
        setup_list : function (data){
            this.data = data;
        },
        /**
         * @desc добавляет бюджет
         * @param month int 1 - 12
         * @param year int ~ 2222
         * @return void
         */
        add : function (month, year){
            $.post('/budget/add',
                    {
                        month : month,
                        year : year,
                        data : $('#budget_edit_form').srialize()
                    },
                    function(data)
                    {
                        this.data = data;
                        this.print_list();
                    },
                    'json'
                ); //add
        },
        /**
         * @desc удаляет бюджет
         * @param id int
         * @return void
         */
        del : function(id){
            $.post('/budget/del',
                {
                    id:id
                },
                function(data)
                {
                    this.data = data;
                    this.print_list();
                },
                'json'
            );//del
        },
        /**
         * @desc редактирует бюджет
         * @param id int
         * @return void
         */
        edit : function (id){
            $.post('/budget/add',
                    {
                        id : id,
                        data : $('#budget_edit_form').srialize()
                    },
                    function(data)
                    {
                        this.data = data;
                        this.print_list();
                    },
                    'json'
                ); //edit
        },
        /**
         * @desc печатает список бюджетов
         * @return void
         */
        print_list : function(){
            var bud_list = this.data.list;
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
                str+='</table></div>';
            }
            $(this.jQuery).append(str);
        },
        /**
         * @desc пичатает инфо о бюджете
         * @return void
         */
        print_info : function (){
            $('#total_budget').val(this.data.main.total);
            var str = '<div class="income">Итого доходов: <span><b>'+this.data.main.income_all+'</b> '+this.data.main.cur+'</span></div>';
            str += '<div class="waste">Итого расходов: <span><b>'+this.data.main.expense_all+'</b> '+this.data.main.cur+'</span></div>';
            str += '<div class="rest">Остаток: <span><b>'+this.data.main.balance+'</b> '+this.data.main.cur+'</span></div>';
            $('.f_field3').html(str);
        }
    }
////заглушка
budget.setup_list(
{
    list : {
        1 : {
                name : 'name',
                total : 667,
                children :[
                    {
                    id : 1,    
                    name : 'c_name',
                    total : 667,
                    cur : 'rur',
                    limit_red : 45,
                    limit_green : 35,
                    limit_strip : 24,
                    mean_expenses : 123.44,//вроде средний расход
                    type : 0
                    }
                ]
            }
        },
    main :  {
        total:6776,
        cur : 'rur',
        expense_all : 999,
        income_all : 676,
        balance : 333
    }
});
budget.print_list();
budget.print_info();
///////////////////////////////////
    $('input#year').keyup(function(){
        var str = $('input#year').val();
        $('input#year').val(str.match(/[0-9]{0,4}/))
    });
    $('.waste_list form tr').live('mouseover',function(){
        $(this).addClass('act');
    }).live('mouseout',function(){
        $(this).removeClass('act');
    })

})
