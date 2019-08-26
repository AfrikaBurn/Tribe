/**
 * @file
 * AfrikaBurn Sidebar behaviours.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnSidebars = {
    attach: function (context, settings) {
      $(
        () => setTimeout(
          () => {
            if (context == document) {

              var
                $biobar = $('.biobar .sidebar').not('.js-floating'),
                $sidebar = $('.collectivebar .sidebar').not('.js-floating'),
                $logo = $('.logo-container img').not('.js-floating')

              if ($biobar.length) new Floating($biobar, 200)
              if ($sidebar.length) new Floating($sidebar, -40)
              if ($logo.length) new Floating($logo, 0)
            }
          },
          10
        )
      )

      // Collapsability
      if (context == document){
        var expanded = $.cookie('expanded') || ''
        $('.sidebar .block, .region-header .block')
          .not('#block-ticketblock, #block-adminimal-theme-page-title')
          .addClass('collapsible')
          .not(expanded)
          .addClass('collapsed')
          .children('.content, ul.menu')
          .hide()

        $('.collapsible h2').click(
          function(){
            var
              $head = $(this),
              $block = $head.parent(),
              $body = $block.children('.content, ul.menu'),
              $siblings = $block.siblings('.collapsible')

            if ($block.hasClass('collapsed')){
              $siblings.addClass('collapsed').children('.content').slideUp()
              $body.slideDown()
              $block.removeClass('collapsed')
            } else {
              $body.slideUp()
              $block.addClass('collapsed')
            }
          }
        )
      }
    }
  }


  /* ------ Collapsing sidebars ------ */


  class Collapsible{
    constructor($sidebar){

      $('.block', $element).each(
        ($block) => new Collapsing($block, $sidebar)
      )

      $sidebar.bind('toggle-block',
        () => {}
      )
    }
  }

  class Collapsing{
    constructor($block, $sidebar){
      $()
    }
  }


  /* ------ Floating sidebars ------ */


  class Floating{

    constructor($element, offset){

      this.$element = $element.addClass('js-floating')
      this.offset = offset

      Floating.$window.resize(
        () => {
          this.reset()
          this.scroll()
        }
      )
      this.reset()

      Floating.$window.scroll(
        () => this.scroll()
      )
    }

    reset(){

      this.$element.css(
        {
          position: '',
          top: '',
          left: '',
          width: '',
          zIndex: ''
        }
      )

      this.props = {
        top: this.$element.offset().top,
        left: this.$element.offset().left,
        width: this.$element.parent().width()
        - toInt(this.$element.css('padding-left'))
        - toInt(this.$element.css('padding-right'))
        - toInt(this.$element.css('border-left-width'))
        - toInt(this.$element.css('border-right-width')),
        marginTop: toInt(
          this.$element.css('margin-top').replace(/[a-zA_Z]+/, '')
        )
      }
      this.window = {
        topPadding: toInt(Floating.$body.css('padding-top')),
        width: Floating.$window.width()
      }
      this.threshold =
        this.props.top
        - this.window.topPadding
        - this.props.marginTop
    }

    scroll(){
      if (
        this.window.width > 960 &&
        Floating.$window.scrollTop() > this.threshold - this.offset
      ) {
        this.$element.css(
          {
            position: 'fixed',
            top: (this.window.topPadding + this.offset) + 'px',
            left: this.props.left + 'px',
            width: this.props.width + 'px',
            zIndex: 1,
          }
        )
      } else this.$element.css(Object.assign(Floating.reset, this.reset))
    }
  }

  Floating.$window = $(window)
  Floating.$body = $('body')
  Floating.reset = {
    position: 'static',
    width: '100%'
  }


  /* ------ Utility ------ */


  function toInt(s){
    return Number(
      s.replace(/[a-zA_Z]+/, '')
    )
  }

})(jQuery)
