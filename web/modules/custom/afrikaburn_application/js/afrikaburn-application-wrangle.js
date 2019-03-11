/**
 * @file
 * AfrikaBurn wrangler form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistration = {
    attach: function (context, settings) {
      $('tbody .form-checkbox, tbody .form-select, tbody .form-text, tbody .form-textarea', context).not('.editor-processed').each(
        (i, element) => new editor(element)
      )
    }
  }


  class editor {

    constructor(element){
      this.element = $(element)
      this.element.addClass('editor-processed')
      this.element.parent().addClass('editor-parent')
      this.element.change(() => this.update())
    }

    update(){
      this.element.parent().addClass('editor-busy')
      this.element.parent().removeClass('editor-error')
      this.element.parent().removeClass('editor-success')

      var
        sid = this.element.parents('tr').data('sid'),
        field = this.element.attr('name'),
        value = this.element.hasClass('form-checkbox')
          ? (this.element.prop('checked') ? 1 : 0)
          : this.element.val()

      $.ajax(
        {
          url: '/application/update/' + sid,
          data: {
            field: field,
            value: value
          },
          success: (data, status) => {
            this.element.parent().removeClass('editor-busy')
            setTimeout(
              () => this.element.parent().removeClass('editor-success'),
              5000
            )
            this.element.parent().addClass('editor-success')
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

