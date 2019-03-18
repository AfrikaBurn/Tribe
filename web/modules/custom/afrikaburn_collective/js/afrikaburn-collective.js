/**
 * @file
 * AfrikaBurn collective behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnCollective = {
    attach: function (context, settings) {

      jQuery.each(Drupal.views.instances,

        (i, view) => {
          if (['my_collectives', 'collective_posts'].indexOf(view.settings.view_name) > -1) {
            view.$view.once('view-refresher', () => delete view.settings.pager_element);
          }
        }
      )

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
                if (!$('.view-collective-posts details[open]').length) $('.view-collective-posts').triggerHandler('RefreshView')
              }, 10000
            )

          }
        }
      )
    }
  }
})(jQuery)