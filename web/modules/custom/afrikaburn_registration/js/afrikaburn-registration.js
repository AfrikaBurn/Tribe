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

      // Validate retype
      $('.js-next').click(
        function(){
          alert('h')
      	}
      )

    }
  }

})(jQuery)