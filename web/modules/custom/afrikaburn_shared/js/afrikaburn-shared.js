/**
 * @file
 * AfrikaBurn shared javascript.
 */

'use strict';

(function ($, toValidate) {

  const BLOCK_COLLAPSED = {
    'user-logged-in': ['wrangle', 'publish'],
    'page-node-type-collective': ['bioblock', 'ticketblock', 'my_collectives', 'my_invites']
  }


  Drupal.behaviors.afrikaBurnShared = {
    attach: function (context, settings) {

      // Validate everything on blur
      $(toValidate, context).blur(
        function(){
          $(this).valid ? $(this).valid() : false
        }
      )

      var collapsiblock = Drupal.Collapsiblock.getCookieData()

      // Collapse blocks
      if (context == document){
        $.each(BLOCK_COLLAPSED,
          (bodyClass, blockIDs) => {
            $.each(blockIDs,
              (index, blockID) => {
                collapsiblock[blockID] = !$('body').hasClass(bodyClass)
                var cookieString = JSON.stringify(collapsiblock);
                $.cookie('collapsiblock', cookieString, {
                  path: settings.basePath
                });
              }
            )
          }
        )
      }
    }
  }
})(jQuery, '.form-email,.form-text,.form-tel,.form-autocomplete,.form-checkbox,.form-select,.form-textarea,.form-file,.form-number,.form-date')