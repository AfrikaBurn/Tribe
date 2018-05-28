/**
 * @file
 * AfrikaBurn shared form behaviors.
 */

(function ($, toValidate) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistration = {
    attach: function (context, settings) {

      // Form validation
      $(toValidate, context).blur(
        function(){
          $(this).valid ? $(this).valid() : false;
        }
      );

      // Text size fixing
      $('.user-form input[size]').each(
        () => this.removeAttr('size')
      )

    }
  };

})(jQuery, '.form-text, .form-tel, .form-autocomplete, .form-checkbox, .form-select, .form-textarea, .form-file, .form-number, .form-date');

