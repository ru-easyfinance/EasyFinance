function calculate(funcStr){
    if(!funcStr){
        return '0.00';
    }
    funcStr = funcStr.toString().replace(/[^0-9\.\-\+\/\*]/gi,'') || '0';
    funcStr = funcStr.replace(/[\,]/gi, '.').replace(/[\.\.]/gi, '.').replace(/[\+\+]/gi, '+').replace(/[\*\*]/gi, '*').replace(/[\/\/]/gi, '/').replace(/[\-\-]/gi, '-');
    if (funcStr.match(/([0-9]+(\.{1}[0-9]+)?[\+\-\*\/]{1}){0,}[0-9]+(\.{1}[0-9]+)?/)[0] != funcStr){
        return funcStr;
    }
    var sign = 0;
    if (funcStr.match(/[0-9]+(\.{1}[0-9]+)?/)[0] == funcStr){
        sign = new Number(funcStr);
    }else{
        sign = new Number(eval(funcStr));
    }
    return sign.toFixed(2).toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
}

$(document).ready(function(){
    var flag = 0;
    var calculator = $('.calculatorRW');
    if (calculator.length > 0){
        calculator.find('td.printed div').click(function(){
            var val = calculator.find('input').val();
            var txt = $(this).text();
            if (flag !='0'){
                calculator.find('input').val(val+txt);
            }else{
                if ('*/+-'.indexOf(txt) == -1 ){
                    if (txt == '.'){
                        txt = '0.';
                    }
                    calculator.find('input').val(txt);
                    flag = 1;
                }else{
                    calculator.find('input').val(val+txt);
                    flag = 1;
                }
            }
        });
        calculator.find('td.special').click(function(){
            var event = $(this).attr('event');
            var val = 0;
            switch (event){
                case 'clear':
                    calculator.find('input').val('0');
                    flag = 0;
                    break;
                case 'back':
                    val = calculator.find('input').val();
                    calculator.find('input').val(val.substr(0,val.length-2));
                    break;
                case 'calc':
                    val = calculator.find('input').val();
                    calculator.find('input').val(calculate(val));
                    flag = 0;
                    break;
            }
        });
        calculator.keypress(function(e){
            if (e.which == 13){
                var val = calculator.find('input').val();
                calculator.find('input').val(calculate(val));
                flag = 0;
            }
        })
        calculator.find('input').blur(function(){
            var val = calculator.find('input').val();
            calculator.find('input').val(calculate(val));
            flag = 0;
        })

        calculator.find('input').keypress(function(e){
            if (!e.altKey && !e.shiftKey && !e.ctrlKey){
                if (calculator.find('input').val()=='0'){
                    flag = 1;
                }
                var chars = '1234567890.';
                if (flag){
                    chars += '+-*/';
                }
                if (chars.indexOf(String.fromCharCode(e.which)) == -1){
                    var keyCode = e.keyCode;
                    if (keyCode != 13 && keyCode != 46 && keyCode !=8 && keyCode !=37 && keyCode != 39 && e.which != 32)
                        return false;
                }
                flag = 1;
                if (calculator.find('input').val()=='0'){
                    calculator.find('input').val('');
                }
            }
        });
    }
});
