$(document).ready(function() {


        $('body').mousemove(function(){
            if (!$('.map div:hover').length)
            {
                $('.w_dialog.mapContent').hide();
                for (var N=2; N<=10; N++)
                $('.w_dialog.mapContent'+N).hide();
            }
           // return false;
        })  
        //$('#w_dialog.mapContent').css('position','absolute');
        
        //$('#w_dialog.mapContent').hide();

	$('.map div').mouseover(function(){//.popup_map div
		var c = $(this).attr('class');
                //alert(c)
		switch (c)
		{
			case 'registration':
                            $('.w_dialog.mapContent').show();
				break;
			case 'CreateSales':
                            $('.w_dialog.mapContent2').css('display','block');
				break;
			case 'categoryEdit':
                            $('.w_dialog.mapContent3').css('display','block');
				break;
                        case 'closeOperation':
                            $('.w_dialog.mapContent4').css('display','block');
				break;
                        case 'infoPanel':
                            $('.w_dialog.mapContent5').css('display','block');
				break;
                         case 'createTransaction':
                            $('.w_dialog.mapContent6').css('display','block');
				break;
                         case 'expertView':
                            $('.w_dialog.mapContent7').css('display','block');
				break;
                         case 'analise':
                            $('.w_dialog.mapContent8').css('display','block');
				break;
                         case 'financeTarget':
                            $('.w_dialog.mapContent9').css('display','block');
				break;
                         case 'capitalCreate':
                            $('.w_dialog.mapContent10').css('display','block');
				break;
		}
                return false;
	});
        //$('#w_dialog.mapContent').hide();
        $('.link.close').click(function(){
            $('.w_dialog.mapContent').hide();
            for (var N=1; N<=10; N++)
            $('.w_dialog.mapContent'+N).hide();
        });
});


