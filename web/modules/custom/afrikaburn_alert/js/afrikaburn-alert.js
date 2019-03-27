/**
 * @file
 * AfrikaBurn alert behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnAlert = {
    attach: function (context, settings) {

      /* Hack hack hack hack hack - stop view from scrolling when refreshed */
      jQuery.each(Drupal.views.instances,
        (i, view) => {
          if (view.settings.view_name == 'alerts') {
            delete view.settings.pager_element
          }
        }
      )

      $(
        () => {
          // if (context == document){
          //   setInterval(
          //     () => {
          //       $('.view-alerts').triggerHandler('RefreshView')
          //     }, 20000
          //   )
          // }

          $(context).find('.flag-unread.action-unflag:not(.flag-processed)').add(
            $(context).filter('.flag-unread.action-unflag:not(.flag-processed)')
          ).each(
            (index, element) => {
              $(element).addClass('flag-processed').find('a.use-ajax').click(
                (event) => $(event.target).parents('article.alert').animate({ height: 'toggle', opacity: 0 }, 1000)
              )
            }
          )

          $(context).parent().find('.flag-unread.action-flag:not(.flag-processed)').each(
            (index, element) => {
              $(element).addClass('flag-processed').find('a.use-ajax').click(
                (event) => $(event.target).parents('article.alert').stop().animate( { height: 'toggle', opacity: 1}, 500)
              )
            }
          )

        }
      )
    }
  }

})(jQuery)
