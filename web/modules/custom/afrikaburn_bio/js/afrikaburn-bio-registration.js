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

          var email = $('#edit-mail')

          // Validate retype
          $('#edit-field-email-retype-0-value').blur(
            function(){

              var
                field = $('#edit-field-email-retype-0-value'),
                error = $('.user-register-form .retype-error')

              if (field.val() != email.val()){
                if (error.length == 0) {
                  email.add(field)
                    .addClass('error')
                    .after('<div class="form-item--error-message  retype-error">Email address and Retype Email address should match!</div>')
                } else {
                  error.show()
                }
              } else {
                error.hide()
              }
            }
          )

          email.blur(
            function(){

              var
                error = $('.user-register-form .mail-error')

              if (email.val().match(/.+\.de$/)){
                if (error.length == 0) {
                  email
                    .addClass('error')
                    .after('<div class="form-item--error-message  mail-error">This email address end in .de - mail filters in german ISPs block our system mails. Please use a different email address.</div>')
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
