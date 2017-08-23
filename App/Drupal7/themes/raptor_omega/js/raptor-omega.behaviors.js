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

  // We'll use this to contain all PHP variables. 
  // We created it here so as to avoid creating it on each page it is needed.
  Drupal.pageData = {};

  // Show "Please wait" dialog
  Drupal.behaviors.raptorShowSpinner = function () {
    $('#administer-modal')
      .html('<div style="text-align: center; padding-top: 25px"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Please wait&hellip;</div>')
      .dialog({
        title: 'Loading…',
        modal: true,
        width: 300,
        height: 150,
        autoOpen: true,
        position: { 
          my: "center", 
          at: "center", 
          of: window 
        },
        buttons: {
          // reset any previous buttons
        }
      });
  };

  // Bring up Administer Dialog
  Drupal.behaviors.raptorShowAdministerDialog = function (title, url, callback, showSpinner, customDimensions, filter) {
    if (showSpinner) {
      Drupal.behaviors.raptorShowSpinner();
    }

    filter = filter || '';

    $('#administer-modal')
        .load(url + ' form', function () {
          var dialogDimensions = {
            width: 1200,
            height: 800
          };

          if (customDimensions) {
            dialogDimensions = customDimensions;
          }

          $(this)
            .find('h1')
            .remove()
            .end()
            .find('table.dataTable')
            .dataTable({
              'search': {
                'search': filter
              }
            })
            .end()
            .find('.datepicker')
            .datepicker()
            .end()
            .dialog({
                title: title,
                width: dialogDimensions.width,
                height: dialogDimensions.height,
                position: { 
                  my: "center", 
                  at: "center", 
                  of: window 
                },
                modal: true,
                autoOpen: true
            });

          if (typeof callback === 'function') {
            callback();
          }
        });
  }; // END Drupal.behaviors.raptorShowAdministerDialog

  $(document).ready(function () {

    /*** Idle timeout ***/

    var countdownIntervalId = 0,
      $timeoutWarningContainer = $('#timeout-warning'),
      $timeLeftContainer = $('#timeout-warning-time-left');

    // 'Yes, Keep me signed in'
    $('#timeout-stay-signed-in').on('click', function (event) {
      stopTimeoutWarning(countdownIntervalId);
    });

    // 'No, Sign me out'
    $('#timeout-sign-me-out').on('click', function (event) {
      kickUserOut();
    });

    var kickUserOut = function () {
      clearInterval(countdownIntervalId);
      window.location.href = 'raptor/kickout_timeout'
    };

    var isUserStillAuthenticated = function (authenticated) {
        // User may have logged out and is no longer authenticated
        return authenticated.toLowerCase() === 'yes';
    };

    var timeoutWarningIsDisplayed = false;

    if (location.href.indexOf('user/login') === -1 || Drupal.pageData.baseURL !== undefined) {

      // Ensure user isn't timed out if they are actively editing information on the page
      var lastChange = {};
      lastChange.lastAjaxCall = new Date(); // Idle time gets reset when page is first loaded
      lastChange.alivePingIntervalSeconds = 60; // This determines how often key presses and form changes reset the user's idle time

      // Obtain lastChange.alivePingIntervalSeconds from server
      $.getJSON(Drupal.pageData.baseURL + '/raptor/secondssincelastaction', function (response) {
        var seconds = parseFloat(response.alive_ping_interval_seconds);

        if (typeof seconds === 'number' && seconds > 0) {
          lastChange.alivePingIntervalSeconds = seconds;
        }
      });

      var resetSecondsSinceLastActionAjaxCall = function () {
        $.get(Drupal.pageData.baseURL + '/raptor/resetsecondssincelastaction', function () { /* Intentionally left blank */ });
        lastChange.lastAjaxCall = new Date();
      }

      var resetSecondsSinceLastAction = function () {
        var now, secondsSinceLastAjaxCall;

        now = new Date();
        secondsSinceLastAjaxCall = (now - lastChange.lastAjaxCall) / 1000;

        if (secondsSinceLastAjaxCall > lastChange.alivePingIntervalSeconds) {
          resetSecondsSinceLastActionAjaxCall();
        }
      }

      // Keep user from being timed out if they change any form values
      $(document).on('change', 'input, select, textarea', function () {
        resetSecondsSinceLastAction();
      })

      // Keep user from being timed out if they type anything
      $(document).on('keypress', 'input, textarea', function () {
        resetSecondsSinceLastAction();
      })

      var showTimeoutWarning = function (allowedGraceSeconds) {
        timeoutWarningIsDisplayed = true;
        $timeoutWarningContainer
          .slideDown()
          .find('#timeout-warning-time-left')
          .text(allowedGraceSeconds);
      };

      var stopTimeoutWarning = function (countdownIntervalId) {
        timeoutWarningIsDisplayed = false;
        resetSecondsSinceLastActionAjaxCall();
        $timeoutWarningContainer.slideUp();
        clearInterval(countdownIntervalId);
      };

      // Check each minute to see if the user needs to be logged out or not
      setInterval(function () {
        // Need to use grab the base URL from PHP to keep the URL path from breaking
        $.getJSON(Drupal.pageData.baseURL + '/raptor/secondssincelastaction', function (response) {
          // console.log('Outer raptor/secondssincelastaction %s', 0, response);

          if (!timeoutWarningIsDisplayed) {
            // User may have logged out and is no longer authenticated
            if (!isUserStillAuthenticated(response.authenticated)) {
              kickUserOut();
            }

            // User has been idle far too long
            if (response.idle_seconds > response.allowed_idle_seconds) {
              // console.log('timeout warning start');
              // Display the timeout warning banner
              showTimeoutWarning(response.allowed_grace_seconds);

              // Start the countdown
              countdownIntervalId = setInterval(function () {
                // Update countdown display
                var timeLeft = parseFloat($timeLeftContainer.text());

                if (timeLeft <= 0) {
                  kickUserOut();
                } else {

                  $timeLeftContainer.text(timeLeft - 1);

                  // Ping server for updated status every 10 seconds
                  if (timeLeft % 5 === 0) {
                    // Check to see if the user has been reactivated.
                    // Perhaps the user has multiple tabs open and has reactivated session
                    // from another tab.
                    $.getJSON(Drupal.pageData.baseURL + '/raptor/secondssincelastaction', function (countDownResponse) {
                      // console.log('Inner raptor/secondssincelastaction %s', timeLeft, countDownResponse);
                      // User may have logged out and is no longer authenticated
                      if (!isUserStillAuthenticated(countDownResponse.authenticated)) {
                        kickUserOut();
                      }

                      if (countDownResponse.idle_seconds <= countDownResponse.allowed_idle_seconds) {
                        stopTimeoutWarning(countdownIntervalId);
                      }
                    });
                  }
                }
              }, 1000);
            }
          }
        }); // END $.getJSON
      }, 30 * 1000);
    }

    /*** Page events ***/

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

    // Sub-navigation display
    $('.top-nav > ul > li > ul')
        .on('mouseover', function (e) {
            $(this).show();
        })
        .on('mouseout', function (e) {
            $(this).hide();
        });

    var dataTableCallback = function () {
      $('#administer-modal')
        .find('.dataTable')
        .DataTable()
        .page
        .len(25)
        .draw();      
    }

    // Sub-navigation links
    $('.top-nav > ul > li > ul > li')
        .on('click', function (e) {
          var callback, 
              showSpinner = true,
              title = $(this).text(),
              url = $(this).children('a').attr('href');

          e.preventDefault();
          // Remember last dialog content opened
          $.cookie('lastDialogTitle', title);
          $.cookie('lastDialogURL', url);

          if (['Manage Users', 'Manage Protocols', 'Manage Contraindications'].indexOf($(this).text()) !== -1) {
            callback = dataTableCallback;
          }

          Drupal.behaviors.raptorShowAdministerDialog(
            title, 
            url, 
            callback, 
            showSpinner
          );
        })
        .children('a')
        .on('click', function (e) {
          e.preventDefault();
        });

    /***** END NAVIGATION *****/



    /***** BEGIN DIALOGS *****/

    // TODO: Reduce the need for parent selectors by ID and use the .raptor-magic-dialog class instead
    var combineSelector = function (selector) {
      var dialogWorkflowWhitelist = [
        '#raptor-contraindications-mng-ci-form-builder',
        '#raptor-contraindications-viewci-form-builder',
        '#raptor-contraindications-editci-form-builder',
        '#raptor-contraindications-deleteci-form-builder',
        '#raptor-contraindications-addci-form-builder',

        '#raptor-glue-mng-protocols-form-builder',
        '#raptor-glue-viewprotocollib-form-builder',
        '#raptor-glue-editprotocollib-form-builder',
        '#raptor-glue-deleteprotocollib-form-builder',
        '#raptor-glue-addprotocollib-form-builder',
        
        '#raptor-glue-mngusers-form-builder',
        '#raptor-glue-viewuser-form-builder',
        '#raptor-glue-edituser-form-builder',
        '#raptor-glue-deleteuser-form-builder',
        '#raptor-glue-adduser-form-builder',

        '#raptor-glue-mng-lists-form-builder',
        '.raptor-magic-dialog',
        
        '#raptor-reports-viewreports-form-builder'
      ];

      return dialogWorkflowWhitelist.join(' ' + selector + ', ') + ' ' + selector;
    };

    // Globally available dialog
    $('#administer-modal')
      .on('click', combineSelector('.raptor-dialog-submit'), function (e) {
        var url = $(this).attr('data-redirect'),
            dialogTitle = $(this).val();

        Drupal.behaviors.raptorShowAdministerDialog(
          dialogTitle, 
          url,
          dataTableCallback, 
          true
        );
      })
      .on('click', combineSelector('.admin-action-button') + ', ' + combineSelector('.action-button'), function (e) {
        var url = $(this).closest('form').attr('action'),
            dialogTitle = $.cookie('lastDialogTitle');

        e.preventDefault();
        Drupal.behaviors.raptorShowSpinner();

        // Submit form via Ajax
        $.ajax({
          type: "POST",
          url: url,
          data: $(this).closest('form').serialize(),
          success: function (response) {

            if ($(response).find('.messages--error').length === 0) {
              // Validation passed

              // Go back to last dialog
              Drupal.behaviors.raptorShowAdministerDialog(
                $.cookie('lastDialogTitle'), 
                $.cookie('lastDialogURL'), 
                dataTableCallback, 
                true
              );

            } else {
              // Validation failed

              // Replace contents of admin dialog with HTML
              $('#administer-modal')
                .html( $(response).find('.messages--error') )
                .append( $(response).find('form') )
                .dialog('option', 'title', 'RAPTOR Error')
                .dialog('option', 'width', 1200)
                .dialog('option', 'height', 800);
            }
          },
          dataType: 'html'
        });
      })
      .on('click', '.raptor-dialog-cancel', function (e) {
        // Forget initial dialog loaded
        $.removeCookie('lastDialogURL');
        $.removeCookie('lastDialogTitle');
        // Close dialog
        $('#administer-modal').dialog('close');
      })
      .on('click', combineSelector('.admin-cancel-button'), function (e) {
        if ($.cookie('lastDialogURL').length) {
          Drupal.behaviors.raptorShowAdministerDialog(
            $.cookie('lastDialogTitle'), 
            $.cookie('lastDialogURL'), 
            dataTableCallback, 
            true
          );
        }
      })
      .on('click', combineSelector('a'), function (e) {
        var dialogTitle = $(this).closest('#administer-modal').prev().find('.ui-dialog-title').text();
        // Keep links within dialog from opening up a new page
        // In other words, what happens in Vegas stays in Vegas

        // if link takes user to another page
        if (this.href[0] === '/' || this.href.substr(0, 2) === '..' || (this.href !== '' && this.href[0] !== '#' && this.href.indexOf('javascript') === -1)) {
          e.preventDefault();

          Drupal.behaviors.raptorShowAdministerDialog(
            dialogTitle, 
            this.href, 
            null, 
            true
          );

        }
      });


    /***** END DIALOGS *****/

  });

})(jQuery);
