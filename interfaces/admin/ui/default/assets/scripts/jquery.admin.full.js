/**
* JavaScript behaviors for the CASH admin
*
* @package platform.org.cashmusic
* @author CASH Music
* @link http://cashmusic.org/
*
* Copyright (c) 2016, CASH Music
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without modification,
* are permitted provided that the following conditions are met:
*
* Redistributions of source code must retain the above copyright notice, this list
* of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this
* list of conditions and the following disclaimer in the documentation and/or other
* materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
* ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
* IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
* INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
* NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
* OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
* LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
* OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
* OF THE POSSIBILITY OF SUCH DAMAGE.
*
**/

// insertAtCaret plugin for textarea stuff / injecting codes on page editor
jQuery.fn.extend({
   insertAtCaret: function(myValue){
      return this.each(function(i) {
         if (document.selection) {
            //For browsers like Internet Explorer
            this.focus();
            var sel = document.selection.createRange();
            sel.text = myValue;
            this.focus();
         } else if (this.selectionStart || this.selectionStart == '0') {
            //For browsers like Firefox and Webkit based
            var startPos = this.selectionStart;
            var endPos = this.selectionEnd;
            var scrollTop = this.scrollTop;
            this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
            this.focus();
            this.selectionStart = startPos + myValue.length;
            this.selectionEnd = startPos + myValue.length;
            this.scrollTop = scrollTop;
         } else {
            this.value += myValue;
            this.focus();
         }
      });
   }
});

(function($) {
   /**
   *
   *
   *
   * INITIAL LOAD: SET IT ALL IN MOTION
   *
   *
   *
   **/
   $(document).ready(function() {
      setUIBehaviors();
      setContentBehaviors();

      // safe to call. already loaded before admin
      FastClick.attach(document.body);

      window.globaltimeout = false;

      history.pushState(1, null, location.pathname);
      window.addEventListener('popstate', function(e) {
         if (e.state) {
            refreshPageData(location.pathname,null,null,null,true);
         }
      }, false);

   }); // $document


   /**
   *
   *
   *
   * PAGE REDRAW AND AJAX REQUESTS
   *
   *
   *
   **/

   /**
   * redrawPage (function)
   *
   * handle per-element specific redraws for each request
   *
   */
   function redrawPage(data) {
      // change the color
      if ($('#page').hasClass('panel')){
          $('#mainspc, #pagetitle, #page').removeClass (function (index, css) {
            return (css.match (/(^|\s)usecolor\S+/g) || []).join(' ');
          });
          $('#mainspc, #page').addClass(data.specialcolor);
      } else {
        $('#mainspc, #pagetitle, #page').removeClass();
          $('#mainspc, #page').addClass(data.specialcolor);
      }

      // nav
      redrawMainNav(data.section_name);

      // the rest
      $('#pagemessage').html('');
      if (data.error_message) {
         doMessage(data.error_message,'Error',true);
      }
      if (data.page_message) {
         doMessage(data.page_message,'');
      }
      //$('#pagetips').hide();
      if (data.ui_page_tip != '') {
         $('#learn_tip').html(data.ui_page_tip);
         $('#learn_tip').css('display','block');
      } else {
         $('#learn_tip').css('display','none');
      }
      $('.panelcontent').html(data.ui_learn_text);
      $('#page_content').html(data.content);
      $('#pagetitle span').html(data.ui_title);

      window.scrollTo(0,0);
      $(document).trigger('redraw');
   }

   /*
   * doPersistentPost(url,formdata,showerror,showmessage,skiphistory)
   *
   * When we made the move to hosted there were a lot of issues with null returns
   * — was never able to duplicate it locally, so it probably has something to do
   * with the server config, latency, or load balancing.
   *
   * This function pulls out a lot of what was in refreshPageData and allows us to
   * check for null success returns and try again. Loop potential like you read
   * about...
   */
   function doPersistentPost(url,formdata,showerror,showmessage,skiphistory) {
      // do a POST to get the page data, change pushstate, redraw page
      $.ajax({
         type: "POST",
         url: url,
         data: formdata+'data_only=1',
         success: function(data) {
            if (!data) {
               doPersistentPost(url,formdata,showerror,showmessage,skiphistory);
            } else {
               if (data.initiallogin) {
                  //console.log('login');
                  $('body').removeClass('login');
                  $('#loadingmask').css('width','1%');
               }
               if (data.template_name)	{
                  if (data.template_name.toLowerCase().indexOf('login') >= 0) {
                     $('body').addClass('login');
                     history.pushState(1, null, cashAdminPath + '/');
                  }
               }
               if (!("doredirect" in data)){ data.doredirect = false; }
               if (data.doredirect) {
                  if (data.showerror) {
                     refreshPageData(data.location,false,data.showerror);
                  } else if (data.showmessage) {
                     refreshPageData(data.location,false,false,data.showmessage);
                  } else {
                     refreshPageData(data.location);
                  }
               } else {
                  if (showerror) { data.error_message = showerror; }
                  if (showmessage) { data.page_message = showmessage; }
                  redrawPage(data);
                  if (!skiphistory) {history.pushState(1, null, url);}
                  setContentBehaviors();
               }

               $('#page_content, #ajaxloading, #logo, #hero, #learnpanel, #settingspanel, #helppanel').removeClass('loading');
            }
         },
         error: function(obj,status,errorThrown) {
            console.log(status + ': ' + errorThrown);
         },
         dataType: 'json'
      });
   }

   /**
   * refreshPageData (function)
   *
   * handles the data request for each page load, manipulates history,
   * and decides redraw method (full redraw or redrawPage)
   *
   */
   function refreshPageData(url,formdata,showerror,showmessage,skiphistory) {
      if (!formdata) {
         formdata = '';
      } else {
         formdata = formdata+'&';
      }

      // remove any dialogs
      $('.modallightbox').fadeOut('fast', function() {
         $('.modallightbox').remove();
      });
      $('.modalbg').fadeOut('fast', function() {
         $('.modalbg').remove();
      });

      // close panel * if settings open *
      if ($("body").hasClass('settings')){
         closePanel();
      }

      $('#page_content, #ajaxloading, #logo, #hero, #learnpanel, #settingspanel, #helppanel').addClass('loading');
      doPersistentPost(url,formdata,showerror,showmessage,skiphistory);
   }

   /**
   *
   *
   *
   * MAIN UI ELEMENT BEHAVIORS
   *
   *
   *
   **/

   /**
   * setContentBehaviors (function)
   * miscellaneous behaviors for various things — needs to run each AJAX page load
   *
   */
   function setContentBehaviors() {
      // close tertiary panel *if settings open*

      if ($("body").hasClass('settings')){
        closePanel();
      };

      // show/hide drawers
      prepDrawers('<div class="icon icon-arw-up"></div><!--icon-->Hide','<div class="icon icon-arw-dwn"></div><!--icon-->Show');

      // deal with scalar form fields
      prepScalars();

      // datepicker
      $('input[type=date],input.date').datepicker();

      formValidateBehavior();
      venueAutocompleteBehavior();
      handleUploadForms();
      glitch();
      ZclipBoard();
      handleSwitchBlocks();

      // should we clear the form persistence stuff?
      // remove all localstorage form persistence junk
      if (localStorage.getItem('resetadminforms') && !$('body').hasClass('login')) {
         clearPersistentFormData();
      }

      $('form').garlic();

      // Animate graph load
      //$('.graph').delay(8000).addClass('loaded');


      setTimeout(function(){
          $('.graph').addClass("loaded");
      }, 100);
   }

   /**
   * setUIBehaviors (function)
   *
   * The main UI behaviors — only needs to be run on the first page load, not on
   * each AJAX load-in, bind all events with on to document to preserve cross-load
   *
   */
   function setUIBehaviors() {
      // vital/complex behavior
      ajaxPageBehaviors();
      assetFormBehaviors();
      modalBehaviors();
      textareaTabBehavior();
      listenForModals();
      listenForScalars();
      moveToExample();
      prepItemVariants();

      // page tip show/hide
      $(document).on('click', '#tipslink', function(e) {
         e.preventDefault();
         $('#pagetips').slideDown(200);
      });
      $(document).on('click', '#tipscloselink', function(e) {
         e.preventDefault();
         $('#pagetips').slideUp(100);
      });

      // Mobile Swipe // Bind the Swipe Handler callback function to the swipe event on page
      $(document).on('swipeleft', '#page', function(e) {
         $('body').removeClass('swiperight');
         if (!$('body').hasClass('swipeleft') && !$('body').hasClass('swiperight')){
            $('body').addClass("swipeleft");
         }
      });
      $(document).on('swiperight', '#page', function(e) {
         $('body').removeClass('swipeleft');
         if (!$('body').hasClass('swipeleft') && !$('body').hasClass('swiperight')){
            $('body').addClass('swiperight');
         }
      });

      // show/hide mainmenu
      $(document).on('click', '#menutoggle', function(e) {
         $('#menutoggle').toggleClass('display');
         $('#navmenu').toggleClass('display');
      });

      // show/hide search
      $(document).on('click', '#searchbtn', function(e) {
         $('#searchbtn').toggleClass('display');
         $('#search').toggleClass('display');
      });

      /*
      * THIS WILL BE NEEDED SOON - TODO - ADD BACK WHEN SOCIAL FEEDS ELEMENT IS DONE
      *
      */
      //injecting dynamic code (wait for it)
      $(document).on('click','a.injectcode',function(e) {
         e.preventDefault();
         if ($('#template')) {
            $('#template').insertAtCaret('{{{element_' + $(this).data('elementid') + '}}}');
         }
      });

      // hide mainmenu & tertiary panel
      $(document).on('click', '#flipback', function(e) {
         $('#flipback').parent().removeClass('display');
         closePanel();
      });

      // when we need a submit button outside it's target form (see file assets, etc)
      $(document).on('click', 'input.externalsubmit', function(e) {
         $($(this).data('cash-target-form')).submit();
      });

      $(document).on('change','#current-campaign',function(event) {
         $(this).closest('form').submit();
      });

      $(document).on('change','#current-published-campaign',function(event) {
         //$(this).closest('form').submit();
         var tmplt = $(this).find(':selected').data('template');
         if (!tmplt) {
            doMessage('','Before you can publish this campaign, you need to set its page theme. Open the campaign and click the edit icon to start.',true);
         } else {
            doMessage('','This will change your public page. Are you sure?',true,$(this).find(':selected').data('path'));
         }
      });

      // element embed highlight-and-copy code
      $(document).on('click', '.codearea', function(e) {
         element = this;
         if (document.body.createTextRange) {
            var range = document.body.createTextRange();
            range.moveToElementText(element);
            range.select();
         } else if (window.getSelection) {
            var selection = window.getSelection();
            var range = document.createRange();
            range.selectNodeContents(element);
            selection.removeAllRanges();
            selection.addRange(range);
         }
      });

      $(document).on('click', '.multipart-next', function (e) {
         e.preventDefault();

         var forcestop = false;
         $($(mpForm.form).children('.part-'+mpForm.section)[0]).find('input,select,textarea').each(function() { // replace this with a hunt for specific children?
            if (!validator.element($(this))) {
               forcestop = true;
               return false;
            }
         });

         if (!forcestop) {
            $(mpForm.form.children('.part-'+mpForm.section)[0]).hide();
            mpForm.section = mpForm.section+1;
            if (mpForm.section > mpForm.total) {
               $($(mpForm.form).children('.section.basic-information')[0]).fadeIn();
               $(mpForm.steps).text(
                  'Finalize: ' + $($(mpForm.form).children('.section.basic-information')[0]).data('section-name')
               );
               $(mpForm.submit).show();
            } else {
               $($(mpForm.form).children('.part-'+mpForm.section)[0]).fadeIn();
               $(mpForm.steps).text(
                  'Step ' + mpForm.section + ' of ' + mpForm.total + ': ' + $($(mpForm.form).children('.part-'+mpForm.section)[0]).data('section-name')
               );
            }
         }
      });

      $(document).on('click', '.multipart-prev', function (e) {
         e.preventDefault();
         $(mpForm.form.children('.part-'+mpForm.section)[0]).hide();
         mpForm.section = mpForm.section-1;
         $($(mpForm.form).children('.part-'+mpForm.section)[0]).fadeIn();
         $(mpForm.steps).text(
            'Step ' + mpForm.section + ' of ' + mpForm.total + ': ' + $($(mpForm.form).children('.part-'+mpForm.section)[0]).data('section-name')
         );
      });

      $(document).on('click', '.store a[href^="' + cashAdminPath + '/elements/add"]', function (e) {
         e.preventDefault();
         e.stopPropagation();
         jQuery.post(this.href,'data_only=1', function(data) {
            $('div.modallightbox').html(
               '<h4>' + data.ui_title + '</h4>' +
               data.content + //jQuery.param(data) +
               '<div class="tar" style="position:relative;z-index:9876;"><a href="#" class="modalcancel smalltext"><div class="icon icon-plus"></div><!--icon--></a></div>'
            );
            $('.store .modallightbox h4').css('width','62%');

            $(document).bind('scroll',handleModalScroll);
            handleMultipartForms();
            formValidateBehavior();
         },'json')
      });

      $(document).on('click', '.revealpassword' ,function(e){
         e.preventDefault();
         var p = $(e.target).prev('input[type="password"]').attr('value');

         $(e.target).parent().children('.needsreveal').each(function() {
            var value = $(this).prop('value');

            if($(this).prop('type') == 'password') {
               $(this).attr('type','text');
            } else {
               $(this).attr('type','password');
            }

            $(this).attr('value',value);
         });
      });

      // featured asset cover flip stuff
      $(document).on('mouseenter', '.featured-release' ,function(e){
         $('#card',this).addClass('flipped');
      });
      $(document).on('mouseleave', '.featured-release' ,function(e){
         $('#card',this).removeClass('flipped');
      });

      // toggle element/list menus
      $(document).on('click', '.toggle' ,function(e){
         $(this).parent().toggleClass('display');
      });

      // tertiary panel
      $(document).on('click', '.paneltitle', function (e) {
         closePanel();
      });

      /* Settings Panel */
      $(document).on('click', '#settings.toggle, #settingspanel .toggle, .settings.toggle', function (e) {
         $('body').removeClass('help').removeClass('learn');
         $ (this).parents('body').addClass('panel').addClass('settings');
         $('#settingspanel .tertiarynav li a').removeClass('current');
         refreshPanelData(cashAdminPath + '/account/');
         $('#settingspanel .tertiarynav li a:first').addClass('current');
      });
      /* Help Panel */
      $(document).on('click', '#help.toggle, #helppanel .toggle', function (e) {
         $('body').removeClass('settings').removeClass('learn');
         $ (this).parents('body').addClass('panel').addClass('help');
         $('#helppanel .tertiarynav li a').removeClass('current');
         $('#helppanel .tertiarynav li a:first').addClass('current');
      });
      /* Help FAQs Panel */
      $(document).on('click', '#helppanel .tertiarynav li .faqs', function(e) {
         $('body').removeClass('settings').removeClass('learn');
         $('#helppanel .tertiarynav li a').removeClass('current');
         refreshPanelData(cashAdminPath + '/help/');
         $(this).addClass('current');
      });
      /* Help - FAQ Clicks */
      $(document).on('click', '#helppanel .faq', function(e) {
         $('#helppanel .tertiarynav li .faqs').addClass('current');
      });
      /* Help - Learn Content */
      $(document).on('click', '#helppanel .tertiarynav li .learn', function(e) {
         $ (this).parents('body').addClass('learn')
         $('#helppanel .tertiarynav li a').removeClass('current');
         refreshPanelData();
         $(this).addClass('current');
      });


      // swipe hint hide on click
      $(document).on('click', '.swipehint', function (e) {
         $(this).addClass('hide');
      });
   }

   // clear stored form data
   function clearPersistentFormData() {
      var i = localStorage.length;
      while(i--) {
         var key = localStorage.key(i);
         if(key.match(/^garlic/i)) {
            localStorage.removeItem(key);
         }
      }
      localStorage.removeItem('resetadminforms');
   }

   function refreshPanelData(url){
      $.post(url, 'data_only=1', function(data) {
         if ($('body').hasClass('learn')) {
           $('.panelcontent').html($(data.ui_learn_text));
         } else if ($('body').hasClass('help')) {
            $('#helppanel .panelcontent').html($(data.content));
         } else if ($('body').hasClass('settings')) {
            $('#settingspanel .panelcontent').html($(data.content));
         }
         formValidateBehavior();
      });
   };

   // close the tertiary panel entirely
   function closePanel() {
      $('body').removeClass('panel').removeClass('help').removeClass('settings').removeClass('learn');
   }

   // glitch campaign backgrounds
   function glitch(){
      if ($('#cnvs').length) {
         var dataseed = $('#cnvs').data('seed');
         if (dataseed) {
            var seed = dataseed.toString().split('').reverse();

            var imno = Math.ceil((Number(seed[0]) + 1) / 2);
            var atno = Math.ceil((Number(seed[1]) + 1) / 2);

            var colors = [
               // pink, purple, orange, red, green
               "250,56,102",
               "106,56,250",
               "255,124,18",
               "250,56,56",
               "0,207,127",
               // pink, purple, orange, red, green
               "250,56,102",
               "106,56,250",
               "255,124,18",
               "250,56,56",
               "0,207,127"
            ];

            var alphas = [
               "0.9",
               "0.6",
               "0.3"
            ];

            var widths = [
               9,
               24,
               60,
               980,
               120,
               180,
               210,
               240,
               270,
               330
            ];

            var bg = new Image();
            bg.src = cashAdminPath+"/assets/images/glitch/background/glitch"+imno+".jpg";
            bg.addEventListener("load", function() {
               var cw = $('#cnvs').width();
               var ch = $('#cnvs').height();
               var cnvs = document.getElementById('cnvs').getContext('2d');

               cnvs.drawImage(bg,0,0);

               olay = new Image();
               olay.src = cashAdminPath+"/assets/images/glitch/artist/artist"+atno+".jpg";
               olay.addEventListener("load", function() {

                  cnvs.globalCompositeOperation = "screen";
                  //cnvs.globalAlpha = 0.9;

                  var i = 0;
                  while (i < 2000) {
                     cnvs.drawImage(
                        olay,
                        (olay.width / ((Number(seed[2]) +1) * 5)) + (i / 3),
                        0,
                        widths[Number(seed[2])],
                        ch,
                        i,
                        0,
                        widths[Number(seed[2])],
                        ch
                     );
                     i = i + widths[Number(seed[2])];
                  }

                  cnvs.save();

                  cnvs.globalCompositeOperation = "hard-light";

                  var g = cnvs.createLinearGradient(0,0,$('#cnvs').width()/2,0);
                  g.addColorStop(0,'rgba(' + colors[Number(seed[3])] + ',' + alphas[Number(seed[3]) % 3] + ')');
                  g.addColorStop(1,'rgba(' + colors[Number(seed[4])] + ',' + alphas[Number(seed[3]) % 3] + ')');

                  cnvs.fillStyle=g;

                  cnvs.fillRect(0, 0, cw, ch);
                  cnvs.restore();

                  $('#cnvs').addClass('display');
               }, false);
            }, false);
         }
      }
   };


   /* Show/Hide Element Gallery */

   function moveToExample() {
      $(document).on('mouseenter', '.elementdisplay', function(e) {
         if (!$(this).hasClass('injectcode')) {
            e.preventDefault();
            var panel_name = $(this).attr('name');
            // the timeout slows it down just enough we don't get accidental changes on a
            // quick pass through a menu element to the right panel
            window.globaltimeout = window.setTimeout(function(){
               // Math.floor to avoid weird pixel fractions. the -34 accounts for padding
               $('.gallery').stop().animate({ scrollLeft:Math.floor($(panel_name).position().left) - 34}, "slow");
            }, 150);
            $('.example').removeClass('current');
            $('.gallery '+panel_name).addClass('current');
         }
      });

      $(document).on('mouseleave', '.elementdisplay', function(e) {
         e.preventDefault();
         //$('.gallery').animate({ scrollLeft: 0}, "fast");
         if (window.globaltimeout) {
            window.clearTimeout(window.globaltimeout);
         }
      });
   };

   // ZeroClipboard
   function ZclipBoard() {
      ZeroClipboard.config( { swfPath: cashAdminPath+"/ui/default/assets/flash/ZeroClipboard.swf" } );

      var client = new ZeroClipboard($(".copy"));
      client.on( "ready", function( readyEvent ) {
         //alert ("ready!");
         client.on( "aftercopy", function( event ) {
            alert("Embed Code Copied To Clipboard." ) //+ event.data["text/plain"] )
         } );

      } );
   };


   /**
   *
   *
   *
   * DO LINKS AND FORMS VIA AJAX
   *
   *
   *
   **/

   function ajaxPageBehaviors() {
      // open local (admin) links via AJAX
      // cashAdminPath is set in the main template to the www_base of the admin
      $(document).on('click', 'a[href^="' + cashAdminPath + '"]', function(e) {
         var el = $(e.currentTarget);
         if (!e.altKey && !e.ctrlKey && !e.metaKey && !e.shiftKey
            && !el.hasClass('lightboxed') && !el.hasClass('needsconfirmation') && !el.hasClass('showelementdetails')
            && !el.hasClass('noajax') && !el.parents('div').hasClass('inner')
            && (!$('body').hasClass('store') && el.attr('href').indexOf('elements/add') && !$('body').hasClass('page-editor') && !el.hasClass('connection'))
         ) {
            e.preventDefault();
            var url = el.attr('href');
            clearPersistentFormData();
            refreshPageData(url);
            el.blur();

            // if inside the tertiary panel or a panel touchpoint
         } else if (el.parents('div').hasClass('inner') && !el.hasClass('connection') && !el.hasClass('lightboxed') && !el.hasClass('needsconfirmation')){
            e.preventDefault();
            $('.panelcontent').removeClass('display');
            var url = el.attr('href');
            refreshPanelData(url);
            $('.panelcontent').addClass('display');
            $('.inner a').removeClass('current');
            el.addClass('current');
            el.blur();

            // if launching the store lightbox
         } else if (el.hasClass('store')){
            e.preventDefault();
            $('body').addClass('store');
         }
         // if launching the pageeditor lightbox
         else if (el.hasClass('page-editor')){
            e.preventDefault();
            $('body').addClass('page-editor');
         }
      });



      /*
      // stop in-app forms from submitting — we handle them in formValidateBehavior()
      $(document).on('submit', 'form', function(e) {
      var el = $(e.currentTarget);
      if (el.attr('action').toLowerCase().indexOf('s3.amazonaws') < 1 && !el.hasClass('noajax')) {
      e.preventDefault();
   }
});
*/
}

// submit a form via AJAX
function ajaxFormSubmit(form) {
   form = $(form);
   var url = form.attr('action');
   if (url == '') {
      url = location.pathname;
   }
   var formdata = $(form).serialize();
   if (form.hasClass('returntocurrentroute')) {
      formdata += '&forceroute=' + location.pathname.replace(cashAdminPath, '');
   }
   refreshPageData(url,formdata);
}

// validate forms and get them ready to submit (via AJAX)
// for more, see: http://jqueryvalidation.org/documentation/
var validator;
function formValidateBehavior() {
   $("form").each(function () {
      var el = $(this);
      validator = el.validate({
         errorClass: "invalid",
         errorElement: "span",
         //errorLabelContainer:"#pagemessage",
         highlight: function(element, errorClass) {
            $(element).addClass(errorClass);
            $(element.form).find("label[for=" + element.id + "]").addClass(errorClass);
         },
         unhighlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
            $(element.form).find("label[for=" + element.id + "]").removeClass(errorClass);
         },
         submitHandler: function(f) {
            //
            //
            // DO NOT wrap f as $(f) here or we'll convert it to a jquery object and
            // trigger the submit events on loop if it fails conditions for ajaxFormSubmit

            // note the $() added below
            if ($(f).attr('action').toLowerCase().indexOf('s3.amazonaws') < 1 && !$(f).hasClass('noajax')) {
               ajaxFormSubmit(f);
            } else {
               // and note the complete lack of dollarz here
               f.submit();
            }
         }
      });
   });
}





/**
*
*
*
* MAIN NAVIGATION
*
*
*
**/

/**
* redrawMainNav (function)
* collapse all main nav tabs, opening one if a section is specified
*
*/
function redrawMainNav(section) {
   if (section != currentSection) {
      currentSection = section;

      $('div.mainnavmenu li').each(function(index) {
         $(this).removeClass('current');
         if ($(this).hasClass(section+'nav')) {
            $(this).addClass('current');
         }
      });

      $('div.mainnavmenu a').each(function(index) {
         if ($(this).hasClass(section+'nav')) {
            $(this).parent().addClass('current');
         }
      });
   }
}





/**
*
*
*
* ASSET FILE HANDLING UI CODE
*
*
*
**/

// handle the upload forms
function handleUploadForms() {
   $('#connection_id').each( function() {
      if ( this.value > 0 ) {
         var newUploadEndpoint = $('.file-upload-trigger').data('upload-endpoint') + this.value;
         $('.upload-corral').fadeIn().find('.file-upload-trigger').data('upload-endpoint', newUploadEndpoint );
      }
   });
}

function assetFormBehaviors() {
   // make an asset public
   $(document).on('click', 'a[data-publicize-endpoint]', function(e) {
      e.preventDefault();

      var publicize = $.ajax({
         url: $(this).data('publicize-endpoint'),
         dataType: 'json'
      }).done(function(result) {
      }).complete(function(result) {
         var response = $.parseJSON(result.responseText);
         if (response.success) {
            $('#asset_location').val(response.location);
            $('#connection_id').val('0');
            $('.upload-corral').fadeOut();
         }
      });

   });

   // storage connection change handler
   $(document).on('change', '#connection_id', function(e) {
      if ( this.value > 0 ) {
         //var connectionID = this.value;
         var newUploadEndpoint = $('.file-upload-trigger').data('upload-endpoint') + this.value;

         var trigger = $('.upload-corral').fadeIn().find('.file-upload-trigger')
         trigger.data('upload-endpoint', newUploadEndpoint );

         var uploadTo = $.ajax({
            url: newUploadEndpoint,
            dataType: 'json',
            data: 'data_only=1'
         }).done(function(result) {
            //trigger.parents('.fadedtext').fadeOut( function() {
            trigger.parents('.drawer').find('.drawercontent').html(result.content);
            //});
         });
      } else {
         $('.upload-corral').fadeOut();
      }
   });

   // file upload handlers
   $(document).on('click', '.file-upload-trigger', function(e) {
      e.preventDefault();

      var trigger = $(this),
      iframeSrc = $(this).data('upload-endpoint'),
      connectionID = $('#connection_id').val();

      if ( connectionID == '0' ) {
         alert('Sorry, can\'t upload without a connection. Have you tried a normal link?');
         return false;
      } else {
         trigger.parents('.fadedtext').css('height','0px');
         trigger.parents('.fadedtext').animate({ opacity: 0 });
      }
   });
}




/**
*
*
*
* EVENT UI CODE
*
*
*
**/

// venue autocomplete
function venueAutocompleteBehavior() {
   $('.autocomplete').each( function() {
      var acURL = $(this).data('cash-endpoint-url');
      $(this).autocomplete({
         // probably should do some error handling here.
         source: function( request, response ) {
            $.ajax({
               url: acURL + '/' + request.term,
               dataType: "json",
               error: function( data) {},
               success: function( data ) {
                  response(
                     $.map( data, function( item ) {
                        return {
                           label: item.displayString,
                           value: item.displayString,
                           id: item.id
                        }
                     })
                  );
               }
            })
         },
         select: function( event, ui ) {
            // TODO: this is pretty ugly
            $('#event_venue').val( ui.item.id );
         },
         minLength: 2
      });
   });
}





/**
*
*
*
* DIALOGS, LIGHTBOXES, UI DISPLAY ENHANCEMENTS
*
*
*
**/

function modalBehaviors() {

   // overlay cancel button event
   $(document).on('click', '.modalcancel, .modalskip', function(e) {
      e.preventDefault();
      //remove the store identifier on close
      removeModal();
   });

   // Learn tips opened by inline click
   $(document).on('click', '.page-description', function(e) {

      if($("body").hasClass("settings") || $("body").hasClass("help")){
         $("body").removeClass("settings").removeClass("help");
         $("body").addClass("learn");
         $(this).addClass("display");
      } else if($("body").hasClass("learn")){
         $("body").removeClass("panel");
         window.globaltimeout = window.setTimeout(function(){
            $("body").removeClass("learn").removeClass("settings").removeClass("help");
         }, 250);
      } else {
         $(this).parents("body").addClass("learn").addClass("panel");
         $(this).addClass("display");
      }
   });

   // fade/close on escape key
   $(document).keyup(function(e) {
      if(e.keyCode === 27) {
         removeModal();
      }
   });
}

function removeModal() {
   $('.modallightbox').fadeOut('fast', function() {
      $('.modallightbox').remove();
      openlightbox = false;
   });
   $('.modalbg').fadeOut('fast', function() {
      $('.modalbg').remove();
      $("body").removeClass("store page-editor");

   });
   $(document).unbind('scroll',handleModalScroll);
}

function listenForModals() {
   // modal pop-ups
   $(document).on('click', '.needsconfirmation', function(e) {
      e.preventDefault();
      doMessage('','Are you sure?',true,$(this).attr('href'));

      this.blur();
   });

   // modal lightboxes
   $(document).on('click', '.lightboxed', function(e) {
      e.preventDefault();
      if ($(this).hasClass('closepanel')) {
         closePanel();
      }
      if ($(this).hasClass('returntocurrentroute')) {
         doModalLightbox($(this).attr('href'),true);
      } else {
         doModalLightbox($(this).attr('href'));
      }
      this.blur();
   });
}

/**
*
* function handleSwitchBlocks()
*
* parses out div.switchblock div and shows/hides stuff as needed
* ex: <div class="switchblock" data-watch="#target-select" data-default="#show-default" ...
*         ... data-change="#show-on-change" data-special='{"val":"#show-if-val"}'>
*
**/
function handleSwitchBlocks() {
   $('div.switchblock').each( function() {
      var w = $($(this).data('watch'));
      if (w) {
         // we found a select to watch
         var c = $(this).data('change');
         w.change(function() {
            $(c).addClass('show');
         });
         var v = w.val(); // grab the current val for the select
         var s = $(this).data('special');
         if (s) {
            // we found special values to display for certain options
            if (typeof(s) === 'object') {
               if (s.val) {
                  // this means it's a single set option
                  if (v == s.val) {
                     if($(s.target)) {
                        $(s.target).addClass('show'); // show it
                        w.change(function() {
                           // make sure to hide on change
                           $(s.target).removeClass('show');
                        });
                        return true;
                     }
                  }
               } else {
                  $.each(s, function(ii,iv) {
                     // no .val means we have an array of objects. neat!
                     // iterate through and compare current value to special value
                     if (v == iv.val) {
                        if($(iv.target)) {
                           $(iv.target).addClass('show'); // show it
                           w.change(function() {
                              // hide on change
                              $(iv.target).removeClass('show');
                           });
                           return true;
                        }
                     }
                  });
               }
            }
         }
         // we made it all the way to the end, and the monster at the end of the book
         // is me. lovable, huggable, grover.
         var d = $(this).data('default');
         $(d).addClass('show'); // show the default thing
         if (d !== c) {
            w.change(function() {
               // hide on change
               $(d).removeClass('show');
            });
         }
      }
   });
}

/**
* doMessage (function)
* displays a message to the user (modal/non-modal) or:
*
* opens a modal confirmation box for delete links, etc. essentially this is a
* silly "are you sure you want to click this?" message, and it sends along a
* GET param saying that it's been clicked — so the receiving controller knows
* it's happened and can skip displaying any form confirmation, etc.
*
*/

function doMessage(msg,label,modal,redirectUrl) {
   // markup for the confirmation link
   var markup = '<div class="modalbg"><div class="modaldialog">' +
   '<div class="pure-u-1">' +
   '<h4>' + label + '</h4>';
   if (msg) {
      markup += '<p><span class="big">' + msg + '</span></p>';
   }
   if (modal && redirectUrl) {
      markup += '<input type="button" class="button modalcancel" value="Cancel" />' +
      '<input type="button" class="button modalyes" value="Yes do it" />';
   }
   if (modal && !redirectUrl) {
      markup += '<input type="button" class="button modalyes" value="OK" />';
   }
   markup += '</div><!--pure-->' +
   '</div></div>';
   markup = $(markup);
   markup.hide();
   $('body').append(markup);

   if (!modal) {
      window.setTimeout(function() {$('.modalbg').remove();}, 2000);
   } else {
      // button events
      $('.modalyes').on('click', function(e) {
         e.preventDefault();
         if (redirectUrl) {
            refreshPageData(redirectUrl,'modalconfirm=1&redirectto='+location.pathname.replace(cashAdminPath, ''));
         }
         $('.modalbg').remove();
      });
   }

   // show the dialog with a fast fade-in
   $('.modalbg').fadeIn('fast');
}

var currentScroll = 0;
function handleModalScroll () {
   if ($(document).scrollTop() < currentScroll) {
      currentScroll = $(document).scrollTop();
      if (currentScroll < 0) {
         currentScroll = 0;
      }
      $('.modallightbox').css('top',currentScroll+'px');
   }
}

/**
* doModalLightbox (function)
* opens a modal input form from a specific route
*
*/
function doModalLightbox(route,returntocurrentroute) {
   jQuery.post(route,'data_only=1', function(data) {
      //removeModal();
      var addedClass = '';
      if (returntocurrentroute) {
         addedClass = 'returntocurrentroute '
      }
      var alreadyopen = $('.modallightbox').length;
      if (!alreadyopen) {
         // markup for the confirmation link
         //var modalTop = $(document).scrollTop() + 120;
         var markup = '<div class="modalbg">&nbsp;</div><div class="modallightbox ' + addedClass + '">' +
         //'<div class="row"><div class="twelve columns">' +
         '<h4>' + data.ui_title + '</h4>' +
         data.content + //jQuery.param(data) +
         //'</div></div>' +
         '<div class="tar" style="position:relative;z-index:9876;"><a href="#" class="modalcancel smalltext"><div class="icon icon-plus"></div><!--icon--></a></div>' +
         '</div></div>';

         markup = $(markup);
         markup.hide();
         $('body').append(markup);
         prepDrawers('<i class="icon icon-chevron-sign-up"></i>Hide','<i class="icon icon-chevron-sign-down"></i>Show');

         // fix form position based on current scrolltop:
         currentScroll = $(document).scrollTop();
         $('.modallightbox').css('top',currentScroll+'px');
      } else {
         var markup = '<h4>' + data.ui_title + '</h4>' +
         data.content + //jQuery.param(data) +
         //'</div></div>' +
         '<div class="tar" style="position:relative;z-index:9876;"><a href="#" class="modalcancel smalltext"><div class="icon icon-plus"></div><!--icon--></a></div>';
         $('.modallightbox').html(markup);
      }

      //reload quick copy
      ZclipBoard();

      $(document).bind('scroll',handleModalScroll);

      handleMultipartForms();

      if (!alreadyopen) {
         // show the dialog with a fast fade-in
         $('.modalbg').fadeIn('fast');
         $('.modallightbox').fadeIn('fast', function() {
            // the lightboxes have forms, so tell them to validate and post by ajax...
            formValidateBehavior();
            prepScalars();
         });
      } else {
         formValidateBehavior();
         prepScalars();
      }
   },'json');
}

var mpForm = {
   "form":null,
   "section":1,
   "total":0,
   "submit":null,
   "steps":null
};
function handleMultipartForms() {
   // in lightboxes:
   mpForm.section = 1;
   $('.modallightbox form.multipart').each(function() {
      mpForm.form = $(this);
      mpForm.submit = $(this).children('input[type=submit]')[0];//.value;
      mpForm.total = $(this).data('parts');
      $('.modallightbox form.multipart div.section').each(function() { // replace this with a hunt for specific children?
         if (!$(this).hasClass('part-'+mpForm.section) || !mpForm.total) {
            $(this).hide();
         }
      });
      mpForm.steps = $('<h5 class="steps">Step 1 of ' + mpForm.total + ': ' + $($(mpForm.form).children('.part-'+mpForm.section)[0]).data('section-name') + '</h5>');
      if (mpForm.total) {
         $(mpForm.submit).hide();
      } else {
         $($(mpForm.form).children('.section.basic-information')[0]).fadeIn();
         $(mpForm.steps).text(
            'Finalize: ' + $($(mpForm.form).children('.section.basic-information')[0]).data('section-name')
         );
      }
      mpForm.steps.insertBefore($(this));
      for (var i = 1; i <= mpForm.total; i++) {
         addMultipartButtons(i);
      };
   });
}

function addMultipartButtons(section) {
   var containerDiv = $('<div class="row"></div>');
   var buttonDiv = $('<div class="twelve columns"></div>');
   $(containerDiv).append(buttonDiv);
   if (section <= mpForm.total) {
      if (section == mpForm.total) {
         // this structure means we ALWAYS need a .section.basic-information div
         var descriptor = 'Next';
         //var nextTitle = $($(mpForm.form).children('.section.basic-information')[0]).data('section-name');
      } else {
         var descriptor = 'Next';
         //var nextTitle = $($(mpForm.form).children('.part-'+(section+1))[0]).data('section-name');
      }
      if (section > 1) {
         //var prevTitle = $($(mpForm.form).children('.part-'+(section-1))[0]).data('section-name');
         $(buttonDiv).append($('<button class="button multipart-prev">Previous</button> '));
      }
      $(buttonDiv).append('<button class="button multipart-next">'+descriptor+'</button>');
      $($(mpForm.form).children('.part-'+section)[0]).append(containerDiv);
   }
}

/**
* prepDrawers (function)
* Simple function to roll-up and roll-down content inside a div with class "drawer" â€” will
* look for a "handle" inside the div â€” an element that triggers the effect on click and remains
* visible throughout.
*
* Pass labelTextVisible/labelTextHidden to prepend the handle width "show"/"hide" type text
* Pass labelClassVisible/labelClassHidden to add classes for visible/hidden states
*
* Automatically closes all drawers and attaches event handlers
*
*/
function prepDrawers(labelTextVisible,labelTextHidden,labelClassVisible,labelClassHidden) {

   $('.drawer').each(function() {
      // minimize jQuery calls and simplify. set each element up fron in the function scope:
      var drawer, drawerHandle, drawerContent, drawerHandleLabel;
      drawer = $(this);
      if (drawer.find('.drawerhandleaction').length == 0) {
         if (drawer.hasClass('noprefix')) {
            $.data(drawer,'labelTextHidden','');
            $.data(drawer,'labelTextVisible','');
         } else {
            $.data(drawer,'labelTextHidden',labelTextHidden);
            $.data(drawer,'labelTextVisible',labelTextVisible);
         }
         drawerHandle = drawer.find('.drawerhandle');
         drawerContent = drawer.find('.drawercontent');
         // create the label span and add necessary classes
         drawerHandleLabel = $('<span class="drawerhandleaction">' + $.data(drawer,'labelTextHidden') + ' </span>');
         if (labelClassVisible) {
            drawerHandleLabel.addClass(labelClassHidden);
         }
         // first hide the content add a label to all the drawerhandles
         if (!drawer.hasClass('defaultopen')) {
            drawerContent.hide();
         }
         drawerHandle.prepend(drawerHandleLabel);
         // then set up click actions on each of them
         $(this).find('.drawerhandle').on('click',function () {
            $(this).blur();
            if (drawerContent.is(':hidden')) {
               drawerContent.slideDown(200, function () {
                  drawerHandleLabel.html($.data(drawer,'labelTextVisible') + ' ');
                  if (labelClassVisible) {
                     drawerHandleLabel.removeClass();
                     drawerHandleLabel.addClass(labelClassVisible);
                  }
               });
            } else {
               drawerContent.slideUp(200, function () {
                  drawerContent.hide();
                  drawerHandleLabel.html($.data(drawer,'labelTextHidden') + ' ');
                  if (labelClassHidden) {
                     drawerHandleLabel.removeClass();
                     drawerHandleLabel.addClass(labelClassHidden);
                  }
               });
            }
         });
      }
   });
}

function prepScalars() {
   $('div.scalar').each(function( index ) {
      $(this).hide();

      var cloneButton = $('<a href="#" class="clonebutton"><i class="icon icon-circle-plus"></i>' + $(this).data('actiontext') + '</a>');
      var cloneMarkup = $(this).html();
      var cloneCount = 0 + Number($(this).data('clonecount'));
      var clonedFrom = $(this).data('name');
      var clones = $(this).nextAll('div.clonedscalar');
      if (clones.length) {
         clones.last().after(cloneButton);
      } else {
         $(this).after(cloneButton);
      }

      cloneButton.click(function(e) {
         e.preventDefault();
         var cloned = $('<div class="clonedscalar">' + cloneMarkup + '</div>');
         cloned.find('*').each(function() {
            if ($(this).attr('name')) {
               $(this).attr('name',$(this).attr('name')+'-clone-'+clonedFrom+'-'+cloneCount);
               $(this).attr('id',$(this).attr('id')+'-clone-'+clonedFrom+'-'+cloneCount);
            }
         });
         cloneCount++;
         var removeButton = $('<a href="#" class="removescalar"><div class="icon icon-plus"></div></a>');
         cloned.append(removeButton);
         $(this).before(cloned);
      });
   });
}

function listenForScalars() {
   $(document).on('click', 'a.removescalar', function(e) {
      e.preventDefault();
      var cloned = $(this).parent('.clonedscalar');
      cloned.fadeOut(400, function() {
         cloned.detach();
      });
   });
}

function textareaTabBehavior() {
   $(document).on('keydown', 'textarea.taller', function(e) {
      // repurposed from here: http://jsfiddle.net/sdDVf/8/

      if(e.keyCode === 9) {
         var start = this.selectionStart;
         end = this.selectionEnd;
         var target = $(this);

         // set textarea value to: text before caret + tab + text after caret
         target.val(target.val().substring(0, start)
         + "\t"
         + target.val().substring(end));

         // put caret at right position again
         this.selectionStart = this.selectionEnd = start + 1;
         return false;
      }
   });
}

function prepItemVariants() {
   $(document).on('input', 'form.add_variants #primary_variant_name', function(e) {
      var v = $('#primary_variant_name').val();
      if (v) {
         $('form.add_variants div.variant1').addClass('pure-u-md-1-2');
         $('form.add_variants div.variant2').css('display', 'inline-block');
         $('form.add_variants .findreplace').each(function() {
            if ($(this).data('startvalue').indexOf('[primary_variant_name]') !== -1) {
               $(this).text($(this).data('startvalue').replace('[primary_variant_name]',v));
            }
         });
      } else {
         $('form.add_variants div.variant1').removeClass('pure-u-md-1-2');
         $('form.add_variants div.variant2').css('display', 'none');
      }
   });

   $(document).on('input', 'form.add_variants #secondary_variant_name', function(e) {
      var v = $('#secondary_variant_name').val();
      if (v) {
         $('form.add_variants div.variant1-options').addClass('pure-u-md-1-2');
         $('form.add_variants div.variant2-options').css('display', 'inline-block');
         $('form.add_variants .findreplace').each(function() {
            if ($(this).data('startvalue').indexOf('[secondary_variant_name]') !== -1) {
               $(this).text($(this).data('startvalue').replace('[secondary_variant_name]',v));
            }
         });
      } else {
         $('form.add_variants div.variant1-options').removeClass('pure-u-md-1-2');
         $('form.add_variants div.variant2-options').css('display', 'none');
      }
   });
}
})(jQuery);
