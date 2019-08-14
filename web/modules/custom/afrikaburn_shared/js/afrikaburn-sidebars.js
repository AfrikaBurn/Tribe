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
              if ($('.biobar .sidebar').length) new floating($('.biobar .sidebar'), 200)
              if ($('.collectivebar .sidebar').length) new floating($('.collectivebar .sidebar'), -40)
              if ($('.logo-container img').length) new floating($('.logo-container img'), 0)
            }
          },
          10
        )
      )
    }
  }


  function toInt(s){
    return Number(
      s.replace(/[a-zA_Z]+/, '')
    )
  }


  class floating{

    constructor($element, offset){

      this.$element = $element
      this.offset = offset

      floating.$window.resize(
        () => this.resize()
      )
      this.resize()

      floating.$window.scroll(
        () => this.scroll()
      )
    }

    resize(){
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
      this.reset = {
        position: this.$element.css('position'),
        top: this.$element.css('top'),
        left: this.$element.css('left')
      }
      this.window = {
        topPadding: toInt(floating.$body.css('padding-top')),
        width: floating.$window.width()
      }
      this.threshold =
        this.props.top
        - this.window.topPadding
        - this.props.marginTop
    }

    scroll(){
      if (
        this.window.width > 960 &&
        floating.$window.scrollTop() > this.threshold - this.offset
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
      } else this.$element.css(Object.assign(floating.reset, this.reset))
    }
  }

  floating.$window = $(window)
  floating.$body = $('body')
  floating.reset = {
    position: 'static',
    width: '100%'
  }

})(jQuery)
