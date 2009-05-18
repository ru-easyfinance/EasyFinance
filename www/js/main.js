// JavaScript Document
function getObjectById (id) {
    var Object;
    if (typeof(document.getElementById) != 'undefined') {
        Object = document.getElementById(id);
    }
    else if (typeof(document.all) != 'undefined') {
        Object = document.all(id);
    }
    else {
        return false;
    }
    return Object;
}

var dtCh= ".";
var minYear=1980;
var maxYear=2100;

function isInteger(s){
	var i;
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    return true;
}

function stripCharsInBag(s, bag){
	var i;
    var returnString = "";
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year){
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function isDate(dtStr){
	var daysInMonth = DaysArray(12)
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strDay=dtStr.substring(0,pos1)
	var strMonth=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
		alert("?????? ???? ?????? ???? : ??.??.????")
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
		alert("??????? ?????????? ?????")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
		alert("??????? ?????????? ????!")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		alert("??????? ??? ????? "+minYear+" ? "+maxYear)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
		alert("????? ???? ?? ??????????!")
		return false
	}
return true
}

function ValidateForm(){
	var dt=document.getElementById("sel3");
	if (isDate(dt.value)==false){
		dt.focus()
		return false
	}
    return true
}

function checkDrain() {
	if (getObjectById('isDrain').checked)
	{
		getObjectById('pos_oc').style.backgroundColor="#ffc29e";
		getObjectById('minus').innerHTML = '-';
	}else{
		getObjectById('pos_oc').style.backgroundColor="#b7ea96";
		getObjectById('minus').innerHTML = '';
	}
}

function onSavePeriodic(formName) {
	var arrError = new Array();
	var err = '';
	getObjectById('error_money').style.color = "";
	getObjectById('next_date').style.color = "";
	getObjectById('povtor').style.color = "";
	//getObjectById('err_remind').style.color = "";
	
	if (getObjectById('pos_mc').value == 'NaN')
	{
		getObjectById('error_money').style.color = "red";
		err = err + '<li>???????? ??????!</li>';
	}
	
	if (getObjectById('pos_oc').value == '')
	{
		getObjectById('error_money').style.color = "red";
		err = err + '<li>????? ?? ?????? ???? ??????!</li>';
	}
	
	if (getObjectById('sel5').value == '')
	{
		getObjectById('next_date').style.color = "red";
		err = err + '<li>???? ?? ?????? ???? ??????!</li>';
	}
	
	if ( (getObjectById('povt2').checked && getObjectById('povtor_num').value == "") || (getObjectById('povt2').checked && getObjectById('povtor_num').value == '0'))
	{
		getObjectById('povtor').style.color = "red";
		err = err + '<li>?????? ?? ?????? ???? ??????!</li>';
	}
	
	if ( isNaN(getObjectById('povtor_num').value) )
	{
		getObjectById('povtor').style.color = "red";
		err = err + '<li>? ??????? ???????? ??????!</li>';
	}

	if (err != "")
	{
		getObjectById('arrError').innerHTML = "<br /><font size='+1' color='#FF0000'>????????, ??????!</font><ul>" + err + "</ul>";
		return false;
	}else{
		if (formName == "form_add")
		{
			document.forms.form_add.submit();
		}
		if (formName == "form_edit")
		{
			document.forms.form_edit.submit();
		}
	}
}

function onSumChange() {	
    var Input1 = null;
    var Input2 = null;
	var tmp = null;

    Input1 = getObjectById('pos_oc');
    Input2 = getObjectById('pos_mc');

	if (Input1 && Input2) {
		tmp = convertCurrency(Input1.value);
		if (tmp) {
			Input2.value = round(tmp, 4);
		}
		else {
			Input2.value = '';
		}
		return true;
	}
	Input2.value = '???????!';
	return false;
}

function onSumChangeEdit() {
    var Input1 = null;
    var Input2 = null;
	var tmp = null;

    Input1 = getObjectById('pos_oc_edit');
    Input2 = getObjectById('pos_mc_edit');

	if (Input1 && Input2) {
		tmp = convertCurrency(Input1.value);
		if (tmp) {
			Input2.value = round(tmp, 4);
		}
		else {
			Input2.value = '';
		}
		return true;
	}
	Input2.value = '???????!';
	return false;
}

function convertCurrency(sum) {
	var tmp = sum + " ";
	var pattern = /\,/;
	
	sum = tmp.replace(pattern,'.');
	
	return sum;
}

function round(number,X) {
    // rounds number to X decimal places, defaults to 2
    X = (!X ? 2 : X);
    return Math.round(number*Math.pow(10,X))/Math.pow(10,X);
}

function confirmDelete(text) {
    if (confirm("Вы действительно хотите удалить " + text + " ?")) {           
        document.forms.form_del.submit();
    }
}

function switch_cat_type(to)
{
    if (to == 1)
    {
        document.getElementById('new_cat').style.backgroundColor = '#fAf9f3';
        document.getElementById('old_cat').style.backgroundColor = '#ffffff';

        document.forms[0].cat_id_new.disabled = false;
        document.forms[0].cat_name_new.disabled = false;

        document.forms[0].cat_id_old.disabled = true;

        return true;
    }
    else if (to == 0)
    {

        document.getElementById('new_cat').style.backgroundColor = '#ffffff';
        document.getElementById('old_cat').style.backgroundColor = '#fAf9f3';

        document.forms[0].cat_id_new.disabled = true;
        document.forms[0].cat_name_new.disabled = true;

        document.forms[0].cat_id_old.disabled = false;

        return true;
    }
    else 
    {
        document.getElementById('new_cat').style.backgroundColor = '#ffffff';
        document.getElementById('old_cat').style.backgroundColor = '#fAf9f3';

    }
}

function check_convert_currency()
{
	if (document.filter.check_group_account.checked)
	{
		document.filter.check_select_currency.disabled = false;
	}
	else
	{
		document.filter.check_select_currency.disabled = true;
	}
}

function getId(id)
{
	alert(id);
//	alert(document.getElementById('to_account').options);	
}

function switch_account(to, from)
{
    if (to == from)
    {
        document.getElementById('td_kat').style.backgroundColor = '#ffffff';

        document.forms[0].pos_oc.disabled = true;

        return true;
    }
    else
    {
        document.getElementById('td_kat').style.backgroundColor = '#fAf9f3';

        document.forms[0].pos_oc.disabled = false;

        return true;
    }
}

function checkPass(first, repeate){
    if(repeate.value != first.value) {
        if(document.getElementById) {
            document.getElementById("t_checkPass").innerHTML = "Пароли не совпадают!";
            document.getElementById("t_checkPass").style.color = "red";
			first.style.color = "red";
            repeate.style.color = "red";
        }
        return false;
    } else {
        if(document.getElementById) {
            document.getElementById("t_checkPass").innerHTML = "Пароль совпадает!";
            document.getElementById("t_checkPass").style.color = "green";
			first.style.color = "green";
            repeate.style.color = "green";
        }
        return true;
    }
}

//////////////////////////////////////////////////////////// SEND REQUEST /////////////////////////
function sendRequest() {
		$.ajax({
			url: "index.php",
			dataType: "html",
			type: "POST",
			data: {
				email: $("#b_name").attr("value"),
				text: $("#b_text").attr("value"),
				captcha: $("#b_captcha").attr("value")
			},
			cache: false,
			beforeSend: function (XMLHttpRequest) {
				$("#b_name").attr("disabled", "disabled");
				$("#b_text").attr("disabled", "disabled");
				$("#b_captcha").attr("disabled", "disabled");
				$("#b_submit").attr("disabled", "disabled");
				$("#AjaxLoading").css("display", "block");
				$("#AjaxResult").css("display", "none");
				$("#form").each()
				this;
			},
			timeout: 10000,
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				$("#b_name").attr("disabled", "");
				$("#b_text").attr("disabled", "");
				$("#b_captcha").attr("disabled", "");
				$("#b_submit").attr("disabled", "");
				$("#AjaxLoading").css("display", "none");
				$("#AjaxResult").css("display", "block");
				$("#AjaxResult").html(textStatus);
				$("#captcha").attr("src", $("#captcha").attr("src")+"?"+Math.random());
				$("#b_captcha").attr("value", "");
			},
		
			success: function(html) {
				$("#b_name").attr("disabled", "");
				$("#b_text").attr("disabled", "");
				$("#b_captcha").attr("disabled", "");
				$("#b_submit").attr("disabled", "");
				$("#AjaxLoading").css("display", "none");
				$("#AjaxResult").css("display", "block");
				$("#AjaxResult").html(html);
				$("#captcha").attr("src", $("#captcha").attr("src")+"?"+Math.random());
				$("#b_captcha").attr("value", "");
			}
		});
	}

//////////////////////////////////////////////////////////// ACCOUNT /////////////////////////////////////////

function formCreateAccountVisible()
{
	if ($("#formCreateAccount").is(":hidden")) 
	{
		$.get('/index.php',{modules: 'accounts',action : 'getStepCreateAccount'},getStepCreateAccount);
		$('#formCreateAccount').slideDown('normal');
	}
}

function formCreateAccountUnVisible()
{ 
	accountNextStep(1);
	$('#formCreateAccount').hide();
}

function accountNextStep(step)
{
	switch (step)
	{
			case 1:
				$.get('index.php',{modules: 'accounts',action : 'getStepCreateAccount', account: document.getElementById("typeAccount").value},getStepCreateAccount);
				break;
			case 2:
				$.get('index.php',{modules: 'accounts',action : 'getStepCreateAccount',step: '2', type: document.getElementById("typeAccount").value},getStepCreateAccount);
				break;
	}
		/*switch (document.getElementById("typeAccount").value)
		{
			case '0':
				alert('0');
				break;
			case '4':
				if (step == 1)
				{
					$.get('index.php',{modules: 'accounts',action : 'getStepCreateAccount', account: '4'},getStepCreateAccount);
				}
				if (step == 2)
				{
					$.get('index.php',{modules: 'accounts',action : 'getStepCreateAccount',step: '2', type: '4'},getStepCreateAccount);
					break;
				}
				break;
		}*/
}

function getStepCreateAccount(data)
{
	$("#firstFormCreateAccount").html(data);
}

function accountSave(data)
{
	switch (data)
	{		
		case 4:
			saveAccountDeposite();
			break;
	}
}

function saveAccountDeposite()
{
	var dateCreated = document.getElementById("sel3").value;
	var name = document.getElementById("name").value;
	var bank = document.getElementById("bank").value;
	var sum = document.getElementById("pos_mc").value;
	var currency = document.getElementById("currency").value;
	var percent = document.getElementById("percent").value;
	var getpercent = document.getElementById("getpercent").value;
	var from_account = document.getElementById("selectAccountForTransfer").value;
	var from_currency = document.getElementById("currency_add").value;
	var type = 4;
	
	var modules = "accounts";		
	var action = "saveAccount";
	$.get('index.php',{modules: modules,action : action,name: name, type: type, bank:bank, sum:sum, currency:currency, percent:percent, getpercent:getpercent, dateCreated:dateCreated, from_account:from_account, from_currency:from_currency},accountsAfterInsert);
}

function changeAccountsList()
{
	var modules = "accounts";		
	var action = "getAccountList";	
		
	$("#accountsDataList").hide();
		
	$.get('/index.php',	{modules: modules,action : action},	accountsListSuccess); 
}

function accountsListSuccess(data)
{		
	$("#accountsDataList").html(data);
	$("#accountsDataList").show();
}

function changeAccountFromMoneyForDeposit()
{
	var id =document.getElementById("selectAccountForTransfer").value;
	var currency = document.getElementById("currency").value;
	$("#currencyFromMoneyForDeposit").html("&nbsp;");

	$.get('/index.php',{modules:"accounts",action:"getCurrency",id:id, currency:currency, type:"add"},changeCurrencyFromMoneyForDeposit);
}

function changeCurrencyFromMoneyForDeposit(data)
{
	$("#currencyFromMoneyForDeposit").html(data);
}

function accountsAfterInsert(data)
{
	changeAccountsList();
}