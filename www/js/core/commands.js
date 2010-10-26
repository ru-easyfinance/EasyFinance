var commands = (function() {
    var doc = $(document);

    function onCmd(evt) {
        doc.trigger(utils.getType.command(evt.target), utils.getParams(evt.target))
    };

    $('.js-cmd').live('click', onCmd);
})();