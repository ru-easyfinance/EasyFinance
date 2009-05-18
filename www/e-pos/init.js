// @todo строка 142 валюты, opercode = внутренний ID сделки
// @todo  disabled="disabled" Line: 93
var POS_url = "ajax-homak.php";
$(function() {
    $('#POS_sel_group').change(function() {
        var del = ['POS_tr_groupItems', 'POS_tr_prompt', 'POS_tr_buttons', 'POS_tr_sum', 'POS_tr_currency'];
        for(var i in del) {
            $('#'+del[i]).remove();
        }
        POS_showGroupItems(this);
    });
    $('#loading').ajaxStart(function() { 
        $(this).css('visibility', 'visible');
    });
    $('#loading').ajaxStop(function() { 
        $(this).css('visibility', 'hidden');
    });
});

function POS_showGroupItems(obj)
{
    var tbl = $('#POS_tr_group').parents("table");
    var options = "";
    if( ! document.getElementById('POS_tr_groupItems')) {
        $('<tr id="POS_tr_groupItems">'+
            '<td>Выберите оператора:</td>'+
            '<td colspan="2">'+
            '<select name="POS_sel_operator" id="POS_sel_operator" '+
            'onchange="POS_showOperatorProps(this)">'+
            '<option value="">-</option>'+
            '</select>'+
            '</td></tr>').appendTo(tbl);
    }
    $.ajax({
        url : POS_url,
        data : "group="+obj.value+"&action=groupItems",
        type : "POST",
        dataType : "xml",
        cache : false,
        success : function(xml) {
            var output = '<option value="">-</option>';
            $(xml).find("operator").each(function(i) {
                if($(this).find('mode').text().indexOf("o") != -1) {
                    output += '<option value="'+$.trim($(this).find('code').text())+'">'+
                        $.trim($(this).find('name').text())+'</option>';
                }                
            });
            $('#POS_sel_operator').empty();
            $('#POS_sel_operator').html($(output));
        }
    });
}
function POS_showOperatorProps(obj)
{
    var del = ['POS_tr_prompt', 'POS_tr_buttons', 'POS_tr_sum', 'POS_tr_currency'];
    for(var i in del) {
        $('#'+del[i]).remove();
    }
    $.ajax({
        url : POS_url,
        data : "operator="+obj.value+"&action=operatorProperties",
        type : "POST",
        dataType : "xml",
        cache : false,
        success : function(xml) {
            var tbl = $('#POS_tbl_group');
            if($(xml).find("result").text() == "ERROR") {
                $('<tr id="POS_tr_prompt"><td colspan="3" style="color:red;text-align:center">'+
                    $(xml).find("errortext").text()+'</td></tr>').appendTo(tbl);
            } else if($(xml).find("result").text() == "ACCEPT") {
                alert('accept')

            } else if($(xml).find("result").text() == "OK") {
                if(!document.getElementById("POS_tr_prompt"))
                {
                    $('<tr id="POS_tr_prompt"><td class="POS_prompt">'+$.trim($(xml).find("prompt").text())+'</td>'+
                        '<td><input type="text" name="POS_prompt" id="POS_prompt"  value="" onblur="POS_checkPrompt()" /></td>'+
                        '<td>'+
                        '<input type="hidden" name="POS_regexp" id="POS_regexp" value="'+$(xml).find("regexp").text()+'" />Пример: '+
                        $(xml).find("example").text()+
                        '</td></tr>').appendTo(tbl);
                    $('<tr id="POS_tr_sum">'+
                        '<td>Введите сумму: </td>'+
                        '<td><input type="text" name="POS_sum" onblur="POS_checkSum()" id="POS_sum" value=""></td>'+
                        '<td>Минимальная сумма: <b>'+$(xml).find("minRUR").text()+
                        '</b><br />Максимальная сумма: <b>'+$(xml).find("maxRUR").text()+'</b>'+
                        '<input type="hidden" name="POS_minRUR" id="POS_minRUR" value="'+$(xml).find("minRUR").text()+'" />'+
                        '<input type="hidden" name="POS_maxRUR" id="POS_maxRUR" value="'+$(xml).find("maxRUR").text()+'" />'+
                        '<br />Процентная ставка: <b>'+$(xml).find("percentplus").text()+'%</b></td>'+
                        '</tr>').appendTo(tbl);
                    $('<tr id="POS_tr_currency">'+
                        '<td>Выберите валюту: </td>'+
                        '<td colspan="2"><select name="POS_sel_currency" id="POS_sel_currency" disabled disabled="disabled">'+
                        '<option value="WMR" selected="selected">WMR (WebMoney R)</option>'+
                        '<option value="WMZ">WMZ (WebMoney Z)</option>'+
                        '<option value="WME" >WME (WebMoney E)</option>'+
                        '<option value="WMU" >WMU (WebMoney U)</option>'+
                        '<option value="WMG" >WMG (WebMoney G)</option>'+
                        '<option value="WMB" >WMB (WebMoney B)</option>'+
                        '<option value="WMY" >WMY (WebMoney Y)</option>'+
                        '<option value="MMR" >MoneyMail (Деньги@Mail.Ru)</option>'+
                        '<option value="RMR" >RBK Money</option>'+
                        '<option value="WCR" >WebCreds</option>'+
                        '<option value="WMD" >оплата в кредит [WM]</option>'+
                        '<option value="ENR" >Элекснет (нал, карты)</option>'+
                        '<option value="YDR" >Яндекс.Деньги</option>'+
                        '</select></td>'+
                        '</tr>').appendTo(tbl);
                    $('<tr id="POS_tr_buttons">'+
                        '<td align="right"><input type="submit" onclick="POS_submit()" value="Оплатить" /></td>'+
                        '<td colspan="2"><input type="submit" onclick="POS_cancel()" value="Отменить" /></td>'+
                        '</tr>').appendTo(tbl);
                }
            }
        }
    });
}
function POS_checkPrompt()
{
    var el = $('#POS_prompt');
    var re = $('#POS_regexp').val();
    if(el.val().match(re) == null) {
        alert($('td.POS_prompt').text().replace(":", '')+' в неправильном формате');
        return false;
    }
    return true;
}
function POS_checkSum()
{
    var el = $('#POS_sum');
    var v = $.trim(el.val()).replace(",", ".");
    if(v == "") {
        el.val($('#POS_minRUR').val());
    } else if(isNaN(parseFloat(v))) {
        alert("Сумма не является цифрой");
    }
    if(v.indexOf(".") == -1) {
        v += ".00";
        el.val(v);
    } else {
        v = v.split(".");
        for(var i= v[1].length; i<2; i++) {
            v[1] += "0";
        }
        v = v[0]+"."+v[1];
        el.val(v);
    }
    if(v < $('#POS_minRUR').val()) {
        alert('Минимальная сумма: '+$('#POS_minRUR').val());
        el.val($('#POS_minRUR').val());
    } else if (v > $('#POS_maxRUR').val()) {
        alert('Максимальная сумма: '+$('#POS_maxRUR').val());
    }
}
function POS_submit()
{
    if(!POS_checkPrompt()) return;
    POS_checkSum();
    var param = "operator="+$('#POS_sel_operator').val()
    +"&sum="+$('#POS_sum').val()
    +"&account="+$('#POS_prompt').val()
    +"&paycurr="+$('#POS_sel_currency').val()
    param += "&action=postParam";
    $.ajax({
        url : "e_pos_3_5.php",
        data : param,
        type : "POST",
        dataType: "xml",
        cache : false,
        success: function(xml) {
            if($(xml).find('operstatus').text() == "ERROR") {
                var text = $(xml).find('errornumber').text() + ' '
                    + $(xml).find('errortext').text();
                $('#POS_output').html(text);
            } else if($(xml).find('operstatus').text() == "ACCEPT") {
                window.location.href = 'e_pos_3_4.php?p='+$.trim($(xml).find('operID').text());
            }
        } 
    });
    return false;
}
function POS_cancel()
{
    alert('Отменить?');
    return false;
}