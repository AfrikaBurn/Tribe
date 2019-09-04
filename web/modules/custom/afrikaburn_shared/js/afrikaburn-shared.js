/**
 * @file
 * AfrikaBurn shared javascript.
 */

'use strict';

(function ($, toValidate) {

  Drupal.behaviors.afrikaBurnShared = {
    attach: function (context, settings) {

      // Text size fixing
      $('*[size]').removeAttr('size')

      // Validate everything on blur, changed
      function validateMe(){
        var element = $(this)
        element.valid && !element.hasClass('editor-processed')
          ? element.valid()
          : false
        if (element.hasClass('valid')) {
          element.add(element.parent())
            .siblings('.form-item--error-message').remove()
        }
        if ($('.form-item--error-message').length==0) $('form .messages--error').remove()
      }
      $(toValidate, context).blur(validateMe)
      $(toValidate, context).change(validateMe)

      // Scroll to error linked to in messages
      $('.messages--error:not(.delete)', context).each(
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

      // Dim submit buttons
      $('form').submit(
        function() {
          $('input[type="submit"]', this).css('opacity', 0.5).click(
            (e) => e.preventDefault() && flase
          )
        }
      )
    }
  }

})(jQuery, '.form-email,.form-text,.form-tel,.form-autocomplete,.form-checkbox,.form-select,.form-textarea,.form-file,.form-number,.form-date')