/**
 * @file
 * AfrikaBurn shared form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistration = {
    attach: function (context, settings) {

      var
        root = $('.user-form', context),
        panels = root.find('.details-wrapper'),
        tabs = root.find('.vertical-tabs__menu, .horizontal-tabs-list').children()

      // Text size fixing
      $('.user-form .form-text, .user-form .form-email').removeAttr('size')
      // Unhide the submit button
      $('.form-actions').removeClass('hidden')

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