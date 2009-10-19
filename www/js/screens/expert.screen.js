/**
 * @desc Expert Screen
 * @author Andrey [Jet] Zharikov
 */

$(document).ready(function(){
    // init widgets
    easyFinance.widgets.expertEditInfo.init('#widgetExpertEditInfo', easyFinance.models.expert);
    easyFinance.widgets.expertEditPhoto.init('#widgetExpertEditPhoto', easyFinance.models.expert);
    easyFinance.widgets.expertEditCertificates.init('#widgetExpertEditCertificates', easyFinance.models.expert);
    easyFinance.widgets.expertEditServices.init('#widgetExpertEditServices', easyFinance.models.expert);
})