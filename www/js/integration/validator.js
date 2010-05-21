function dValidatorClass() {
    this.initVars();
}

dValidatorClass.prototype.initVars = function() {
    //Inits
    this.validatableElems = new Array(); //Types, msgs and help texts objects
    this.validatableElemsAdditional = new Array(); //Multi-Validation
    this.emsgs = new Array(); //Error messages
    this.fakeLabels = new Array(); //Toggled labels for fakeCheckbox and fakeRadio
    this.errorInAddit = -1;
    this.groupId = null;
    this.alertType = 0;
    this.validatingAllFields = false; //Flag shows where are we: in catchOnChange (false) or in validateForm (true)
    this.existsCheckPassed = {};

    //Config
    this.tooltipsDiv = 'divhlptxt';
    this.defaultTooltip = 'Пожалуйста, заполните все необходимые поля формы.<br /><br />Будьте внимательны при их заполнении.';
    this.errorDescrDiv = 'diverrtxt';
    this.breakOnError = true;
    this.existsCheckUrl = '/exists_check.php?dataType=json';

    //RegExp
    this.reBlank = new RegExp('^[\\n\\s ]*$', 'i');
    this.reMail = new RegExp('^[a-z][a-z0-9_\\-\\.]+@[a-z0-9_\\-\\.]+\\.[a-z]{2,4}$', 'i');
    this.reUrl = new RegExp('^http:\\/\\/[a-z0-9_\\-\\.]+\\.[a-z]{2,}(\/.*)?$', 'i');
    this.reAlnum = new RegExp('^[a-zа-яё0-9_\-]+$', 'i');
    this.reAlnumEn = new RegExp('^[a-z0-9_\-]+$', 'i');
    this.reAlnumRu = new RegExp('^[а-яё0-9_\-]+$', 'i');
    this.reAlphaEn = new RegExp('^[a-z\-]+$', 'i');
    this.reAlphaRu = new RegExp('^[а-яё\-]+$', 'i');
    this.reRusLit = new RegExp('^[а-яё0-9\\(\\)\\-\\.\\*\\+\\?\\/,:";!%\\s№]+$', 'i');
    this.rePhone = new RegExp('^[0-9() \\-\\+]+$', 'i');
    this.rePhoneStrict = new RegExp('^\\+(\\d){1,2}\\(\\d{3}\\)\\d{3}-(\\d{4}|\\d{2}-\\d{2})$', 'i');
    this.reDate1 = new RegExp('^(\\d{4})-(\\d{2})-(\\d{2})$', 'i');
    this.reDateTime1 = new RegExp('^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2})(:(\\d{2}))?$', 'i');
    this.reDate2 = new RegExp('^(\\d{2})[\\/\\.](\\d{2})[\\/\\.](\\d{4})$', 'i');
    this.reDateTime2 = new RegExp('^(\\d{2})[\\/\\.](\\d{2})[\\/\\.](\\d{4}) (\\d{2}):(\\d{2})(:(\\d{2}))?$', 'i');
    this.reInteger = new RegExp('^-?\\d+$', 'i');
    this.reFloat = new RegExp('^-?\\d+(\\.?\\d+)?$', 'i');

    this.reLengthOfParam = new RegExp('^(\\d*)..(\\d*)$', 'i');
    this.reIntervalParam = new RegExp('^(\\d+(\\.\\d+)?)?..(\\d+(\\.\\d+)?)?$', 'i');

    //IE crunch
    this.doNotSetFocus = (navigator.userAgent.indexOf('MSIE') != -1) ? true : false;
}

dValidatorClass.prototype.catchOnFocus = function(input) {
    this.toggleForm(input.form);

    if (($('#' + this.tooltipsDiv).length == 1) && this.validatableElems[input.form.name] && (typeof(this.validatableElems[input.form.name]) != 'undefined') && this.validatableElems[input.form.name][input.name] && (typeof(this.validatableElems[input.form.name][input.name]) != 'undefined')) {
        $('#' + this.tooltipsDiv).html(this.validatableElems[input.form.name][input.name].hlpText);
    }
}

dValidatorClass.prototype.catchOnBlur = function(input) {
    if ($('#' + this.tooltipsDiv).length == 1) {
        $('#' + this.tooltipsDiv).html(this.defaultTooltip);
    }
}

dValidatorClass.prototype.catchOnChange = function(input, fakeField, alertType) {
    this.toggleForm(input.form);
    this.form = input.form;
    this.validatingAllFields = false;

    if (alertType && (typeof(alertType) != 'undefined')) {
        this.alertType = alertType;
    } else {
        this.alertType = 0;
    }

    if (fakeField && (typeof(fakeField) != 'undefined')) {
        var fakeInput = $('#' + fakeField);
    }

    if (input.type && (typeof(input.type) != 'undefined') && ((input.type == 'radio') || (input.type == 'checkbox'))) {
        var group = input.form[input.name];
        if (group.length && (typeof(group.length) != 'undefined')) {
            for (var i = 0; i < group.length; i++) {
                if (group[i] && (typeof(group[i]) != 'undefined') && (typeof(group[i].id) != 'undefined') && (group[i].id != '')) {
                    input = group[i];
                }
            }
        }
    }

    if (fakeInput && (typeof(fakeInput) != 'undefined')) {
        input = fakeInput;
    }

    var validationType = this.getValidationType(input);
    if (validationType !== false) {
        if ((typeof(this.params.group) != 'undefined') && (this.params.group === true) && (typeof(form[input.name].length) != 'undefined') && (form[input.name].length > 1)) {
            var flagCorrect = true;
            var curCorr = false;
            for (var j = 0; j < input.form[input.name].length; j++) {
                curCorr = this.checkField(input.form[input.name][j], validationType);
                flagCorrect = flagCorrect && curCorr;
                if ((typeof(this.params.single) != 'undefined') && (this.params.single === true) && curCorr) {
                    flagCorrect = true;
                    break;
                }
            }

            if (this.breakOnError) {
                if (flagCorrect) {
                    this.makeCorrect(input);
                } else {
                    this.makeIncorrect(input);
                }
            }
        } else {
            var res = this.checkField(input, validationType);
            if (this.breakOnError) {
                if (res) {
                    this.makeCorrect(input);
                } else {
                    this.makeIncorrect(input);
                }
            }
        }
    }
}

dValidatorClass.prototype.getValidationType = function(input) {
    this.toggleForm(input.form);

    if (this.validatableElems[input.form.name] && (typeof(this.validatableElems[input.form.name]) != 'undefined') && this.validatableElems[input.form.name][input.name] && (typeof(this.validatableElems[input.form.name][input.name]) != 'undefined')) {
        this.params = this.validatableElems[input.form.name][input.name].params;
        if (!this.emsgs[input.form.name] || (typeof(this.emsgs[input.form.name]) == 'undefined')) {
            this.emsgs[input.form.name] = new Array();
        }
        this.emsgs[input.form.name][input.name] = this.validatableElems[input.form.name][input.name].errMsg;
        this.groupId = this.validatableElems[input.form.name][input.name].groupId;
        this.rehashParamsAliases();
        return this.validatableElems[input.form.name][input.name].validationType;
    }
    return false;
}

dValidatorClass.prototype.rehashParamsAliases = function() {
    for (var i in this.params) {
        switch (i) {
            case 'in':
            case 'within':
            case 'interval':
                this.params.multitude = this.params[i];
                break;
            case 'allow_nil':
            case 'allow_blank':
                if (this.params[i] === true) {
                    this.params.empty = this.params[i];
                }
                break;
            case 'with':
                this.params.regex = this.params[i];
                break;
            case 'maximum':
            case 'less_than':
            case 'less_than_or_equal':
                this.params.to = this.params[i];
                break;
            case 'minimum':
            case 'greater_than':
            case 'greater_than_or_equal':
                this.params.from = this.params[i];
                break;
            case 'only_integer':
                if (this.params[i] === true) {
                    this.params.integer = true;
                }
                break;
            case 'is':
                this.params.equal_to = this.params[i];
                break;
        }
    }
}

dValidatorClass.prototype.checkField = function(input, type, silent) {
    this.toggleForm(input.form);

    if (typeof(silent) == 'undefined') {
        silent = false;
    }

    this.errorInAddit = -1;
    var res = this.checkFieldMain(input, type);
    if (!silent) {
        if (res) {
            this.makeCorrect(input);
        } else if (!this.breakOnError) {
                this.makeIncorrect(input);
        }
    }

    if ((res || !this.breakOnError) && this.validatableElemsAdditional[input.form.name] && (typeof(this.validatableElemsAdditional[input.form.name]) != 'undefined')) {
        var validationArray = this.validatableElemsAdditional[input.form.name][input.name];

        if (validationArray && (typeof(validationArray) != 'undefined') && (validationArray.length > 0)) {
            for (var i =0; i < validationArray.length; i++) {
                this.params = validationArray[i].params;
                this.rehashParamsAliases();
                this.groupId = validationArray[i].groupId;
                this.errorInAddit = i;
                if (this.checkFieldMain(input, validationArray[i].validationType)) {
                    if (!silent) {
                        this.makeCorrect(input);
                    }
                } else {
                    res = false;
                    if (!silent) {
                        if (this.breakOnError) {
                            break;
                        } else {
                            this.makeIncorrect(input);
                        }
                    }
                }
            }
        }
    }

    return res;
}

dValidatorClass.prototype.checkFieldMain = function(input, type) {
    this.toggleForm(input.form);
    var i, pos, ifId, tmpRes, tmpType, linkedFld;

    if ((typeof(this.params.empty) != 'undefined') && (this.params.empty === true) && (input.value == '')) {
        return true;
    }

    if ((typeof(this.params.validateIf) != 'undefined') && (this.params.validateIf != '') && (typeof(input.form[this.params.validateIf]) != 'undefined')) {
        linkedFld = input.form[this.params.validateIf]

        if (this.params.validateIfFldIs == 'correct') {
            //If correct
            tmpType = this.getValidationType(linkedFld);
            if (tmpType !== false) {
                tmpRes = this.checkField(linkedFld, tmpType, true);
            } else {
                tmpRes = true;
            }
        } else {
            //If just flag
            if (linkedFld.type == 'checkbox') {
                tmpRes = linkedFld.checked;
            } else {
                tmpRes = (linkedFld.value == 1);
            }
        }

        if ((typeof(this.params.validateIfNegative) != 'undefined') && (this.params.validateIfNegative === true)) {
            tmpRes = !tmpRes;
        }

        if (!tmpRes) {
            return true;
        }
    }

    switch (type) {
        case 'file':
        case 'validates_presence_of':
        case 'blank':
            return (!this.reBlank.test(input.value));
            break;
        case 'blankOneOf':
            if (this.params.fld_list && (typeof(this.params.fld_list) != 'undefined') && (this.params.fld_list.length > 0)) {
                for (var i = 0; i < this.params.fld_list.length; i++) {
                    if (this.params.fld_list[i] && (typeof(this.params.fld_list[i]) != 'undefined')) {
                        if (!this.reBlank.test(input.form[this.params.fld_list[i]].value)) {
                            return true;
                        }
                    }
                }
            }
            return false;
            break;
        case 'mail':
            return (this.reMail.test(input.value));
            break;
        case 'url':
            return (this.reUrl.test(input.value));
            break;
        case 'length':
            if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return ((input.value.length >= this.params.from) && (input.value.length <= this.params.to));
                } else {
                    return (input.value.length <= this.params.to);
                }
            } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                return (input.value.length >= this.params.from);
            }
            return false;
            break;
        case 'alnum':
            if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return ((input.value.length >= this.params.from) && (input.value.length <= this.params.to) && this.reAlnum.test(input.value));
                } else {
                    return ((input.value.length <= this.params.to) && this.reAlnum.test(input.value));
                }
            } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                return ((input.value.length >= this.params.from) && this.reAlnum.test(input.value));
            }
            return this.reAlnum.test(input.value);
            break;
        case 'alnumru':
            if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return ((input.value.length >= this.params.from) && (input.value.length <= this.params.to) && this.reAlnumRu.test(input.value));
                } else {
                    return ((input.value.length <= this.params.to) && this.reAlnumRu.test(input.value));
                }
            } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                return ((input.value.length >= this.params.from) && this.reAlnumRu.test(input.value));
            }
            return this.reAlnumRu.test(input.value);
            break;
        case 'alnumen':
            if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return ((input.value.length >= this.params.from) && (input.value.length <= this.params.to) && this.reAlnumEn.test(input.value));
                } else {
                    return ((input.value.length <= this.params.to) && this.reAlnumEn.test(input.value));
                }
            } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                return ((input.value.length >= this.params.from) && this.reAlnumEn.test(input.value));
            }
            return this.reAlnumEn.test(input.value);
            break;
        case 'enalpha':
            if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return ((input.value.length >= this.params.from) && (input.value.length <= this.params.to) && this.reAlphaEn.test(input.value));
                } else {
                    return ((input.value.length <= this.params.to) && this.reAlphaEn.test(input.value));
                }
            } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                return ((input.value.length >= this.params.from) && this.reAlphaEn.test(input.value));
            }
            return this.reAlphaEn.test(input.value);
            break;
        case 'rualpha':
            if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return ((input.value.length >= this.params.from) && (input.value.length <= this.params.to) && this.reAlphaRu.test(input.value));
                } else {
                    return ((input.value.length <= this.params.to) && this.reAlphaRu.test(input.value));
                }
            } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                return ((input.value.length >= this.params.from) && this.reAlphaRu.test(input.value));
            }
            return this.reAlphaRu.test(input.value);
            break;
        case 'ruslit':
            if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return ((input.value.length >= this.params.from) && (input.value.length <= this.params.to) && this.reRusLit.test(input.value));
                } else {
                    return ((input.value.length <= this.params.to) && this.reRusLit.test(input.value));
                }
            } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                return ((input.value.length >= this.params.from) && this.reRusLit.test(input.value));
            }
            return this.reRusLit.test(input.value);
            break;
        case 'phone':
            return (this.rePhone.test(input.value));
            break;
        case 'equal2':
            if (this.params.linkedFld && (typeof(input.form[this.params.linkedFld]) != 'undefined')) {
                return (input.value == input.form[this.params.linkedFld].value);
            } else {
                return false;
            }
            break;
        case 'ext':
            var exts = this.explode(this.params.extensions, '-');
            var re = new RegExp('\\.([a-z0-9]+)$', 'i');
            if (re.test(input.value)) {
                var arr = re.exec(input.value);
                return this.inArray(arr[1], exts);
            } else {
                return false;
            }
            break;
        case 'radio':
            var group = input.form[input.name];
            if (group.length && (typeof(group.length) != 'undefined')) {
                for (var i = 0; i < group.length; i++) {
                    if (group[i] && (typeof(group[i]) != 'undefined') && group[i].checked) {
                        return true;
                    }
                }
            } else {
                return input.checked;
            }
            return false;
            break;
        case 'fakeradio':
            return (input.value != '');
            break;
        case 'checkbox':
            var group = input.form[input.name];
            if (group.length && (typeof(group.length) != 'undefined')) {
                for (var i = 0; i < group.length; i++) {
                    if (group[i] && (typeof(group[i]) != 'undefined') && group[i].checked) {
                        return true;
                    }
                }
            } else {
                return input.checked;
            }
            return false;
            break;
        case 'fakecheckbox':
            var group = input.form[input.name];
            if (group.length && (typeof(group.length) != 'undefined')) {
                for (var i = 0; i < group.length; i++) {
                    if (group[i] && (typeof(group[i]) != 'undefined') && (group[i].value != '')) {
                        return true;
                    }
                }
            } else {
                return (input.value != '');
            }
            break;
        case 'date':
            var year, mnth, day;

            if (this.reDate1.test(input.value)) {
                // ^(\d{4})-(\d{2})-(\d{2})$
                year = RegExp.$1;
                mnth = RegExp.$2;
                day = RegExp.$3;
            } else if (this.reDate2.test(input.value)) {
                // ^(\d{2})[\/\.](\d{2})[\/\.](\d{4})$
                year = RegExp.$3;
                mnth = RegExp.$2;
                day = RegExp.$1;
            } else {
                return false;
            }

            year = this.toInt(year);
            mnth = this.toInt(mnth);
            day = this.toInt(day);

            var tmpDt = new Date(mnth + '/' + day + '/' + year);
            if ((tmpDt.getFullYear() == year) && (tmpDt.getMonth() == mnth - 1) && (tmpDt.getDate() == day)) {
                return true;
            }

            return false;
            break;
        case 'datetime':
            var year, mnth, day, hrs, min, sec;
            if (this.reDateTime1.test(input.value)) {
                //^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2})(:(\\d{2}))?$
                year = RegExp.$1;
                mnth = RegExp.$2;
                day = RegExp.$3;
                hrs = RegExp.$4;
                min = RegExp.$5;
                sec = RegExp.$7;
            } else if (this.reDateTime2.test(input.value)) {
                //^(\\d{2})[\\/\\.](\\d{2})[\\/\\.](\\d{4}) (\\d{2}):(\\d{2})(:(\\d{2}))?$
                year = RegExp.$3;
                mnth = RegExp.$2;
                day = RegExp.$1;
                hrs = RegExp.$4;
                min = RegExp.$5;
                sec = RegExp.$7;
            } else {
                return false;
            }

            year = this.toInt(year);
            mnth = this.toInt(mnth);
            day = this.toInt(day);
            hrs = this.toInt(hrs);
            min = this.toInt(min);
            sec = ((this.toInt(sec) > 0) ? this.toInt(sec) : 0);

            var tmpDt = new Date(mnth + '/' + day + '/' + year + ' ' + hrs + ':' + min + ':' + sec);
            if ((tmpDt.getFullYear() == year) && (tmpDt.getMonth() == mnth - 1) && (tmpDt.getDate() == day) && (tmpDt.getHours() == hrs) && (tmpDt.getMinutes() == min) && (tmpDt.getSeconds() == sec)) {
                return true;
            }

            return false;
            break;
        case 'integer':
            if (this.reInteger.test(input.value)) {
                if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                    if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                        return ((input.value >= this.params.from) && (input.value <= this.params.to));
                    } else {
                        return (input.value <= this.params.to);
                    }
                } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return (input.value >= this.params.from);
                }
                return true;
            }
            return false;
            break;
        case 'float':
            if (this.reFloat.test(input.value)) {
                if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                    if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                        return ((input.value >= this.params.from) && (input.value <= this.params.to));
                    } else {
                        return (input.value <= this.params.to);
                    }
                } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return (input.value >= this.params.from);
                }
                return true;
            }
            return false;
            break;
        case 'validates_format_of':
            return this.params.regex.test(input.value);
            break;
        case 'validates_length_of':
        case 'validates_size_of':
            return this.simulateLengthOf(input);
            break;
        case 'validates_numericality_of':
            var isNum = false;
            if ((typeof(this.params.integer) != 'undefined') && (this.params.integer === true)) {
                isNum = this.reInteger.test(input.value);
            } else {
                isNum = this.reFloat.test(input.value);
            }

            if (isNum) {
                if ((this.params.equal_to != null) && (typeof(this.params.equal_to) != 'undefined') && !isNaN(this.params.equal_to)) {
                    isNum = (isNum && (this.params.equal_to == input.value));
                }

                if (isNum && (typeof(this.params.integer) != 'undefined') && (this.params.integer === true)) {
                    if ((typeof(this.params.odd) != 'undefined') && (this.params.odd === true)) {
                        isNum = (isNum && ((input.value % 2) != 0));
                    }

                    if ((typeof(this.params.even) != 'undefined') && (this.params.even === true)) {
                        isNum = (isNum && ((input.value % 2) == 0));
                    }
                }
            }

            if (isNum) {
                if ((this.params.to != null) && (this.params.to != '') && !isNaN(this.params.to)) {
                    if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                        return ((input.value >= this.params.from) && (input.value <= this.params.to));
                    } else {
                        return (input.value <= this.params.to);
                    }
                } else if ((this.params.from != null) && (this.params.from != '') && !isNaN(this.params.from)) {
                    return (input.value >= this.params.from);
                }
            }

            return isNum;
            break;
        case 'validates_inclusion_of':
            return this.simulateInclusionOf(input);
            break;
        case 'validates_exclusion_of':
            return !this.simulateInclusionOf(input);
            break;
        case 'exists':
            return this.existsCheck(input);
            break;
        default:
            return false;
    }
}

dValidatorClass.prototype.simulateLengthOf = function(input) {
    var matches = this.reLengthOfParam.exec(this.params.multitude);

    if (matches && (typeof(matches) != 'undefined')) {
        matches[1] = this.toInt(matches[1]);
        matches[2] = this.toInt(matches[2]);

        if (this.params.tokenizer && (typeof(this.params.tokenizer) != 'undefined')) {
            var tmp = input.value;
            var i = 0;
            while (this.params.tokenizer.test(tmp)) {
                tmp = tmp.replace(this.params.tokenizer, '');
                i++;
            }
            var x = i;
        } else {
            var x = input.value.length;
        }

        if (!isNaN(matches[1])) {
            if (!isNaN(matches[2])) {
                return (x >= matches[1]) && (x <= matches[2]);
            }

            return (x >= matches[1]);
        } else if (!isNaN(matches[2])) {
            return (x <= matches[2]);
        }
    }

    return false;
}

dValidatorClass.prototype.simulateInclusionOf = function(input) {
    var matches = this.reIntervalParam.exec(this.params.multitude);

    //alert(matches + ' = ' + this.reIntervalParam + '.exec(' + this.params[0] + ')');
    if (matches && (typeof(matches) != 'undefined')) {
        //Make it num
        matches[1] = matches[1] - 1 + 1;
        matches[3] = matches[3] - 1 + 1;

        if (!isNaN(matches[1])) {
            if (!isNaN(matches[3])) {
                return (input.value >= matches[1]) && (input.value <= matches[3]);
            }

            return (input.value >= matches[1]);
        } else if (!isNaN(matches[3])) {
            return (input.value <= matches[3]);
        }
    } else if ((this.params.multitude != '') && (typeof(this.params.multitude) != 'undefined')) {
        var arr = this.explode(this.params.multitude, ' ');
        return this.inArray(input.value, arr);
    }

    return false;
}

dValidatorClass.prototype.existsCheck = function(input) {
    if (!this.reBlank.test(input.value)) {
        this.lockForm();

        $.ajax({
            type: 'GET',
            url: this.existsCheckUrl + '&model=' + this.params.model + '&id=' + input.value + '&for_field=' + input.name,
            dataType: 'json',
            cache: false,
            success: this.catchExistsCheckSuccess,
            error: this.catchExistsCheckError
        });

        return true;
    }

    return false;
}

dValidatorClass.prototype.catchExistsCheckSuccess = function(data) {
    var res = false;

    if (data && (typeof(data) != 'undefined') && (typeof(data.forField) != 'undefined') && (data.forField != '')) {
        res = data.exists;
    } else {
        //If server returned something wrong, validation will be his his problem
        res = true;
    }
    dValidator.unlockForm();

    if (res) {
        if (dValidator.form[data.forField] && (typeof(dValidator.form[data.forField]) != 'undefined')) {
            dValidator.makeCorrect(dValidator.form[data.forField]);
        }

        if (dValidator.validatingAllFields) {
            dValidator.existsCheckPassed[data.forField] = true;
            if (dValidator.validateForm(dValidator.form)) {
                dValidator.form.submit();
            }
        }
    } else {
        if (dValidator.form[data.forField] && (typeof(dValidator.form[data.forField]) != 'undefined')) {
            dValidator.makeIncorrect(dValidator.form[data.forField]);
        } else {
            //2DO: What should we do???
            //alert('Nothing to hilight!');
        }
    }
}

dValidatorClass.prototype.catchExistsCheckError = function() {
    return false;
}

dValidatorClass.prototype.makeCorrect = function(input) {
    switch (this.alertType) {
        case 0:
            this.makeCorrectHtml(input);
            break;
        case 1:
            //Alert only type. Do nothing.
            break;
        case 2:
        default:
            if ((typeof(input.parentNode) != 'undefined') && input.parentNode) {
                $(input.parentNode).addClass('correct');
                $(input.parentNode).removeClass('incorrect');
            }
    }
}

dValidatorClass.prototype.makeCorrectHtml = function(input) {
    //UnHighlite
    if ((input.type == 'radio') || (input.type == 'checkbox') || (input.type == 'hidden')) {
        $(input.parentNode).removeClass('validation-failed');
        $(input.parentNode).addClass('validation-passed');
    } else {
        $(input).removeClass('validation-failed');
        $(input).addClass('validation-passed');
    }

    //Delete error message
    if (this.groupId && (typeof(this.groupId) != 'undefined')) {
        var ulId = 'err_msg_group_ul_' + this.groupId;
    } else {
        var ulId = input.name + '_err_msg_ul';
        if (this.errorInAddit >= 0) {
            ulId += '_' + this.errorInAddit;
        }
    }
    var liId = input.name + '_err_msg_li';
    if (this.errorInAddit >= 0) {
        liId += '_' + this.errorInAddit;
    }

    var errUl = $$(ulId);
    var errLi = $$(liId);
    if (($('#' + this.errorDescrDiv).length == 1) && errUl && (typeof(errUl) != 'undefined') && errLi && (typeof(errLi) != 'undefined')) {
        $(errLi).remove();

        if (errUl.childNodes.length == 0) {
            $(errUl).remove();
        }

        if (($('#' + this.errorDescrDiv).html() == '') && ($('.error-stack').length == 1)) {
            $('.error-stack').css('display', 'none');
        }
    }
}

dValidatorClass.prototype.makeIncorrect = function(input) {
    this.toggleForm(input.form);

    var errMsg = 'Fill in required fields correctly!';
    if (this.errorInAddit >= 0) {
        errMsg = this.validatableElemsAdditional[input.form.name][input.name][this.errorInAddit].errMsg;
    } else if (this.emsgs[input.form.name] && (typeof(this.emsgs[input.form.name]) != 'undefined') && this.emsgs[input.form.name][input.name] && (typeof(this.emsgs[input.form.name][input.name]) != 'undefined')) {
        errMsg = this.emsgs[input.form.name][input.name]
    }

    switch (this.alertType) {
        case 0:
            this.makeIncorrectHtml(input, errMsg);
            break;
        case 1:
            this.alert(errMsg);
            break;
        case 2:
        default:
            if ((typeof(input.parentNode) != 'undefined') && input.parentNode) {
                $(input.parentNode).addClass('incorrect');
                $(input.parentNode).removeClass('correct');
            }
    }
}

dValidatorClass.prototype.makeIncorrectHtml = function(input, errMsg) {
    //Highlite
    if ((input.type == 'radio') || (input.type == 'checkbox') || (input.type == 'hidden')) {
        $(input.parentNode).removeClass('validation-passed');
        $(input.parentNode).addClass('validation-failed');
    } else {
        $(input).removeClass('validation-passed');
        $(input).addClass('validation-failed');
    }

    //Paste error message
    if ($('#' + this.errorDescrDiv).length == 1) {
        //var htm = '<ul id="' + input.name + '_err_msg"><li>' + this.emsgs[input.form.name][input.name] + '</li></ul>';

        if ($('.error-stack').length == 1) {
            $('.error-stack').css('display', 'block');
        }

        if (this.groupId && (typeof(this.groupId) != 'undefined')) {
            var ulId = 'err_msg_group_ul_' + this.groupId;
        } else {
            var ulId = input.name + '_err_msg_ul';
            if (this.errorInAddit >= 0) {
                ulId += '_' + this.errorInAddit;
            }
        }
        var liId = input.name + '_err_msg_li';
        if (this.errorInAddit >= 0) {
            liId += '_' + this.errorInAddit;
        }


        var errUl = $$(ulId);
        var errLi = $$(liId);
        if (!errLi || (typeof(errLi) == 'undefined')) {
            if (!errUl || (typeof(errUl) == 'undefined')) {
                errUl = document.createElement('ul');
                errUl.id = ulId;
            }

            errLi = document.createElement('li');
            errLi.id = liId;
            errLi.innerHTML = errMsg;

            errUl.appendChild(errLi);

            $('#' + this.errorDescrDiv).append(errUl);
        }
    }
}

dValidatorClass.prototype.makeErrMsgId = function(input) {
    var err_id = input.name;
    var re = /[\[\]]/i;

    while (re.test(err_id)) {
        err_id = err_id.replace(re, '_');
    }

    return 'err_msg_' + err_id;
}

dValidatorClass.prototype.alert = function(msg) {
    alert(msg);
}

dValidatorClass.prototype.explode = function(str, sep) {
    var arr = new Array();

    if (str.indexOf(sep) != -1) {
        var pos;

        while ((pos = str.indexOf(sep)) != -1) {
            arr[arr.length] = str.slice(0, pos);
            str = str.slice(pos + 1, str.length);
        }

        arr[arr.length] = str;
    } else {
        arr[0] = str;
    }

    return arr;
}

dValidatorClass.prototype.inArray = function(needle, haystack) {
    for (i in haystack) {
        if (needle == haystack[i]) {
            return true;
        }
    }
    return false;
}

dValidatorClass.prototype.toInt = function(val, radix) {
    if (/^(-)?(\d+)/.test(val)) {
        var arr = /^(-)?(\d+)/.exec(val);
        if (arr && (typeof(arr) != 'undefined') && (arr[2] > 0)) {
            arr[2]--; arr[2]++;
            if (arr[1] == '-') {
                arr[2] = - arr[2];
            }
            return arr[2];
        }
    }

    return 0;
}

dValidatorClass.prototype.validateForm = function(form, alertType) {
    this.toggleForm(form);

    this.validatingAllFields = true;

    if (this.formIsLocked) {
        //alert('Генацвали, нэ тарапыся!');
        return false;
    } else {
        return this.validateFormMain(form, alertType);
    }
}

dValidatorClass.prototype.validateFormMain = function(form, alertType) {
    this.toggleForm(form);

    if (alertType && (typeof(alertType) != 'undefined')) {
        this.alertType = alertType;
    } else {
        this.alertType = 0;
    }

    var validationType = false;
    var ok = true;
    var focused = false;
    var res = true;
    this.form = form;
//alert(this.existsCheckPassed.toSource());
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i] && (typeof(form.elements[i]) != 'undefined')) {
            validationType = this.getValidationType(form.elements[i]);

            //alert(form.elements[i].name + ' [' + validationType + '] | ' + ((validationType != 'exists') || !this.existsCheckPassed[form.elements[i].name]));
            if ((validationType !== false) && ((validationType != 'exists') || !this.existsCheckPassed[form.elements[i].name])) {
                //alert(form.elements[i].name + ' | ' + validationType + ' | ' + this.params);
                if ((typeof(this.params.group) != 'undefined') && (this.params.group === true) && (typeof(form[form.elements[i].name].length) != 'undefined') && (form[form.elements[i].name].length > 1)) {
                    var flagCorrect = true;
                    var curCorr = false;
                    for (var j = 0; j < form[form.elements[i].name].length; j++) {
                        curCorr = this.checkField(form[form.elements[i].name][j], validationType);
                        flagCorrect = flagCorrect && curCorr;
                        if ((typeof(this.params.single) != 'undefined') && (this.params.single === true) && curCorr) {
                            flagCorrect = true;
                            break;
                        }
                    }

                    if (this.breakOnError) {
                        if (flagCorrect) {
                            this.makeCorrect(form.elements[i]);
                        } else {
                            this.makeIncorrect(form.elements[i]);
                        }
                    }

                    if (!flagCorrect) {
                        if (!focused && (form.elements[i].type != 'hidden')) {
                            if (!this.doNotSetFocus) {
                                form.elements[i].focus();
                                focused = true;
                            }
                        }
                        ok = false;
                        if (this.alertType == 1) {
                            return ok;
                        }
                    }
                } else {
                    res = this.checkField(form.elements[i], validationType);
                    if (this.breakOnError) {
                        if (res) {
                            this.makeCorrect(form.elements[i]);
                        } else {
                            this.makeIncorrect(form.elements[i]);
                        }
                    }

                    if (!res) {
                        if (!focused && (form.elements[i].type != 'hidden')) {
                            if (!this.doNotSetFocus) {
                                form.elements[i].focus();
                                focused = true;
                            }
                        }
                        ok = false;
                        if (this.alertType == 1) {
                            return ok;
                        }
                    }
                }
            }
        }
    }

    return (ok && !this.formIsLocked);
}

dValidatorClass.prototype.toggleForm = function(frm) {
    if (frm && (typeof(frm) != 'undefined') && ((typeof(frm.name) == 'undefined') || (frm.name == ''))) {
        if (frm.id && (typeof(frm.id) != 'undefined')) {
            frm.name = frm.id;
        } else {
            frm.name = '';
        }
    }
}

dValidatorClass.prototype.lockForm = function() {
    this.formIsLocked = true;
    $('#dValidatorPreloader').show();
}

dValidatorClass.prototype.unlockForm = function() {
    this.formIsLocked = false;
    $('#dValidatorPreloader').hide();
}

dValidatorClass.prototype.proto = function() {
}

var dValidator = new dValidatorClass();