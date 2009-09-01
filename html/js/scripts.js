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
        $("#poputags_add").hide();
        $("#poputags_edit").hide();
        $(".tags_list ul li a.edit").toggleClass("edit");
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

    /* Hiding Category Line */
    $(".cont .del a").click(function(){
        $(this).parent().parent().parent().parent().parent().hide();
    });

    /* Show & Hide Popup Tags Edit Form */
    $(".tags_list ul li").click(function(e){
        $(this).children().toggleClass("edit"); // Creating Clicked Tag Marker for Identification by "Change" Button.
        var tag_name = $(this).children().text(); // Getting Tag Name
        var y = e.pageY; // Getting Cursor Vertical Position
        $("#poputags_edit").css({'left' : 20 + 'px', 'top' : y - 10 + 'px', 'display' : 'block'}); // Show Popup Form
        $("#poputags_edit #fptags").val(tag_name); // Inserting Into Popup Form Clicked Tag Name
    });
    /* Changing Tag Name */
    $("#poputags_edit .but").click(function(){
        var new_tag_name =  $("#poputags_edit #fptags").val(); // Getting New Tag Value From Popup Form
        $(".tags_list ul li a.edit").text(new_tag_name).toggleClass("edit"); // Insering New Tag Name Into Link With Class "Edit" & Removing It's Marker
        $("#poputags_edit").hide(); // Close Popup Window
    });
    /* Add New Tag */
    $(".tags_list .add").click(function(e){
        var y = e.pageY; // Getting Cursor Vertical Position
        $("#poputags_add").css({'left' : 20 + 'px', 'top' : y - 10 + 'px', 'display' : 'block'}); // Show Popup Form
    });
    $("#poputags_add .but").click(function(){
        var add_tag_name =  $("#poputags_add #fpatags").val(); // Getting New Tag Value From Popup Form
        $(".tags_list ul").append('<li><a href="#">'+ add_tag_name +'</a></li>');
        $("#poputags_add").hide(); // Close Popup Window
    });

    /* Join */
    $(".join").click(function(){
        $("#popupaddobject").show();
        var target_name = $(this).parent().find("a.name").text();
        $("#popupaddobject #fpname2").val(target_name);
    });

    /* Edit Target */
    $(".f_f_edit, .f_f_copy").click(function(){
        var object_name = $(".object .descr a").text();
        $("#popupaddobject #fpname2").val(object_name);
        $("#popupaddobject").show();
    });
    /* Remove Target */
    $(".f_f_del").click(function(){
        $(this).parent().parent().parent().hide();
    });

    /* Show Hided Objects */
    $(".show_all span").click(function(){
        $(this).slideUp("fast");
        $(".hided").slideDown("fast");
    });

    /* Show/ Hide Mini-Menu For List Container */
    $(".l_n_cont").hover(
            function(){$(this).next().find("ul").show();},
            function(){$(this).next().find("ul").hide();}
            );
    $(".cont ul").hover(
        function(){$(this).show();},
        function(){$(this).hide();}
        );
});