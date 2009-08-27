$(document).ready(function(){
    /* Display or Hide Mini-Menu For Categories List & Operations List + Adding and Removing Class "act" */
    $(".line tr, .operation_list tr").hover(
            function(){$(this).addClass("act"); $(this).find(".cont > ul:not(.select_list)").show();},
            function(){$(this).removeClass("act"); $(this).find(".cont > ul").hide();}
            );
    /* Display or Hide Actions List In Operations List */
    $(".operation_list th .cont").hover(
            function(){$(this).find(".select_list").show();},
            function(){$(this).find(".select_list").hide();}
            );
    /* Display or Hide Additional Fields by checking/unchecking Checkbox */
    $("#fonline").click(function(){
        if($(this).is(':checked')){$(".additional").show();}
        else if($(this).not(':checked')){$(".additional").hide();}
    });

    /* Show "Add Goal" Form */
    $(".add span").click(function(){
        $("#popupaddobject").show();
    });

    /* Hide "Add Goal" Form & Calendar*/
    $(".inside .close").click(function(){
        $("#popupaddobject").hide();
        $("#popupadd_calendar").hide();
    });
    /* Display or Hide Tooltip For "Add Category" Button */
    $(".addcategories a").hover(
            function(){$(this).next().fadeIn("fast");},
            function(){$(this).next().fadeOut("fast");}
            );

    /* Display Calendar */
    $("#addtocalendar_but a").click(function(){
        $("#popupadd_calendar").show();
    });

    /* Display Feedback Form */
    $(".addmessage").click(function(){
        $("#popupreport").show();
    });
    /* Hide Feedback Form */
    $("#popupreport .but").click(function(){
        $("#popupreport").hide();
    });

    /* Show or Hide Add Operation Form by Clicking "Add Operation" Button */
    $("#addoperation_but").click(function(){
        $(this).toggleClass("act");
        if($(this).hasClass("act")){$(".addoperation").show();}
        else if($(this).hasClass("add_operation_but")){$(".addoperation").hide();}
    });

    /* Display or Hide "Add New Category" Form */
    $(".addcategories a, .cont .edit a, .cont .add a").click(function(){
        $(this).toggleClass("clicked");
        if($(this).hasClass("clicked")){$(".form_block").show();}
        else if($(this).hasClass("trigger")){$(".form_block").hide();}
    });
    /* Inserting Values Into "Add New Category" Form When User Click "Edit" Category & "Add" Category*/
    $(".cont .edit a, .add a").click(function(){
        $(".trigger").addClass("clicked");
        $("#addoperation_but").addClass("act");
        $(".addoperation").show();
        if($(".cont .edit a, .add a").hasClass("cat")){$(".addoperation").hide(); $("#addoperation_but").removeClass("act");}
        var f_namecat = $(this).parent().parent().parent().parent().parent().find("td.w1 a").text(); // Get Category Name
        $("#f_namecat").val(f_namecat); // Change "Add New Category" Form's Field
        var f_catsys = $(this).parent().parent().parent().parent().parent().find("td.w3").text(); // Get System Category Name
        // Function Searching System Category Name in Categories SelectBox And Make It Selected
        var f_catsys_lenght = $("#f_catsys option").length;
        for(var i=0; i<f_catsys_lenght; i++){
            if($("#f_catsys option").eq(i).text() == f_catsys){$("#f_catsys option").eq(i).attr("selected","selected");}
        }
        var f_cattype = $(this).parent().parent().parent().parent().parent().find("td.w2").text(); // Get System Category Type Name
        // Function Searching System Category Type Name in Category Types SelectBox And Make It Selected
        var f_cattype_length = $("#f_cattype option").length;
        for(var j=0; j<f_cattype_length; j++){
            if($("#f_cattype option").eq(j).text() == f_cattype){$("#f_cattype option").eq(j).attr("selected","selected");}
        }


        var money_type = $(this).parent().parent().parent().parent().parent().find("td.el_6").text();
        var f_oper_type = $(this).parent().parent().parent().parent().parent().find("td.el_2 a").text();
        var amount = $(this).parent().parent().parent().parent().parent().find("td.el_3 b").text();
        var category = $(this).parent().parent().parent().parent().parent().find("td.el_5").text();
        var ftags = $(this).parent().parent().parent().parent().parent().find("td.el_7").text();
        var fmes = $(this).parent().parent().parent().parent().parent().find("td.el_8 .cont p").text();
        var fdate = $(this).parent().parent().parent().parent().parent().find("td.el_4").text();

        var money_type_length = $("#money_type select option").length;
        var f_oper_type_length = $("#f_oper_type select option").length;
        var amount_length = $(".amount select option").length;
        var category_length = $(".category select option").length;

        $("#ftags").val(ftags);
        $("#fmes").val(fmes);
        $("#fdate").val(fdate);

        // Searching Operation Type Name in Operation Types SelectBox And Make It Selected
        for(j=0; j<f_oper_type_length; j++){
            if($("#f_oper_type select option").eq(j).text() == f_oper_type){$("#f_oper_type select option").eq(j).attr("selected","selected");}
        }

        // Searching Amount Type Name in Operation Types SelectBox And Make It Selected
        for(j=0; j<amount_length; j++){
            if($(".amount select option").eq(j).text() == amount){$(".amount select option").eq(j).attr("selected","selected");}
        }

        // Searching Category Type Name in Category Types SelectBox And Make It Selected
        for(j=0; j<category_length; j++){
            if($(".category select option").eq(j).text() == category){$(".category select option").eq(j).attr("selected","selected");}
        }

        // Searching Money Type Name in Category Types SelectBox And Make It Selected
        for(j=0; j<money_type_length; j++){
            if($("#money_type select option").eq(j).text() == money_type){$("#money_type select option").eq(j).attr("selected","selected");}
        }
        
    });
    /* Hiding Category Line */
    $(".cont .del a").click(function(){
        $(this).parent().parent().parent().parent().parent().hide();
    });

});