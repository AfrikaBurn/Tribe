
/**
 * @file
 * AfrikaBurn registration window behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistration = {
    attach: function (context, settings) {

      function checkDups(){
        $('#ddt-assign .form-select option, #vp-assign .form-select option, #wap-assign .form-select option')
          .removeAttr('disabled')

        $('#ddt-assign .form-select option:selected:not([value="0"]), #vp-assign .form-select option:selected:not([value="0"]), #wap-assign .form-select option:selected:not([value="0"])')
          .each(
            (index, element) => {
              var
                selector = '#ddt-assign .form-select option[value="' + $(element).attr('value') + '"], #vp-assign .form-select option[value="' + $(element).attr('value') + '"], #wap-assign .form-select option[value="' + $(element).attr('value') + '"]',
                option = $(selector),
                other = option.not(element)

              other.attr('disabled', 'disabled')
            }
          )
      }
      checkDups()

      $(
        () => {
          $('#ddt-assign .form-select, #vp-assign .form-select, #wap-assign .form-select', context).change(checkDups)
        }
      )
    }
  }

})(jQuery)
