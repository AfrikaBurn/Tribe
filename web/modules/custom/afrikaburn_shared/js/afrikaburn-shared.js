/**
 * @file
 * AfrikaBurn shared javascript.
 */

'use strict';

Drupal.afrikaburn = {
  validate: [
    '.form-email',
    '.form-text',
    '.form-tel',
    '.form-autocomplete',
    '.form-checkbox',
    '.form-select',
    '.form-textarea',
    '.form-file',
    '.form-number',
    '.form-date'
  ].join(',')
}

(function ($, toValidate) {

  Drupal.behaviors.afrikaburnShared = {
    attach: function (context, settings) {

      // Validate everything on blur
      $(toValidate, context).blur(
        function(){
          $(this).valid ? $(this).valid() : false
        }
      )
    }
  }
})(jQuery, Drupal.afrikaburn.validate)