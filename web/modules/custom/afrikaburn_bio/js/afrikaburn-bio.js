/**
 * @file
 * AfrikaBurn shared form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnBio = {
    attach: function (context, settings) {

      // Text size fixing
      $('*[size]').removeAttr('size')

    }
  }

})(jQuery)
