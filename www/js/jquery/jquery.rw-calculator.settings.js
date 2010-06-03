/**
 * @author rewle
 * @see rwCalculator
 */
$.rwCalculator = {};
$.rwCalculator.version = "2.0 a";
$.rwCalculator.initialized = false;
$.rwCalculator.buttonPanel = {
    '0': {
        '0': {
            'class':'printed',
            'text':'1'
        },
        '1': {
            'class':'printed',
            'text':'2'
        },
        '2': {
            'class': 'printed',
            'text': '3'
        },
        '3': {
            'class': 'printed',
            'text': '/'
        },
        '4': {
            'class': 'special',
            'text': 'X',
            'event': 'hide',
            'title': 'Скрыть'
        }
    },
    '1': {
        '0': {
            'class':'printed',
            'text':'4'
        },
        '1': {
            'class':'printed',
            'text':'5'
        },
        '2': {
            'class': 'printed',
            'text': '6'
        },
        '3': {
            'class': 'printed',
            'text': '*'
        },
        '4': {
            'class': 'special',
            'text': 'C',
            'event': 'clear',
            'title': 'Очистить'
        }
    },
    '2': {
        '0': {
            'class':'printed',
            'text':'7'
        },
        '1': {
            'class':'printed',
            'text':'8'
        },
        '2': {
            'class': 'printed',
            'text': '9'
        },
        '3': {
            'class': 'printed',
            'text': '-'
        },
        '4': {
            'class': 'special double',
            'text': '=',
            'event': 'calculate',
            'rowspan' : 2
        }
    },
    '3': {
        '0': {
            'class':'printed',
            'text':'0'
        },
        '1': {
            'class':'printed',
            'text':'000'
        },
        '2': {
            'class': 'printed',
            'text': '.'
        },
        '3': {
            'class': 'printed',
            'text': '+'
        }
    }
};

$.rwCalculator.defaultEvents = {
    keypress: [function(e){
            if (e.keyCode == 9) {
                $.rwCalculator.functions.hide();
                $.rwCalculator.functions.calculate();
            }
            else
                if (e.keyCode == 13) {
                    $.rwCalculator.functions.calculate();
                }
        }
    ],
    click: []
};