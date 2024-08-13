<?php
include ("../../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if (Session::getCurrentInterface() == "central"
   && Session::haveRight("plugin_ticketalerts", READ)) {

   $locale_view = __('Personal View');

   $JS = <<<JAVASCRIPT

   var doOnCentralPage = function() {
      //intercept ajax load of tab
      $(document).ajaxComplete(function(event, jqxhr, option) {
         if (option.url == "../plugins/ticketalerts/ajax/central.php") {
            return;
         }
            //delay the execution (ajax requestcomplete event fired before dom loading)
            setTimeout(function () {

               var suffix = "";
               var selector = ".tab_cadre_central .top:last" +
                  ", .alltab:contains('$locale_view') + .tab_cadre_central .top:last";
               // get central list for plugin and insert in tab
               $(selector).each(function(){                  
                  if (this.innerHTML.indexOf('alert_block') < 0) {
                     if (option.url.indexOf("-1") > 0) { //option.params
                        suffix = "_all";
                     }

                     //prepare a span element to load new elements
                     $(this).prepend("<span id='alert_block" + suffix + "'></span>");

                     //ajax request
                     $("#alert_block" + suffix).load('../plugins/ticketalerts/ajax/central.php');
                  }
               });
            }, 500);    
      });
   }

   $(document).ready(function() {
      $(".ui-tabs-panel:visible").ready(function() {
         doOnCentralPage();
      })

      $("#tabspanel + div.ui-tabs").on("tabsload", function() {
         setTimeout(function() {
            doOnCentralPage();
         }, 300);
      });
   });

JAVASCRIPT;
   echo $JS;
}
