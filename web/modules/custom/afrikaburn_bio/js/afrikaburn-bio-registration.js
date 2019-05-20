/**
 * @file
 * AfrikaBurn shared form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistration = {
    attach: function (context, settings) {

      // Unhide the submit button
      $('.form-actions').removeClass('hidden')

      // Block email copy/paste
      $('.user-form .form-email').on(
        'cut copy paste', (e) => e.preventDefault()
      );

      // Let wizardy things happen first
      setTimeout(
        () => {

          // Validate retype
          $('#edit-field-email-retype-0-value').blur(
            function(){

              var
                field = $('#edit-field-email-retype-0-value'),
                error = $('.user-register-form .retype-error'),
                email = $('#edit-mail')

              if (field.val() != email.val()){
                if (error.length == 0) {
                  email.add(field)
                    .addClass('error')
                    .after('<label class="error retype-error">Email address and Retype Email address should match!</label>')
                } else {
                  error.show()
                }
              } else {
                error.hide()
              }
            }
          )

        }, 100
      )
    }
  }

})(jQuery)
