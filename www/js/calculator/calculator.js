/**
 * Class,realised calculate math functions(@see #function2Val) and utilites for work with numbers
 * @author Alexandr *rewle* Ilychev
 * tested on IE7, FF3.5, Chrome
 * @version 1.0
 * @todo in version 2 : ln lg ^ math_constans
 * @todo in version 3 : setup function,html panel,events,runtime text formating
 */
function OJCalc()
{
    /**
     * Leads a str to a Float
     * @param {String} value
     * @return {String}
     */
    function normaliseFloat(value)
    {
        var str = value.toString();
        str = str.replace(/[^0-9\.]/gi,'');
        return str;
    }
    /**
     * Leads a input value to a kind convenient for work
     * @param {String} value
     * @return {String}
     */
    function _normaliseFString(value)
    {
        var str = value.toString() + '=';
        str = str.replace(/[^0-9\.\=\+\-\*\/\(\)]/gi,'');
        return str;
    }
    /**
     * Transformation of the *normalised*(@see #_normaliseFString) line to an array of objects {number - sign}
     * @param {String} nString
     * @return {Object}
     */
    function _str2EArray(nString)
    {
        var i=0,events = new Array,tmp='';
        while(nString.match(/\-?[0-9]+(\.{1}[0-9]+)?[\=\+\-\*\/]/)){
            tmp = nString.match(/\-?[0-9]+(\.{1}[0-9]+)?[\=\+\-\*\/]/);
            events[i] = {
                number:parseFloat(tmp[0]),
                mark:tmp[0].substr(tmp[0].length-1,1)
            };
            nString = nString.replace(/\-?[0-9]+(\.{1}[0-9]+)?[\=\+\-\*\/]/,'');
            i++;
        }
        return events;
    }
    /**
     * Calculate a functions value and return it
     * @param {String} fn_str
     * @return {Float}
     */
    function function2Val(fn_str){
        var tmp,tmp2;
        var str = _normaliseFString(fn_str)
        while (str.indexOf('(') !=-1)
        {
            tmp = str.match(/\([0-9\.\+\-\*\/]{0,}\)/)
            tmp2 = tmp[0].substring(1,tmp[0].indexOf(')'))
            tmp2 = function2Val(tmp2)
            str = str.replace(tmp[0], tmp2);

        }

        var events = _str2EArray(str);
        var len = events.length
        var k = 0,i;
        var ret = new Array;
        for(i = 0;i<len;i++)//do *
        {
            ret[k]=events[i];
            
            if (ret[k-1])
            {
                if(ret[k-1].mark == '*'){
                    ret[k-1].number *=ret[k].number;
                    ret[k-1].mark =ret[k].mark;
                    k--;
                }
                else if (ret[k-1].mark == '/'){
                    ret[k-1].number /=ret[k].number;
                    ret[k-1].mark =ret[k].mark;
                    k--;
                }
            }
            k++;
        }
        ret[k] ={mark:'+',number:0};
        var r = 0;
        len = ret.length
        for(i = 0;i<len;i++)//do -
        {
            
            if (ret[i-1]&&ret[i-1].mark == '-'){
                r -=ret[i].number;
            }else{
                r +=ret[i].number;
            }

        }
        return r;
    }

    /**
     * Divide number of a digit with a presets delemiter???
     * @param number{String|Float|Int}
     * @param del{String}
     * @return {String}
     */
    function formatNumber(number,del)
    {
        var str = _normaliseFloat(number).toString();
        str = str.replace(/[0-9]{3,3}\./, '/$&');
        if(str.indexOf('.')==-1)
            str +='.*'
        while (str != str.replace(/[0-9]{3,3}\//, '+$&')){
            str = str.replace(/[0-9]{3,3}\//, '+$&');
            str = str.replace(/\//,del);
            str = str.replace(/\+/,'/');
        }
        str = str.replace(/\//,del);
        str = str.replace(/\.[0-9]{3,3}/, '$&/');
        while (str != str.replace(/\/[0-9]{3,3}/, '$&+')){
            str = str.replace(/\/[0-9]{3,3}/, '$&+');
            str = str.replace(/\//,del);
            str = str.replace(/\+/,'/');
        }
        str = str.replace(/\//,del);
        str = str.replace(/\.*/,'');
        return str;
    }
    return {
        function2Val:   function2Val,
        formatNumber:   formatNumber,
        normaliseFloat: normaliseFloat}
}

/**
 * Plugin for jQuery, realized methods from OJCalc class for work in HTML
 * @author Alexandr *rewle* Ilychev
 * tested on IE7, FF3.5, Chrome
 * @version 1.0
 * @todo in version 2 : HTML command pane,diferent initializing and enabled/disabled function,formating return value
 */
(function($){
    $.fn.OJCalc = function (){
        var calc = OJCalc();
        $(this).live('keypress',function(e){
            var val =$(this).val();
            if (e.which == 13){
                $(this).val(calc.function2Val(val));
                return false;
            }
        })
    }
})(jQuery)
