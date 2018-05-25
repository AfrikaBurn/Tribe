jQuery(window).scroll(function() {
  var scrollHeight = jQuery(window).scrollTop();
  if (scrollHeight > 0) {
    jQuery("a.back-to-top-button").addClass("show")
  } else {
    jQuery("a.back-to-top-button").removeClass("show")
  }
});

/*----------------------------------------------------
ADD CLASS "HIGHLIGHTED" TO THE ACTIVE PARENT MENU ITEM
----------------------------------------------------*/

jQuery("ul.nav-child").hover(parentColour, parentColour);

function parentColour() {
  jQuery(this).parent().toggleClass("highlighted");
}

function supportsPlaceholder() {
  return 'placeholder' in document.createElement('input');
}

function radioCheckBoxWrapper() {
  jQuery("input:radio, input:checkbox")
  jQuery("input:radio, input:checkbox").not(".radio-checkbox-wrapper input:radio, .radio-checkbox-wrapper input:checkbox").wrap("<div class='radio-checkbox-wrapper'></div>").after("<div class='radio-checkbox-dummy'></div>");
}

function inputTypeFile() {
  var selectFileInputs = jQuery("input:file").not(".input-file-wrapper input:file");
  jQuery.each(selectFileInputs, function(i, selectFileInput) {
    var inputText = jQuery(selectFileInput).attr("multiple") == "multiple" ? "Select Files" : "Select File";
    jQuery(selectFileInput).wrap("<div class='input-file-wrapper'>" + inputText + "</div>").parent().after("<div class='clr'></div><ul class='selected-files-list'></ul>");
  })
}

function addIconToStatusMessage() {
  jQuery("div.messages").prepend("<span class='icon'></span>");
}

function rotateImages() {
  var oCurrentPhoto = jQuery("div.slide.current");
  var oNextPhoto = oCurrentPhoto.next("div.slide");
  if (oNextPhoto.length == 0)
    oNextPhoto = jQuery("div.slide:first");

  oCurrentPhoto.removeClass("current").addClass("previous");
  oNextPhoto.css({
      opacity: 0.0
    }).addClass("current")
    .animate({
        opacity: 1.0
      }, 2000,
      function() {
        oCurrentPhoto.removeClass("previous");
      });
}

function hideCollectivesBlock() {
  jQuery("#my-collectives-block").removeClass("show");
  jQuery(".my-collectives").removeClass("active");
}

function hideUserMenuBlock() {
  jQuery(".user-menu").removeClass("show");
  jQuery(".user-menu-icon").removeClass("active");
}

function combineFormHeadersAndFields() {
  var formFieldHeaders = jQuery('.js-form-type-webform-markup p.western');
  jQuery.each(formFieldHeaders, function(index, item) {
    if (!jQuery(this).hasClass('hidden')) {
      jQuery(this).addClass('hidden');
      var headerText = jQuery(this).text();
      var fieldText = jQuery(this).parent().next('fieldset').find('legend span').text();
      jQuery(this).parent().next('fieldset').find('legend span').text(headerText + ' ' + fieldText);
    }
  });
}

// ZZZ - Used on the community pages on old site. May need this again. Allows the "contact this group" form to be hidden initially.
// function toggleContactForm(clickedElement) {
//     var contactForm = clickedElement.next();
//     if (contactForm.hasClass('hidden')) {
//         contactForm.slideDown("slow", function() {
//             contactForm.toggleClass('hidden');
//             var scrollBarPosition = clickedElement.offset().top - 15;
//             jQuery('html,body').animate({
//                 scrollTop: scrollBarPosition
//             }, 300);
//         });
//     } else {
//         contactForm.slideUp("slow", function() {
//             contactForm.toggleClass('hidden');
//         });
//     }
// }

var wrapperClass = (window.location.pathname).replace(/\//g, "-");
jQuery("#wrapper").addClass(wrapperClass.slice(1));

jQuery(document).ready(function() {
  // Initiate functions that insert/ wrap html elements to help with tricky styling.
  //radioCheckBoxWrapper();
  inputTypeFile();
  addIconToStatusMessage();
  // combineFormHeadersAndFields();
  // Display file name underneath input type file element when file is selected.
  jQuery("input:file").change(function() {
    var self = this;
    var selectedFiles = jQuery(this)[0].files;
    var headerText = (jQuery(this)[0].files).length > 1 ? "Selected Files:" : "Selected file:";
    jQuery(self).parent().siblings("ul.selected-files-list").append("<li class='header'>" + headerText + "</li>");
    jQuery.each(selectedFiles, function(i, file) {
      jQuery(self).parent().siblings("ul.selected-files-list").append("<li>" + file.name + "</li>");
    })
    jQuery(this).parent().addClass('file-selected');
  });

  // Implement the back to top button functionality.
  jQuery("a.back-to-top-button").attr("href", "javascript: void(0)");
  jQuery("a.back-to-top-button").click(function(event) {
    event.preventDefault();
    jQuery("html, body").animate({
      "scrollTop": "0px"
    }, 100);
  });

  // Initiate the image rotation function.
  jQuery(function() {
    setInterval("rotateImages()", 5000);
  });

  //Toggle the user menu box top right for logged in users.
  jQuery(".user-menu-icon").click(function(e) {
    jQuery(".user-menu").toggleClass("show");
    jQuery(this).toggleClass("active");
    hideCollectivesBlock();
    e.stopPropagation();
  });

  // Add custom classes to each link in the user menu, to allow different icons for each via CSS.
  jQuery(".user-menu ul li a").each(function(e) {
    var href = jQuery(this).attr('href').split('/');
    var slug = href[href.length - 1];
    jQuery(this).addClass(slug);
  })

  jQuery(document).click(function(e) {
    hideUserMenuBlock();
    hideCollectivesBlock();
  });

  jQuery(".user-menu").click(function(e) {
    e.stopPropagation();
  });
   jQuery(".my-collectives").click(function(e) {
    e.stopPropagation();
  });

  var myCollectivesRightPosition = jQuery(".user-greeting").outerWidth() + 15;
  jQuery("#my-collectives-block").css('right', myCollectivesRightPosition);
  jQuery(".my-collectives").attr("href", "javascript: void(0)");
  //Toggle the user menu box top right for logged in users.
  jQuery(".my-collectives").click(function(e) {
    jQuery("#my-collectives-block").toggleClass("show");
    jQuery(this).toggleClass("active");
    hideUserMenuBlock();
    e.stopPropagation();
  });

  jQuery('form#user-login-form').after("<a href='/user/password?'>Reset your Password</a>");

  // Add placeholder text to inputs that initiate a drop down list.
  jQuery(".js input.form-autocomplete").attr('placeholder', 'Type to search');

  // Toggle the mobile menu.
  jQuery("a.btn-navbar").click(function(e) {
    jQuery(this).parent().parent().toggleClass('open');
  })

  jQuery(".node-art-grant-form").closest("body").addClass("node-add-art-grant");
  jQuery(".node-performances-grant-form").closest("body").addClass("node-add-performances-grant");

  jQuery(".collective-row").parent().addClass("collectives-container");

  var numberOfCollectives = jQuery("#my-collectives-block .view-collectives .collective-row").length;
  if (numberOfCollectives > 2) {numberOfCollectives = '3x'}
  jQuery("body").addClass("collectives-length-" + numberOfCollectives);

  // HACKS
  // Force the "no-sidebars class if the sidebar is empty."
  if (jQuery.trim( jQuery('div.sidebar').html() ).length === 0) {
    jQuery('body').addClass('no-sidebars');
  }

  var problemElement = jQuery('#edit-field-prj-stc-physical-value').closest('.field--name-field-prj-stc-physical').next('fieldset');
  problemElement.addClass('hidden');
  jQuery('#edit-field-prj-stc-physical-value').change(function() {

    if(this.checked) {
       problemElement.removeClass('hidden');
    } else {
      problemElement.addClass('hidden');
    }
});


});

// Run various functions above when AJAX operations complete.
jQuery(document).ajaxComplete(function() {
  //radioCheckBoxWrapper();
  inputTypeFile();
  jQuery(".collective-row").parent().addClass("collectives-container");
  // combineFormHeadersAndFields();
});