// {* $Id$ *}
$(document).ready(function(){
    $('#amount').calculator({
        layout: [
                $.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
                'MR_7_8_9_-' + $.calculator.UNDO,
                'MS_4_5_6_*' + $.calculator.PERCENT ,
                'M+_1_2_3_/' + $.calculator.HALF_SPACE,
                'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS],
        showOn: 'opbutton',
        buttonImageOnly: true,
        buttonImage: '/img/calculator.png'
    });
    $("#start,#end").datepicker({dateFormat: 'dd.mm.yy'});

    $("#button_add_target").click(function(event){
        window.location.replace("/index.php?modules=targets&action=add");
    });
    $("input[type=button]#button_save_cancel").click(function(event){
        if (confirm("Отказаться от сохранения цели?")) {
            window.location.replace("/index.php?modules=targets");
        }
    });
    $("[action=join]").click(function(event){
        var category = $(event.target).parents("td").prev().prev().text();
        var title = $(event.target).parents("td").prev(":first").text();
        window.location.replace("/index.php?modules=targets&action=add&category="+category+"&title="+title);
    });
    $("[action=edit]").click(function(event){
        var target_id = $(event.target).parents("tr[target_id]").attr("target_id");
        window.location.replace("/index.php?modules=targets&action=edit&target_id="+target_id);
    });
    $("[action=del]").click(function(event){
        var target_id = $(event.target).parents("tr[target_id]").attr("target_id");
        var title = $(event.target).parents("tr[target_id]").children().eq(1).text(); //TODO Упростить
        if (confirm("Вы уверены, что хотите удалить цель '"+title+"'?")) {
            window.location.replace("/index.php?modules=targets&action=del&target_id="+target_id);
        }
    });
    $("#button_save_target").click(function(event){
        //TODO Проверяем валидность и сабмитим
        document.form_save_target.submit();
    });
    $("input#url").change(function(event){
        $("#url_click").attr("href",$("input#url").val());
    });
});