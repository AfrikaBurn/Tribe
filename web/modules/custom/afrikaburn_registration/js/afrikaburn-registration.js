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

      $('form.user-form', context).find('#edit-submit').click(
        () => {
          setTimeout(
            () => {
              var
                root = $(this).parents('form').first(),
                panels = root.find('.details-wrapper'),
                tabs = root.find('.vertical-tabs__menu, .horizontal-tabs-list'),
                firstErrorPanel = root.find('.error:visible').first().parents('.details-wrapper'),
                firstErrorTab = tabs[panels.index(firstErrorPanel)]
              $(firstErrorTab).find('a').click()
            },
            100
          )
        }
      )

      // Text size fixing
      $('.user-form .form-text, .user-form .form-email').removeAttr('size')

    }
  };

})(jQuery, '.form-text, .form-tel, .form-autocomplete, .form-checkbox, .form-select, .form-textarea, .form-file, .form-number, .form-date');

