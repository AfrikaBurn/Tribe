
/**
 * @file
 * AfrikaBurn wrangler form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistration = {
    attach: function (context, settings) {
      $( window ).unload(function() {
        window.opener.reload();
      });
    }
  }

})(jQuery)
