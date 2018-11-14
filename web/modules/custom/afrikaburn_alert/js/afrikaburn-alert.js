/**
 * @file
 * AfrikaBurn alert behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnAlert = {
    attach: function (context, settings) {
      $(
        () => {

          $(context).find('.flag-unread.action-unflag:not(.flag-processed)').add(
            $(context).filter('.flag-unread.action-unflag:not(.flag-processed)')
          ).each(
            (index, element) => {
              $(element).addClass('flag-processed').find('a.use-ajax').click(
                (event) => $(event.target).parents('article.alert').animate({ height: 'toggle', opacity: 0 }, 5000)
              )
            }
          )

          $(context).parent().find('.flag-unread.action-flag:not(.flag-processed)').each(
            (index, element) => {
              $(element).addClass('flag-processed').find('a.use-ajax').click(
                (event) => $(event.target).parents('article.alert').stop().animate( { height: 'toggle', opacity: 1}, 1000)
              )
            }
          )

        }
      )
    }
  }

})(jQuery)
