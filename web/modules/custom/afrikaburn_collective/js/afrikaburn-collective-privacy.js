/**
 * @file
 * AfrikaBurn collective behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnCollectivePrivacy = {
    attach: function (context, settings) {

      $(
        function(){

          $('#edit-field-settings').prefix(
            '<button>Open</button>'
          )

          $('#edit-field-members .js-form-item input').change(
            function(){
              var $this = $(this)

              $this.prop('checked')
                ? $('#edit-field-admins [value=' + $this.attr('value') + ']')
                  .attr('disabled', 'disabled')
                : $('#edit-field-admins [value=' + $this.attr('value') + ']')
                  .removeAttr('disabled')

            }
          )

          $('.js-form-item input').change()
        }
      )
    }
  }
})(jQuery)