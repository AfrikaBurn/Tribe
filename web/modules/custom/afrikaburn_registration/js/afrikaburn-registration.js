/**
 * @file
 * AfrikaBurn shared form behaviors.
 */

(function ($, toValidate) {

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

      // Form validation
      $(toValidate, context).blur(
        function(){
          $(this).valid ? $(this).valid() : false;
        }
      );

      // Jump to the first error
      root.find('#edit-submit').click(jumpToError)      
      jumpToError()

      // Validate retype
      $('#edit-group-identity .js-next').click(
        function(){
          alert('h')
	}
      )

      /*$('#edit-field-email-retype-0-value').valid(
        function(){
	  return $(this).val() == $('#edit-mail').val()
	}
      )*/
    }
  }

function jumpToError(){
  setTimeout(
    function(){
      var
        firstErrorPanel = root.find('.error:visible').first().parents('.details-wrapper'),
        firstErrorTab = tabs[panels.index(firstErrorPanel)]
      $(firstErrorTab).find('a').click()
    },
    100
  )
}


})(jQuery, Drupal.behaviors.afrikaburnToValidate)

