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
          $('#edit-group-account .js-next').click(
            function(){

              var
                retype = $('#edit-field-email-retype-0-value:visible'),
                email = $('#edit-mail')

              if (retype.length && retype.val() != email.val()){
                email.add(retype)
                  .addClass('error')
                  .after('<label class="error retype-error">Email address and Retype Email address should match!</label>')
              }
            }
          )

        }, 100
      )
    }
  }

})(jQuery)
