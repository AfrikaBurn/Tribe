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
                $logo = $('.logo-container img').not('.js-floating'),
                $wrangleBlock = $('.region-header .menu--wrangle')

              if ($biobar.length) {
                new Floater($biobar, 200)
                new Collapsible($biobar, '#block-ticketblock')
              }
              if ($sidebar.length) {
                new Floater($sidebar, -40)
                new Collapsible($sidebar)
              }

              if ($biobar.length && $logo.length) new Floater($logo, 0)
              if ($wrangleBlock.length) new Collapsing($wrangleBlock)
            }
          },
          10
        )
      )
    }
  }

  $browserWindow = $(window)
  $browserBody = $('body')


  /* ------ Collapsing sidebar blocks ------ */


  class Collapsible{
    constructor($sidebar, exclude){
      $('.block', $sidebar).not(exclude).each(
        (index, block) => new Collapsing($(block), $sidebar)
      )
    }
  }

  class Collapsing{

    constructor($block, $sidebar, exclude){

      this.cookieKey = $sidebar.parent().attr('class')
      this.$sidebar = $sidebar
      this.$block = $block.addClass('collapsible')
      this.$title = $block.children('h2')
      this.$body = $block.children('.content, ul.menu')
      this.$siblings = $block.siblings()
      this.exclude = exclude

      if ($block.attr('id') != $.cookie(this.cookieKey)) this.collapse()

      this.$title.click(() => this.toggle())
      this.$block.bind('collapse', () => this.collapse())
      $browserWindow.resize(() => this.resizeBody())
      $browserWindow.scroll(() => this.resizeBody())
      setTimeout(() => this.resizeBody(), 500)
    }

    toggle(){
      this.$block.hasClass('collapsed') ? this.expand() : this.collapse()
    }

    collapse(){
      this.$body.slideUp().css({height: ''}).css('overflow-y', '')
      this.$block.addClass('collapsed')
      if (this.$block.attr('id') == $.cookie(this.cookieKey))
        $.cookie(this.cookieKey, null)
    }

    resizeBody(){
      if (this.$sidebar) {
        var
          sidebarHeight = this.$sidebar.outerHeight(),
          sidebarTop = toInt(this.$sidebar.css('top')) + toInt(this.$sidebar.css('margin-top')),
          windowHeight = $browserWindow.height(),
          siblingHeight = this.$siblings.toArray().reduce(
            (total, element) => total + $(element).outerHeight(), 0
          ),
          topPadding = toInt($browserBody.css('padding-top')),
          minWidth = $browserWindow.width() > 960,
          minHeight = $browserWindow.height() > 650

        if (
          minWidth && minHeight &&
          this.$sidebar.hasClass('js-floating') &&
          sidebarHeight + sidebarTop > windowHeight - topPadding
        ){
          this.$body.hasClass('js-scrolling')
          this.$body.height(windowHeight - sidebarTop - siblingHeight - topPadding).css('overflow-y', 'auto')
        } else {
          this.$body.css('height', '').css('overflow-y', '')
        }
      }
    }

    expand(){

      this.$body.slideDown(
        () => this.resizeBody()
      )

      this.$block.removeClass('collapsed')
      $.cookie(this.cookieKey, this.$block.attr('id'))
      this.$siblings.not(this.exclude).trigger('collapse')
    }
  }


  /* ------ Floater sidebars ------ */


  class Floater{

    constructor($element, offset){

      this.$element = $element.addClass('js-floater')
      this.offset = offset

      $browserWindow.resize(
        () => {
          this.reset()
          this.scroll()
        }
      )
      this.reset()
      this.scroll()

      $browserWindow.scroll(
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
        topPadding: toInt($browserBody.css('padding-top')),
        width: $browserWindow.width()
      }
      this.threshold =
        this.props.top
        - this.window.topPadding
        - this.props.marginTop
    }

    scroll(){

      var
        minWidth = $browserWindow.width() > 960,
        minHeight = $browserWindow.height() > 650

      if (
        minWidth && minHeight &&
        $browserWindow.scrollTop() > this.threshold - this.offset
      ) {
        this.$element.css(
          {
            position: 'fixed',
            top: (this.window.topPadding + this.offset) + 'px',
            left: this.props.left + 'px',
            width: this.props.width + 'px',
            zIndex: 1,
          }
        ).addClass('js-floating')
      } else {
        this.reset()
        this.$element.removeClass('js-floating')
      }
    }
  }


  /* ------ Utility ------ */


  function toInt(s){
    return Number(
      s.replace(/[a-zA_Z]+/, '')
    )
  }

})(jQuery)
