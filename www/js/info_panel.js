$(document).ready(function () {
    //$('.edit_panel').hide();
    //$('.details_page').hide();
    //$('#dialog').hide();
    //$('#dialog').hide();
    /**
     *апдейт второй панельки
     */

function panel2_print(){
            $.post(
                '/infopanel/update/',
                {type : o_count,
                panel : 2},
                function(data){
                    len = data.length;

                    for (i = 0; i<len;i++)
                    {
                        str ='<img src='+data[i]['image']+' alt="" />';
                        str = str+'<a>'+data[i]['title']+'</a>';
                        str = str+'<div class="indicator_block"><div class="money">'+data[i]['amount']+'.<br /><span> '+data[i]['amount_done'] +'</span></div>';
                        str = str+'<div class="indicator"><div style="width:'+data[i]['percent_done']+'%;"><span>'+data[i]['percent_done']+'</span></div></div>';
                        str = str+'<div class="date"><span>Целевая дата:'+ data[i]['date_end']+'</span> &nbsp;&nbsp;&nbsp; Прогнозная дата: 0</div></div>';
                        $('.ramka3#2').find('#'+i+' .descr').html(str);
                    }
                },
                'json'
            );
    }
    /**
     *апдейт второй панельки
     */
    function panel2_update(){
       $.post('/infopanel/targets/',
       {cnt: o_count},
       function(){
       document.location='/infopanel/'},
       'text'
   )};
    /**
     *рисует хронометр
     */
    function print_chart(i){
        arr =['fcon','money','budget','cost','credit'];
        $.post(
            '/infopanel/xml/',
            {element: arr[i],
            date: c_list[5]},
            function (data){
                    var chartSample_1 = new AnyChart('/swf/anychart/Gauge.swf');
                    chartSample_1.width = '109px';
                    chartSample_1.height = '120px';
                    chartSample_1.setData(data);
                    chartSample_1.wMode="opaque";
                    chartSample_1.write('flash_' + i);
                    chartSample_1 = null;},
            'text');

    };
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
            arr =['akc','pif','ofbu','oms','estat'];
        $.post(
            '/infopanel/update/',
            {panel: 3,
            type: arr[i],
            date: i_date},
            function (data){
                b0 = (data['year']>0)?'class="block up"':
                    (data['year']<0?'class="block down"':
                    'class="block null"');
                b1 = (data['day']>0)?'class="block day up"':
                    (data['day']<0?'class="block day down"':
                    'class="block day null"');
                str = '<a href="#">'+data['name']+'</a>';
                str = str + '<span '+b0+'">';
                str = str + '<span class="pct">'+data['year']+'%</span><span class="period">за год</span>';
                str = str + '</span><span '+b1+'">';
                str = str + '<span class="pct">'+data['day']+'%</span><span class="period">за день</span>';
                str = str + '</span></div>';
                $('.ramka3#3').find('#'+i).html(str);
                    },

            'json');

    };
    /**
     *апдейт второй панельки
     */
    function panel3_update(){
        var cnt = i_list.length;
        for ( i = 0;i<cnt;i++)
        {
            if (i_list[i])
            {
                $('.ramka3#3').find('.investments_block div#'+i).show();
                print_stat(i);
            }
            else
            {
                $('.ramka3#3').find('.investments_block div#'+i).toggle();
            }
        }
    };
//---------------------------------main prop----------------------------------//



    var c_list;//////////////////////////показывает видимость блоков
        c_list = [1,1,1,1,1,0];
    var o_count = 0;
//   $.post( работает криво
//        '/infopanel/get/',
//        {},
//        function(data){ o_count = data;},
//        'text'
//);

    var i_list;//////////////////////////блоки инвестиций
        i_list = [1,1,1,1,1];
    var i_date = 0;

panel3_update();
panel2_print();
//-----------------------------hronometr--------------------------------------//
panel1_update();
//-----------------------------main buttons-----------------------------------//
    $('.over2').click(//кнопка закрыть на панели
        function() {
            $(this).closest('.ramka3').hide();
        }
    )
    $('.over3').click(//кнопка свернуть развернуть на панели
        function() {
            $(this).closest('.ramka3').find('#content').toggle();
        }
    );
     $('.over1').click(//кнопка настрйки развернуть на панели
	function(){
	    var id = $(this).closest('.ramka3').attr('id');

        //$(".edit_panel").hide();
        //$("#panel"+id+"_edit").toggle();
	}
    );
    $('.back').click(//возврат на инфо-панель
        function(){
            $('.block2').show();
            $('.details_page').hide();
        }
    );
//-----------------------------edit panel отсутствует-------------------------------------//
/*    $("#panel1_edit").find('input:button').click(//edit panel1
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
*/
//----------------------------------movie-------------------------------------//
    $(".ramka3").css('background-color','#ffffff');
    $(".block2").sortable({
        connectWith: '.ramka3',
        axis :'y'
    });
    $(".ramka3").disableSelection();
//---------------------------------content------------------------------------//
    $('.ramka3').find('#content div').click(
        function()
        {
            //$('.edit_panel').hide();
            if ($(this).attr('class') == 'add2') 
            {
              	document.location = '/targets/' ;
            }
            else if($(this).closest('.ramka3').attr('id') != 2)
            {
                //$('.block2').hide();
                //$('.details_page').show();

                $.post('/infopanel/page/',
                		{name : $(this).attr('name'),
                		date : c_list[5]},//@todo

                	function(data){
                            //$('.details_page').find('.content').html(data) нету
                            //alert(data+' <br/> js : 246');//временная заглушка
                        });
                        return false;
            }

        }
    );
//-----------------------------panel2_cont_butt-------------------------------//
$('li.del').click(
    function(){
        var $id=$(this).closest('div');
        //$("#dialog").dialog('open');
	//$("#dialog").dialog({
	//		bgiframe: true,
//			resizable: false,
//			height:140,
//			modal: true,
//			overlay: {
//				backgroundColor: '#000',
//				opacity: 0.5
//			},
//			buttons: {
//				'Delete': function() {
  //                                      $.post('/targets/del/',
    //                                    {id:$id.attr('id')},
      //                                  function(){
        //                                    $id.empty();
          //                                  $(this).dialog('close')
            //                            },
              //                          'json');

//                                    }/
//				,
//				Cancel: function() {
//					$(this).dialog('close');
//				}
//			}
		//});
    }
);
$('li#edit').click(
    function(){
    	document.location='/targets/'
});
})