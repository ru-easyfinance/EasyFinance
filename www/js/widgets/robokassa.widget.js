(function($) {
   var opts = {};
    $.fn.robokassa = function(settings) {
        opts.settings = $.extend({}, $.fn.robokassa.defaults, settings);                
        
        $(this).click(function() {
          opts.settings.target = $(this);
          opts.settings.targetValue = $(this).val();
          opts.settings.service = $(this).attr("id").split("_")[1];
        opts.settings.term = $("#term_" + opts.settings.service).val();
          $(".service_submit_button").attr("disabled", true);
          $(this).attr("value","Ждите");          
        $.fn.robokassa.getUrl();
          return false;
        });
                
    };
    
    $.fn.robokassa.getUrl = function() {
      $.getJSON(opts.settings.url,"service=" + opts.settings.service + "&term=" + opts.settings.term, function(data){
        var script = $("<textarea/>").html(data.result.script).val();
        var html = $(data.result.html);
        html.find("a").remove();
        $("#scriptContainer").html('<script type="text/javascript">' + script + '</script>');
        $("#paymentOptions").html(html);
        $("#paymentBlock").show();
        $("#paymentOptions select").change($.fn.robokassa.switchNotification);
        $("#paymentOptions select").change();
        $(".service_submit_button").attr("disabled", false);
        opts.settings.target.attr("value",opts.settings.targetValue);
      });
    }

    $.fn.robokassa.switchNotification = function() {
      var val = $(this).val();
      $(".notifications dt, .notifications dd").hide();
      $("#title_"+val+", #notification_"+val).show();
    }
    
    $.fn.robokassa.evalScripts = function() {
      eval( this.text || this.textContent || this.innerHTML || "" );
    };
    
    $.fn.robokassa.defaults = {
        url: "/my/robokassa/init.json"
    };
})(jQuery);
