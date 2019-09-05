
/**
 * @file
 * AfrikaBurn registration window behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistrationBlock = {
    attach: function (context, settings) {

      $(
        () => {

          // Make member filter autosubmit
          var filtered = context == document
            ? $(
                '.view-collective-projects',
                context
              )
            : $(context).filter('.view-collective-projects')
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
                    1000
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
          $('.view-collective-projects', context)
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
        }
      )
    }
  }

})(jQuery)
