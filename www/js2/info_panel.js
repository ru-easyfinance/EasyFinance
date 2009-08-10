$(window).load(function () {
    $('.edit_panel').hide();
    $('.details_page').hide();
    $('#dialog').hide();
    $('#dialog').hide();
});

$('document').ready(function(){
    /**
     *апдейт второй панельки
     */
function panel2_print(){
            $.post(
                '/infopanel/update/',
                {type : o_count,
                panel : 2},
                function(data){
                    arr=$.parseJSON(data);
                    arr = data;
                    //len = arr.length;
                    //for (i = 0; i< len;len++)
                    //{
                    //    s = "<div class='element' id='"+i+"'>\n\
                    //            <div class='edit_panel' style='background-color:#FF0000'>\n\
                    //            <div class='delete_o'>del</div>\n\
                    //            </div>\n\
                    //            <div class='conteiner'> "+arr[i]+"</div>\n\
                    //        </div>";
                    //    $('.panel#2').find('.content').append(s);
                    //}
                    //$('.panel#2').find('.content').append("</div><input type='button' class='element' id='button' value='Добавить цель'/></div>");
                },
                'text'
            );
    }
    /**
     *апдейт второй панельки
     */
    function panel2_update(){
        //$('.panel#2').find('.element').hide();
        $('.panel#2').find('div.element').empty();
        panel2_print();
        for ( i = 0;i<o_count;i++)
        {
            $('.panel#2').find('.element#'+i).show();
        }
    }
    /**
     *рисует хронометр
     */
    function print_chart(i){
        $.post(
            '/infopanel/xml/',
            {element:i,
            date: c_list[5]},
            function (data){
                    var chartSample_1 = new AnyChart('/swf/anychart/Gauge.swf');
                    chartSample_1.width = '150px';
                    chartSample_1.height = '150px';
                    chartSample_1.setData(data);
                    chartSample_1.wMode="opaque";
                    chartSample_1.write('condition_' + i);
                    chartSample_1 = null;},
            'text');
        
    }
    /**
     *апдейт второй панельки
     */
    function panel1_update(){
        
        for ( i = 0;i<5;i++)
        {
            if (c_list[i])
            {
                $('.panel#1').find('.element#condition_'+i).show();
                print_chart(i);
            }
            else
            {
                $('.panel#1').find('.element#condition_'+i).hide();
            }
        }
    }
        function print_stat(i){
        $.post(
            '/infopanel/update/',
            {panel: 3,
            type: i,
            date: i_date},
            function (data){
                data=$.parseJSON(data)
                b0 = (data[0]>0)?'#00ff00':(data[0]<0?'ff0000':'0000ff');
                b1 = (data[1]>0)?'#00ff00':(data[1]<0?'ff0000':'0000ff');
                str = "<table><tr><td><b>"+ $('.panel#3').find('.element#'+i).attr('name')+"</b></tr></td><tr><td>итого</tr></td><tr><td class='item' style='background-color:"+b0+";' >"+data[0]+"</tr></td><tr><td>за день</tr></td><tr><td class='item'style='background-color:"+b1+";' > "+data[1]+"</tr></td></table>";
                $('.panel#3').find('.element#'+i).html(str);//@todo
                    },

            'text');

    }
    /**
     *апдейт второй панельки
     */
    function panel3_update(){
        var cnt = i_list.length;
        for ( i = 0;i<cnt;i++)
        {
            if (i_list[i])
            {
                $('.panel#3').find('.element#'+i).show();
                print_stat(i);
            }
            else
            {
                $('.panel#3').find('.element#'+i).toggle();
            }
        }
    }
//---------------------------------main prop----------------------------------//



    var c_list;//////////////////////////показывает видимость блоков
        c_list = [1,1,1,1,1,0];
    var o_count;//////////////////////////количество отображаемых фин целей
        o_count = 3;
    var i_list;//////////////////////////блоки инвестиций
        i_list = [1,1,1,1,1];
    var i_date = 0;
panel3_update();
panel2_update();
//-----------------------------hronometr--------------------------------------//
panel1_update();
//-----------------------------main buttons-----------------------------------//
    $('.close').click(//кнопка закрыть на панели
        function() {
            $(this).closest('.panel').hide();
        }
    )
    $('.min').click(//кнопка свернуть развернуть на панели
        function() {
            $(this).closest('.panel').find('.content').toggle();
        }
    );
     $('.edit').click(//кнопка настрйки развернуть на панели
	function(){
	    var id = $(this).closest('.panel').attr('id');
        $(".edit_panel").hide();
        $("#panel"+id+"_edit").toggle();
	}
    );
    $('.back').click(//возврат на инфо-панель
        function(){
            $('.panel').parent().show();
            $('.details_page').hide();
        }
    );
//-----------------------------edit panel-------------------------------------//
    $("#panel1_edit").find('input:button').click(//edit panel1
	function () {
            k = $(this).parent();
            for ( i = 0;i<5;i++)
            {
                if ($(k).find('#'+i).attr('checked'))
                {
                    c_list[i]=1;
                }
                else
                {
                    c_list[i]=0;
                }
            }
	    c_list[5] = $('#datepicker_info').val();
            $(this).parent().toggle();
            panel1_update();
        }
    );

    $("#panel2_edit").find('input:button').click(//edit panel2
	function () {
	    o_count = $(this).parent().find('input:text').val();
        $(this).parent().toggle();
        panel2_update();
	}
    );

   $("#panel3_edit").find('input:button').click(//edit panel3
	function () {
            k = $(this).parent();
            for ( i = 0;i<3;i++)//@todo count
            {
                if ($(k).find('#'+i).attr('checked'))
                {
                    i_list[i]=1;
                }
                else
                {
                    i_list[i]=0;
                }
            }
            $(this).parent().toggle();
            panel3_update();
        }
    );

//----------------------------------movie-------------------------------------//
    $(".panel").parent().sortable({
        connectWith: '.panel',
        axis :'y'
    });
    $(".panel").disableSelection();
//---------------------------------content------------------------------------//
    $('.panel').find('.element').click(
        function()
        {
            $('.edit_panel').hide();
            if ($(this).closest('.panel').attr('id') == '2')
            {
                if ($(this).attr('id') == 'button')
                {
                	document.location = '/targets/' ;
                }
                else
                {
                    $(this).find('.edit_panel').show();
                }
            }
            else
            {
                $('.panel').parent().hide();
                $('.details_page').show();
                $.post('/infopanel/page/',
                		{name : $(this).attr('id'),
                		date : c_list[5]},//@todo
                		
                	function(data){$('.details_page').find('.content').html(data)});
            }

        }
    );
//-----------------------------panel2_cont_butt-------------------------------//
$('.delete_o').click(
    function(){
        var $id=$(this).closest('.element');
        $("#dialog").dialog('open');
	$("#dialog").dialog({
			bgiframe: true,
			resizable: false,
			height:140,
			modal: true,
			overlay: {
				backgroundColor: '#000',
				opacity: 0.5
			},
			buttons: {
				'Delete': function() {
                                        $.post('/target/del/',
                                        {id:$id.attr('id')},
                                        function(){$(this).dialog('close');
                                        
                                        $id.empty();
                                        panel2_update();},
                                        'json');}
					
				,
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});
    }
);
$('.rewright_o').click(
    function(){
    	//@query servises//dialog
});
})