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

          // Close other membership popups
          $('.collective-actions details summary').click(
            function(event){
              $('.collective-actions details').not($(this).parent()).removeAttr('open')
            }
          )

          // Close membership popup
          $('.collective-actions details section a.cancel').click(
            function(event){
              $(this).parents('details').removeAttr('open')
              event.stopPropagation()
              return false
            }
          )

          // Make member filter autosubmit
          var filtered = context == document
            ? $(
                '.view-collective-members, .view-my-collectives',
                context
              )
            : $(context).filter('.view-collective-members, .view-my-collectives')
          $('.views-exposed-form .form-text', filtered)
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

          // Hide filters on single page views
          $('.view-collective-members,\
             .view-my-collectives', context)
            .not('.filter-hide-processed')
            .each(
              (index, element) => {
                if ($('nav.pager', element).length == 0){
                  $('.view-filters', element).hide()
                }
              }
            ).addClass('filter-hide-processed')

          // Focus filter box
          if (context != document){
            var filter = $('.form-text', context).focus();
            filter[0] ? filter[0].setSelectionRange(100, 100) : false;
          }

          // Collective form settings
          function checkOptions(){

            $('#edit-field-settings-public', context).is(':checked')
              ? $('#edit-field-settings-public-members, #edit-field-settings-public-admins').removeAttr('disabled')
              : $('#edit-field-settings-public-members, #edit-field-settings-public-admins').attr('disabled', 'disabled').prop('checked', false)

            $('#edit-field-settings-public-members', context).is(':checked')
              ? $('#edit-field-settings-private-members').attr('disabled', 'disabled').prop('checked', false)
              : $('#edit-field-settings-private-members').removeAttr('disabled')

            $('#edit-field-settings-public-admins', context).is(':checked')
              ? $('#edit-field-settings-private-admins').attr('disabled', 'disabled').prop('checked', false)
              : $('#edit-field-settings-private-admins').removeAttr('disabled')

          }
          $('#edit-field-settings input').click(checkOptions)
          checkOptions()

          // Go directly to membership requests
          if (context == document) $('#block-membersblock a[href="#group_requests"]').click()
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