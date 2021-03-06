function wzStepNextBack(direction, step) {
    var reStep = /^wz_tab_(\d+)$/i;
    var step = step ? step : 0;

    if (
        !step && tabs.activeTab && (typeof(tabs.activeTab) != 'undefined') &&
        reStep.test(tabs.activeTab)
    ) {
        step = RegExp.$1 - 0;
    }

    if (direction == '-') {
        step--;
    } else {
        step++;
    }

    if ((step >= 0) && (step < tabs.labels.length)) {
        if ($('#wz_tab_' + step).hasClass('wz_tab_header_not_main')
                && !$('#wz_tab_8').is(':visible'))
            return wzStepNextBack(direction, step);

        tabs.showTab('wz_tab_' + step);
    }

    return false;
}

function wzSaveStep(frm) {
    var data = wzGetFormData(frm);

    if (data.length > 1) {
        data.saveType = "single_step";

        $.ajax({
            "url": '/srv_script.php',
            "type": 'POST',
            "data": data
            /*success: function() {}*/
        });
    }
}

function wzValidateTab() {
    var curFrm = $('#' + this.activeTab + '_content form.wz_frm');

    if (curFrm.length > 0) {
        if (dValidator.validateForm(curFrm.get(0), 2)) {
            $('#' + this.activeTab).removeClass('incorrect');
            $('#' + this.activeTab).addClass('correct');

            wzSaveStep(curFrm.get(0));
        } else {
            $('#' + this.activeTab).removeClass('correct');
            $('#' + this.activeTab).addClass('incorrect');
        }
    }
}

function wzValidateAll() {
    var curFrm;
    var everythingCorrect = true;

    if (tabs.tabs && (typeof(tabs.tabs) != 'undefined') && (tabs.tabs.length > 0)) {
        for (var i = 0; i < tabs.tabs.length; i++) {
            curFrm = $('#' + tabs.tabs[i].id + ' form.wz_frm');

            if (curFrm.length > 0) {
                if (dValidator.validateForm(curFrm.get(0), 2)) {
                    $('#wz_tab_' + i).removeClass('incorrect');
                    $('#wz_tab_' + i).addClass('correct');
                } else {
                    $('#wz_tab_' + i).removeClass('correct');
                    $('#wz_tab_' + i).addClass('incorrect');

                    everythingCorrect = false;
                }
            }
        }
    }

    return everythingCorrect;
}

function wzGetFormData(frm) {
    var i =0;
    var j = 0;
    var data = {"length": 0};

    if (frm.elements && (typeof(frm.elements) != 'undefined') && (frm.elements.length > 0)) {
        for (i = 0; i < frm.elements.length; i++) {
            if (frm.elements[i].name) {
                if ((frm.elements[i].type == 'checkbox') || (frm.elements[i].type == 'radio')) {
                    if (frm.elements[i].checked) {
                        data[frm.elements[i].name] = frm.elements[i].value;
                        j++;
                    }
                } else {
                    data[frm.elements[i].name] = frm.elements[i].value;
                    j++;
                }
            }
        }

        data.length = j;
    }

    return data;
}

function wzMergeObjects(obj1, obj2) {
    var i;

    for (i in obj2) {
        obj1[i] = obj2[i];
    }

    return obj1;
}

function wzObjToArray(obj) {
    var i;
    var result = 'saveType=whole_data';

    if (obj && (typeof(obj) != 'undefined')) {
        for (i in obj) {
            if ((i != 'saveType') && (i != 'length') && (i != 'step_name')) {
                result += '&anketa[' + i + ']=' + encodeURIComponent(obj[i]);
            }
        }
    }

    return result;
}

function wsInitValidator() {
    dValidator.validatableElems['personal_info'] = new Array();

    dValidator.validatableElems['personal_info']['wz_surname'] = {
        'validationType' : 'rualpha',
        'errMsg' : '',
        'params' : {}
    }

    dValidator.validatableElems['personal_info']['wz_name'] = {
        'validationType' : 'rualpha',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['personal_info']['wz_midname'] = {
        'validationType' : 'rualpha',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['personal_info']['wz_surname_translit'] = {
        'validationType' : 'regexp',
        'errMsg' : '',
        'params' : { regexp: /[A-Z][0-9A-Z. -]{0,32}$/i}
    };

    dValidator.validatableElems['personal_info']['wz_name_translit'] = {
        'validationType' : 'regexp',
        'errMsg' : '',
        'params' : { regexp: /[A-Z][0-9A-Z. -]{0,32}/i}
    };

    dValidator.validatableElems['personal_info']['wz_birthdate'] = {
        'validationType' : 'date',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['personal_info']['wz_birthplace'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['personal_info']['wz_sex'] = {
        'validationType' : 'radio',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['personal_info']['wz_citizenship'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['personal_info']['wz_inn'] = {
        'validationType' : 'regexp',
        'errMsg' : '',
        'params' : { regexp: /^()|([0-9]{12})$/ }
    };

    dValidator.validatableElems['registration_address'] = new Array();

    dValidator.validatableElems['registration_address']['wz_reg_country'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['registration_address']['wz_reg_index'] = {
        'validationType' : 'integer',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['registration_address']['wz_reg_region'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['registration_address']['wz_reg_city'] = {
        'validationType' : 'rualpha',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['registration_address']['wz_reg_street'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['registration_address']['wz_reg_house'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['actual_address'] = new Array();

    dValidator.validatableElems['actual_address']['wz_actual_country'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['actual_address']['wz_actual_index'] = {
        'validationType' : 'integer',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['actual_address']['wz_actual_region'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['actual_address']['wz_actual_city'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['actual_address']['wz_actual_street'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['actual_address']['wz_actual_house'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['rf_passport'] = new Array();

    dValidator.validatableElems['rf_passport']['wz_rf_id_series'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['rf_passport']['wz_rf_id_number'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['rf_passport']['wz_rf_id_organisation'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['rf_passport']['wz_rf_id_date'] = {
        'validationType' : 'date',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['contacts'] = new Array();

    /*
     см. #1512
    dValidator.validatableElems['contacts']['wz_mail'] = {
        'validationType' : 'mail',
        'errMsg' : '',
        'params' : {}
    };
    */

    dValidator.validatableElems['contacts']['wz_phone_mob'] = {
        'validationType' : 'regexp',
        'errMsg' : '',
        'params' : { regexp: /^[+][0-9]{11,15}$/i }
    };

    dValidator.validatableElems['work_info'] = new Array();

    dValidator.validatableElems['work_info']['wz_work_name'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['work_info']['wz_work_position'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['work_info']['wz_work_address'] = {
        'validationType' : 'blank',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['work_info']['wz_work_phone'] = {
        'validationType' : 'phone',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['card_info'] = new Array();

    dValidator.validatableElems['card_info']['wz_card_is_main'] = {
        'validationType' : 'radio',
        'errMsg' : '',
        'params' : {}
    };

    /*dValidator.validatableElems['card_info']['wz_card_currency'] = {
        'validationType' : 'radio',
        'errMsg' : '',
        'params' : {}
    };*/

    dValidator.validatableElems['card_info']['wz_card_type'] = {
        'validationType' : 'radio',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['card_info']['wz_card_rush'] = {
        'validationType' : 'radio',
        'errMsg' : '',
        'params' : {}
    };

    dValidator.validatableElems['finish'] = new Array();

    dValidator.validatableElems['finish']['wz_password'] = {
        'validationType' : 'rualpha',
        'errMsg' : '',
        'params' : {}
    };
}

function wzFillSelect(selectId, data) {
    var optionsList = [];
    for (var i = 0, l = data.length; i < l; i++) {
        optionsList.push('<option value="' + data[i] + '">' + data[i] + '</option>');
    }
    $(selectId).append( optionsList.join('') );
}

$(document).ready(function(){
    // инициализируем валидатор
    wsInitValidator();

    $("#btnCopyRegistrationToAddress").click(function() {
        $("#wz_actual_country").val($("#wz_reg_country").val());
        $("#wz_actual_index").val($("#wz_reg_index").val());
        $("#wz_actual_region").val($("#wz_reg_region").val());
        $("#wz_actual_city").val($("#wz_reg_city").val());
        $("#wz_actual_street").val($("#wz_reg_street").val());
        $("#wz_actual_house").val($("#wz_reg_house").val());
        $("#wz_actual_building").val($("#wz_reg_building").val());
        $("#wz_actual_appartment").val($("#wz_reg_appartment").val());

        //$("#btnAddressNext").click();
    });

    $('.wz_tab_header_not_main').hide();
    $('#wz_card_is_main_0').click(function() {
        $('.wz_tab_header_not_main').show();
    });
    $('#wz_card_is_main_1').click(function() {
        $('.wz_tab_header_not_main').hide();
    });

    wzFillSelect('#wz_citizenship', wzCountries);
    wzFillSelect('#wz_reg_country', wzCountries);
    wzFillSelect('#wz_reg_region', wzRegions);
    wzFillSelect('#wz_actual_country', wzCountries);
    wzFillSelect('#wz_actual_region', wzRegions);

    $('#wz_reg_country').change(function() {
        if (this.value != 'РОССИЯ') {
            $('#wz_reg_region').val('Прочее');
        }
    });

    $('#wz_actual_country').change(function() {
        if (this.value != 'РОССИЯ') {
            $('#wz_actual_region').val('Прочее');
        }
    });

});
