/**
 * @file
 * AfrikaBurn collective behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnCollective = {
    attach: function (context, settings) {

      /* Hack hack hack hack hack - stop view from scrolling when refreshed */
      // jQuery.each(Drupal.views.instances,
      //   (i, view) => {
      //     if (['my_collectives', 'collective_posts'].indexOf(view.settings.view_name) > -1) {
      //       delete view.settings.pager_element
      //     }
      //   }
      // )

      $('.view-posts form').attr('action', '?post=all')

      $(
        () => {

          // Fade on paging
          $('.view-collective-projects .pager__item a,\
             .view-collective-members .pager__item a,\
             .view-my-collectives .pager__item a').click(
            function(){
              $('.view-content', $(this).parents('.view')).animate({ opacity: 0.5})
            }
          )

          // Make member filter autosubmit
          $('.view-collective-members .views-exposed-form .form-text,\
             .view-my-collectives .views-exposed-form .form-text')
            .not('.filter-processed')
            .keyup(

            function() {

              clearTimeout($(this).data('timeout'))
              $(this).data('timeout',
                setTimeout(
                  () => {
                    $('.form-submit', $(this).parents('.views-exposed-form')).click()
                  },
                  500
                )
              )

              $(this)
                .addClass('busy')
                .parents('.view')
                .find('.view-content')
                .animate({ opacity: 0.5})
            }
          ).addClass('filter-processed')


          if (context != document){
            var filter = $('.form-text', context).focus();
            filter[0] ? filter[0].setSelectionRange(100, 100) : false;
          }
        }
      )

      // $(
      //   () => {

          // if (context == document) {

          //   setInterval(
          //     () => {
          //       $('.view-my-collectives').triggerHandler('RefreshView')
          //     }, 30000
          //   )

          //   setInterval(
          //     () => {
          //       if (!$('.view-collective-posts details[open]').length) $('.view-collective-posts').triggerHandler('RefreshView')
          //     }, 15000
          //   )

          // }
      //   }
      // )
    }
  }
})(jQuery)