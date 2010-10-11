/**
 * @desc Мастер старта
 * @author Konstantin Kitmanov
 */

;easyFinance.widgets.wizard = (function(){
    var container,
        tabsHeads,
        tabsContents;

    var videoPrefix = "/upload/video/help/"
    var player;

    function startPlay(trgt) {
        if (!player) {
            player = flowplayer("playerWizard", "/swf/flowplayer-3.1.5.swf", {
                clip: {
                    autoPlay: false,
                    autoBuffering: false
                }
            });
        }

        player.play(videoPrefix + trgt[0].ondblclick().file);
    }

    function onTabSelected(evt) {
        var trgt = $(this);
        tabsContents.removeClass('selected');
        trgt.next('dd').andSelf().addClass('selected');
        startPlay(trgt);
    }

    function init(selector) {
        container = $(selector ? selector : '#popupWizard');

        tabsHeads = container.find('dt');
        tabsContents = container.find('dd');

        tabsHeads.bind('click', onTabSelected);

        container.dialog({
            title: "Мастер старта",
            bgiframe: true,
            autoOpen: false,
            width: 680,
            modal:true
        })
    }

    function show() {
        container.dialog('open');
        if (!player) { // визард еще не открывался
            tabsHeads.eq(0).trigger('click');
        }
    }

    return {
        init: init,
        show: show
    }
})();

