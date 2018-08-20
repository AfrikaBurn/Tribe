/**
 * @file
 * AfrikaBurn shared javascript.
 */

'use strict';

(function ($, toValidate) {

  const BLOCK_COLLAPSED = {
    'user-logged-in': ['wrangle', 'publish'],
    'page-node-type-collective': ['bioblock', 'ticketblock', 'my_collectives', 'my_invites', 'membersblock', 'views_block__collective_projects_past_block']
  }


  Drupal.behaviors.afrikaBurnShared = {
    attach: function (context, settings) {

      // Validate everything on blur
      $(toValidate, context).blur(
        function(){
          var element = $(this)
          element.valid && !element.hasClass('editor-processed') ? element.valid() : false
          if (element.hasClass('valid')) element.siblings('.form-item--error-message').remove()
          if ($('.form-item--error-message').length==0) $('.messages--error').remove()
        }
      )

      // Scroll to error linked to in messages
      $('.messages--error').each(
        (index, messages) => {
          $('a', messages).each(
            (index, link) => {
              $(link).click(
                (event) => {

                  event.preventDefault()

                  var
                    id = $(event.target).attr('href').match(/#.*/)[0],
                    element = $(id + ',' + id + '-wrapper')
                  setTimeout(
                    () => {
                      $('html, body').animate({
                        scrollTop: element.offset().top - 30
                      }, 1000)
                    }, 100);
                }
              )
            }
          )
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

            if (error.length) $('html, body').animate({
              scrollTop: error.offset().top - $(window).height() / 2
            }, 500);

          }, 100
        )
      }

      // Show first tab error
      $('.horizontal-tabs', context).parents('form').submit(
        (event) => {
          showFirstError(event.target)
        }
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