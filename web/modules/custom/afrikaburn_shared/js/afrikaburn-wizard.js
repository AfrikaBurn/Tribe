/**
 * @file
 * AfrikaBurn shared form behaviors.
 */

(function ($, toValidate) {

  'use strict'

  Drupal.behaviors.afrikaBurnWizard = {
    attach: function (context, settings) {

      // You're a wizard Harry
      $('.js-wizard:not(.js-wizard-processed)', context).each(
        function(){
          new Wizard(this).showFirstError();
        }
      );

    }
  }

  // Adds basic Next/Previous button behaviour to tabbed forms with the js-wizard class
  class Wizard {

    constructor(element){

      this.root = $(element)
      this.root.addClass('js-wizard-processed')
      this.tabs = $('.vertical-tabs__menu, .horizontal-tabs-list', this.root).children()
      this.panels = $('.field-group-tab > .details-wrapper', this.root)

      this.panels.each(
      	(index, panel) => $(panel).prepend(
      	  '<div class="pager">Step ' +
      	  (index + 1) +
      	  ' of ' +
      	  this.panels.length +
      	  '</div>'
  	  	)
  	  )

      this.root.parents('form').find('.captcha').appendTo(this.panels.last())
      this.panels.append('<div class="wizard-actions"></div>')
      this.root.parents('form').find('.form-actions').appendTo(this.panels.last().find('.wizard-actions'))
      this.attachPrevious()
      this.attachNext()
      this.attachSubmit()
    }

    // Attach the previous buttons and behaviours
    attachPrevious(){
      this.panels.not(':first').find('.wizard-actions').append('<input type="button" value="Previous" class="js-previous button" />')
      $('.js-previous', this.root).click(
        () => {
          this.getActiveTab().prev().find('a').click()
          this.scrollTo(this.root)
        }
      )
    }

    // Attach the next buttons and behaviours
    attachNext(){
      this.panels.not(':last').find('.wizard-actions').append('<input type="button" value="Next" class="js-next button" />')
      $('.js-next', this.root).click(
        () => {
          if (this.validate()) {
            this.getActiveTab().next().find('a').click()
            this.scrollTo(this.root)
          } else {
            this.scrollTo(this.getVisibleErrors()[0])
          }
        }
      )
    }

    // Hop to first error on submit attempt
    attachSubmit(){
      $('#edit-submit').click(
        () => this.showFirstError()
      )
    }

    // Validate the current tab
    validate(){
      var 
        activePanel = this.getActivePanel(),
        elementsToValidate = activePanel.find(toValidate).length
      if ($.fn.valid && elementsToValidate.length) elementsToValidate.valid()
      return this.getVisibleErrors().length < 1
    }

    // Go to first error
    showFirstError(){

      var
        firstErrorPanel = this.root.find('.error:visible').first().parents('.details-wrapper'),
        firstErrorTab = this.tabs[this.panels.index(firstErrorPanel)],
        link = $(firstErrorTab).find('a')

      setTimeout(() => link.click(), 100)
    }

    // Scroll to top of element
    scrollTo(element){
      var top = $(element).offset().top - parseInt($('body').css('margin-top')) - parseInt($('body').css('padding-top'))
      $('html, body').animate({ scrollTop: top - 30}, 500)
    }

    // Get the active tab
    getActiveTab(){
      return this.tabs.filter('.is-selected, .selected')
    }

    // Get the active panel
    getActivePanel(){
      return $(this.panels[this.getActiveTab().index()])
    }

    // Get all visible errors on the active panel
    getVisibleErrors(){
      return this.getActivePanel().find('.error:visible')
    }

  }

})(jQuery, '.form-email,.form-text,.form-tel,.form-autocomplete,.form-checkbox,.form-select,.form-textarea,.form-file,.form-number,.form-date')

