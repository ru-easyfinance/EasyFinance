$(document).ready(function() {
    /**
     * @desc объект отвечающий за работу бюджета
     */
    var budget =
    {
        jQuery : $('.waste_list'),
        template : [],//@todo
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
                        data : $('budget_edit_form').srialize()
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
                        data : $('budget_edit_form').srialize()
                    },
                    function(data)
                    {
                        this.data = data;
                        this.print_list();
                    },
                    'json'
                ); //edit
        },
        print_list : function(){
            //@todo
        }
    }
////заглушка
budget.setup_list(
{
    list : {
        1 : {
                name : 'name',
                total : 666,
                children :[
                    {name : 'c_name',
                    total : 666,
                    cur : 'rur',
                    tags : ['tag1','tag2'],
                    limit_red : 45,
                    limit_green : 55,
                    limit_line : 24,
                    mean_expenses : 123.44,//вроде средний расход
                    type : 0
                    }
                ]
            }
        },
    main :  {
        total:6666,
        cur : 'rur',
        expense_all : 999,
        income_all : 666,
        balance : 333
    }
})
///////////////////////////////////
//$('input#year').mask('9999');

})
