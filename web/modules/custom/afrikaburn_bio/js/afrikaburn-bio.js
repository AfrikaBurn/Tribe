/**
 * @file
 * AfrikaBurn shared form behaviors.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.afrikaburnBio = {
    attach: function (context, settings) {

      // Text size fixing
      $('*[size]').removeAttr('size')

      if (context == document) {

        function toInt(s){
          return Number(
            s.replace(/[a-zA_Z]+/, '')
          )
        }

        var
          $body = $('body'),
          $biobar = $('.biobar .sidebar'),
          $collectivebar = $('.collectivebar .sidebar'),
          $logo = $('.logo-container img'),
          $window = $(window)

        $(
          function(){

            var
              topPadding, logo, biobar, collectivebar, windowWidth,
              reset = {
                position: 'static',
                width: '100%'
              }

            $window.resize(
              function(){

                topPadding = toInt($body.css('padding-top'))
                windowWidth = $window.width()

                logo = {
                  top: $logo.offset().top,
                  left: $logo.offset().left,
                  width: $logo.width()
                }
                biobar = {
                  top: $biobar.offset().top,
                  width: $biobar.parent().width()
                    - toInt($biobar.css('padding-left'))
                    - toInt($biobar.css('padding-right'))
                    - toInt($biobar.css('border-left-width'))
                    - toInt($biobar.css('border-right-width'))
                },
                collectivebar = $collectivebar.length ? {
                  top: $collectivebar.offset().top,
                  width: $collectivebar.width(),
                  marginTop: Number(
                    $collectivebar.css('margin-top').replace(/[a-zA_Z]+/, '')
                  )
                } : false

                logo.threshold = logo.top - topPadding
                biobar.threshold = biobar.top - topPadding
                if (collectivebar) collectivebar.threshold =
                  collectivebar.top
                  - topPadding
                  - collectivebar.marginTop
              }
            )
            $window.resize()

            $window.scroll(
              function(){

                if (windowWidth > 960){

                  if ($window.scrollTop() > logo.threshold) {
                    $logo.css(
                      {
                        position: 'fixed',
                        top: topPadding + 'px',
                        left: logo.left + 'px',
                        width: logo.width + 'px'
                      }
                    )
                  } else {
                    $logo.css(reset)
                  }

                  if ($window.scrollTop() > biobar.threshold - 200) {
                    $biobar.css(
                      {
                        position: 'fixed',
                        top: topPadding + 200 + 'px',
                        width: biobar.width + 'px',
                        zIndex: 1000
                      }
                    )
                  } else {
                    $biobar.css(reset)
                  }

                  if (collectivebar && $window.scrollTop() > collectivebar.threshold) {
                    $collectivebar.css(
                      {
                        position: 'fixed',
                        top: topPadding + 'px',
                        width: collectivebar.width + 'px'
                      }
                    )
                  } else {
                    $collectivebar.css(reset)
                  }

                } else {
                  $logo.css(reset);
                  $biobar.css(reset);
                  $collectivebar.css(reset);
                }

              }
            )
          }
        )
      }
    }
  }

})(jQuery)
