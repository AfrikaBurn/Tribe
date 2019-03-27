/**
 * @file
 * AfrikaBurn collective behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnCollective = {
    attach: function (context, settings) {

      /* Hack hack hack hack hack - stop view from scrolling when refreshed */
      jQuery.each(Drupal.views.instances,
        (i, view) => {
          if (['my_collectives', 'collective_posts'].indexOf(view.settings.view_name) > -1) {
            delete view.settings.pager_element
          }
        }
      )

      // Make member filter autosubmit
      $('.view-posts form').attr('action', '?post=all')
      $('.view-collective-members .views-exposed-form .form-text').not('.processed').keyup(
        function() {
          clearTimeout($(this).data('timeout'))
          $(this).data('timeout', setTimeout(() => $('.view-collective-members .form-submit').click(), 500))
          $('.view-collective-members .views-exposed-form .form-text').addClass('busy')
        }
      ).addClass('processed')

      if ($(context).hasClass('view-id-collective_members')){
        $('.form-text', context).focus()[0].setSelectionRange(100, 100);
      }

      $(
        () => {

          if (context == document) {

            setInterval(
              () => {
                $('.view-my-collectives').triggerHandler('RefreshView')
              }, 30000
            )

            setInterval(
              () => {
                if (!$('.view-collective-posts details[open]').length) $('.view-collective-posts').triggerHandler('RefreshView')
              }, 15000
            )

          }
        }
      )
    }
  }
})(jQuery)