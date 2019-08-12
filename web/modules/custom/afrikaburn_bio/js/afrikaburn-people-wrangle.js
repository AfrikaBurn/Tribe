/**
 * @file
 * AfrikaBurn wrangler form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnWranglePeople = {
    attach: function (context, settings) {
      $('.editor-parent .form-email, .editor-parent .form-checkbox, .editor-parent .form-select, .editor-parent .form-text, .editor-parent .form-tel', context).not('.editor-processed').each(
        (i, element) => new editor(element)
      )
    }
  }


  class editor {

    constructor(element){
      this.element = $(element)
      this.element.addClass('editor-processed')
      this.element.hasClass('form-email')
        ? this.element.blur(() => this.update())
        : this.element.change(() => this.update())    }

    update(){
      this.element.parent().addClass('editor-busy')
      this.element.parent().removeClass('editor-error')

      var
        uid = this.element.parents('tr').data('uid'),
        field = this.element.attr('name'),
        value = this.element.hasClass('form-checkbox')
          ? (this.element.prop('checked') ? 1 : 0)
          : this.element.val()

      $.ajax(
        {
          url: '/people/update/' + uid,
          data: {
            field: field,
            value: value
          },
          success: (data, status) => {
            this.element.parent().removeClass('editor-busy')
            for(var selector in data){
              $(selector).html(data[selector])
            }
          },
          error: () => {
            this.element.parent().removeClass('editor-busy')
            this.element.parent().addClass('editor-error')
          }
        }
      )
    }
  }

})(jQuery)

