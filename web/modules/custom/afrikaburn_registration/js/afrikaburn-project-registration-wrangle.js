/**
 * @file
 * AfrikaBurn wrangler form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnRegistrationWrangle = {
    attach: function (context, settings) {
      $('tbody .form-checkbox, tbody .form-select, tbody .form-text, tbody .form-textarea, tbody .form-number', context).not('.editor-processed').each(
        (i, element) => new editor(element)
      )

      $('td.views-field-field-prj-wtf-short-copy').not('.wrangle-processed').each(
        function() {
          $(this).prepend('<button class="copy-description">Copy &gt; &gt;</button>')
        }
      )

      $('.copy-description').click(
        function(){
          var
            copyTo = $(this).parent().find('textarea'),
            copyFrom = $(this).parent().siblings('.views-field-field-prj-wtf-short')

          copyTo.val(copyFrom.text().trim()).focus()
        }
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

