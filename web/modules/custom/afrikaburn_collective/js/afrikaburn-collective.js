/**
 * @file
 * AfrikaBurn collective behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnCollective = {
    attach: function (context, settings) {
      $(
        () => {
          if (context == document) {

            setInterval(
              () => {
                $('.view-my-collectives').triggerHandler('RefreshView')
              }, 10000
            )

            setInterval(
              () => {
                if (!$('.view-collective-posts summary[open]').length) $('.view-collective-posts').triggerHandler('RefreshView')
              }, 10000
            )

          }
        }
      )
    }
  }
})(jQuery)