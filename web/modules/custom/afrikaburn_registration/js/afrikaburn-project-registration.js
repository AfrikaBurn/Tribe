
/**
 * @file
 * AfrikaBurn registration window behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistration = {
    attach: function (context, settings) {

      function checkDups(){
        $('#wap-assign .form-select option')
          .removeAttr('disabled')

        $('#wap-assign .form-select option:selected:not([value="0"])')
          .each(
            (index, element) => {
              var
                selector = '#wap-assign .form-select option[value="' + $(element).attr('value') + '"]',
                option = $(selector),
                other = option.not(element)

              other.attr('disabled', 'disabled')
            }
          )
      }
      checkDups()

      $(
        () => {
          $('#wap-assign .form-select', context).change(checkDups)
        }
      )
    }
  }

})(jQuery)
