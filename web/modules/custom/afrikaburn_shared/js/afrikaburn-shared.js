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

      // Method to show first form error in tabs
      var showFirstError = function(rootForm) {
        setTimeout(
          () => {

            var
              root = $(rootForm),
              tabs = $('.vertical-tabs__menu, .horizontal-tabs-list', root).children(),
              panels = $('.field-group-tab > .details-wrapper', root),
              error = root.find('.error:visible').first(),
              firstErrorPanel = error.parents('.details-wrapper'),
              firstErrorTab = tabs[panels.index(firstErrorPanel)],
              link = $(firstErrorTab).find('a')

            link.click()

            $('html, body').animate({
              scrollTop: error.offset().top - $(window).height() / 2
            }, 500);

          }, 100
        )
      }

      // Show first tab error
      $('.horizontal-tabs', context).parents('form').submit(
        (event) => showFirstError(event.target)
      )
      showFirstError($('.horizontal-tabs', context).parents('form'))


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