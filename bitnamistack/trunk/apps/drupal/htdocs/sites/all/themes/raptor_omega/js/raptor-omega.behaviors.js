(function ($) {

  /**
   * The recommended way for producing HTML markup through JavaScript is to write
   * theming functions. These are similiar to the theming functions that you might
   * know from 'phptemplate' (the default PHP templating engine used by most
   * Drupal themes including Omega). JavaScript theme functions accept arguments
   * and can be overriden by sub-themes.
   *
   * In most cases, there is no good reason to NOT wrap your markup producing
   * JavaScript in a theme function.
   */
  Drupal.theme.prototype.raptorOmegaExampleButton = function (path, title) {
    // Create an anchor element with jQuery.
    return $('<a href="' + path + '" title="' + title + '">' + title + '</a>');
  };

  /**
   * Behaviors are Drupal's way of applying JavaScript to a page. The advantage
   * of behaviors over simIn short, the advantage of Behaviors over a simple
   * document.ready() lies in how it interacts with content loaded through Ajax.
   * Opposed to the 'document.ready()' event which is only fired once when the
   * page is initially loaded, behaviors get re-executed whenever something is
   * added to the page through Ajax.
   *
   * You can attach as many behaviors as you wish. In fact, instead of overloading
   * a single behavior with multiple, completely unrelated tasks you should create
   * a separate behavior for every separate task.
   *
   * In most cases, there is no good reason to NOT wrap your JavaScript code in a
   * behavior.
   *
   * @param context
   *   The context for which the behavior is being executed. This is either the
   *   full page or a piece of HTML that was just added through Ajax.
   * @param settings
   *   An array of settings (added through drupal_add_js()). Instead of accessing
   *   Drupal.settings directly you should use this because of potential
   *   modifications made by the Ajax callback that also produced 'context'.
   */
  Drupal.behaviors.raptorOmegaExampleBehavior = {
    attach: function (context, settings) {
      // By using the 'context' variable we make sure that our code only runs on
      // the relevant HTML. Furthermore, by using jQuery.once() we make sure that
      // we don't run the same piece of code for an HTML snippet that we already
      // processed previously. By using .once('foo') all processed elements will
      // get tagged with a 'foo-processed' class, causing all future invocations
      // of this behavior to ignore them.
      // $('.some-selector', context).once('foo', function () {
      //   // Now, we are invoking the previously declared theme function using two
      //   // settings as arguments.
      //   var $anchor = Drupal.theme('raptorOmegaExampleButton', settings.myExampleLinkPath, settings.myExampleLinkTitle);

      //   // The anchor is then appended to the current element.
      //   $anchor.appendTo(this);
      // });
    }
  };

  // Bring up Administer Dialog
  Drupal.behaviors.raptorShowAdministerDialog = function (title, url, callback) {
    $('#administer-modal')
        .load(url + ' form', function () {

            $(this)
              .find('h1')
              .remove()
              .end()
              .find('table.dataTable')
              .dataTable()
              .end()
              .find('.datepicker')
              .datepicker()
              .end()
              .dialog({
                  title: title,
                  width: 1000,
                  height: 800,
                  modal: true,
                  autoOpen: true
              });

            if (callback) {
              callback();
            }
        });
  };

  $(document).ready(function () {

    // Clickable logo
    $('.logo').on('click', function (e) {
      window.location.href = '/drupal/worklist';
    });

    // jQuery Data Tables
    $('.dataTable').dataTable();

    /***** BEGIN NAVIGATION *****/

    // Main navigation
    $('.top-nav > ul > li')
        .on('mouseover', function (e) {
            $(this).children('ul').show();
        })
        .on('mouseout', function (e) {
            $(this).children('ul').hide();
        });

    // Administer link
    $('.top-nav > ul > li:nth-last-child(2) > a').on('click', function (e) {
      e.preventDefault();
    });

    // Sub-navigation
    $('.top-nav > ul > li > ul')
        .on('mouseover', function (e) {
            $(this).show();
        })
        .on('mouseout', function (e) {
            $(this).hide();
        });

    // Sub-navigation links
    $('.top-nav > ul > li > ul > li')
        .on('click', function (e) {
          var callback;

          e.preventDefault();

          if (['Manage Users', 'Manage Protocols', 'Manage Contraindications'].indexOf($(this).text()) !== -1) {
            callback = function () {
              $('.dataTable')
                .DataTable()
                .page
                .len(25)
                .draw();
            }
          } else {
            callback = function () {
              // Left empty on purpose
            }
          }

          Drupal.behaviors.raptorShowAdministerDialog($(this).text(), $(this).children('a').attr('href'), callback);
        })
        .children('a')
        .on('click', function (e) {
          e.preventDefault();
        });

    /***** END NAVIGATION *****/



    /***** BEGIN DIALOGS *****/

    // Globally available dialog
    $('#administer-modal')
      .on('click', '.raptor-dialog-submit', function (e) {
        window.location.href = $(this).attr('data-redirect');
      })
      .on('click', '.raptor-dialog-cancel', function (e) {
        $('#administer-modal').dialog('close');
      });

    /***** END DIALOGS *****/

  });

})(jQuery);
