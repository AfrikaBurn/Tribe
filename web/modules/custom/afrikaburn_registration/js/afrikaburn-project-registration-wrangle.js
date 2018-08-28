/**
 * @file
 * AfrikaBurn wrangler form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistrationWrangle = {
    attach: function (context, settings) {
      $('.form-checkbox, .form-select, .form-text', context).not('.editor-processed').each(
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

      var
        nid = this.element.parents('tr').data('nid'),
        field = this.element.attr('name'),
        value = this.element.hasClass('form-checkbox')
          ? (this.element.prop('checked') ? 1 : 0)
          : this.element.val()

      $.ajax(
        {
          url: '/registration/update/' + nid,
          data: {
            field: field,
            value: value
          },
          success: (data, status) => {
            this.element.parent().removeClass('editor-busy')
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

