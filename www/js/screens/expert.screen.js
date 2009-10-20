/**
 * @desc Expert Screen
 * @author Andrey [Jet] Zharikov
 */

$(document).ready(function(){
    // init widgets
    easyFinance.models.expert.load(function(model){
        easyFinance.widgets.expertEditInfo.init('#widgetExpertEditInfo', model);
        easyFinance.widgets.expertEditPhoto.init('#widgetExpertEditPhoto', model);
        easyFinance.widgets.expertEditCertificates.init('#widgetExpertEditCertificates', model);
        easyFinance.widgets.expertEditServices.init('#widgetExpertEditServices', model);
    });
})