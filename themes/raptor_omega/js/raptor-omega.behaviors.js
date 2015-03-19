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
    Drupal.pageData.messages = '';

    // Show "Please wait" dialog
    Drupal.behaviors.raptorShowSpinner = function (messageOverride) {
        var message = messageOverride ? messageOverride : 'Please wait&hellip;';

        $('#administer-modal')
                .html('<div id="raptor-spinner-container" style="text-align: center; padding-top: 25px"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>' + message + '</div>')
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
    Drupal.behaviors.raptorShowAdministerDialog = function (title, url, callback, showSpinner, customDimensions, filter, modal) {
        if (showSpinner) {
            Drupal.behaviors.raptorShowSpinner();
        }

        // Default values
        filter = filter || '';
        modal = modal || true;

        // Display dialog
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
                                modal: modal,
                                autoOpen: true
                            });

                    if (typeof callback === 'function') {
                        callback();
                    }
                });
    }; // END Drupal.behaviors.raptorShowAdministerDialog

    Drupal.behaviors.dataTableCallback = function () {
        $('#administer-modal')
                .find('.dataTable')
                .DataTable()
                .page
                .len(25)
                .draw();

        // Display success messages
        if (Drupal.pageData.messages) {
            $('#administer-modal')
                    .prepend('<div class="messages messages--status">' + Drupal.pageData.messages + '</div>')
                    .dialog('option', 'width', 1200)
                    .dialog('option', 'height', 800);

            // Clear this cookie
            Drupal.pageData.messages = '';
        }
    }


    /***** BEGIN WORKFLOW DIALOGS *****/


    // TODO: Reduce the need for parent selectors by ID and use the .raptor-magic-dialog class instead
    combineSelector = function (selector) {
        var dialogWorkflowWhitelist = [
            '.raptor-glue-addradiationdosehxentry-form-builder'
        ];

        return dialogWorkflowWhitelist.join(' ' + selector + ', ') + ' ' + selector;
    };

    // Globally available dialog
    $(document)
            .on('click', combineSelector('.raptor-dialog-submit'), function (e) {
                var url = $(this).attr('data-redirect');
                window.location.href = url;
            })
            .on('click', combineSelector('.admin-action-button') + ', ' + combineSelector('.action-button'), function (e) {
                var form = $(this).closest('form'),
                        url = form.attr('action'),
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
                            var feedback = $(response).find('.messages--status').html();

                            if (!feedback) {
                                feedback = '<iframe>' + response + '</iframe>'
                            }

                            Drupal.pageData.messages = feedback;

                            // Close dialog
                            $('#administer-modal').dialog('close');

                            // Update Protocol Dose Hx tab
                            if (form.is('.raptor-glue-addradiationdosehxentry-form-builder')) {
                                // Defined in protocol.js
                                Drupal.behaviors.loadContent($('#tab-content6').children('[data-url]').get(0));
                            }

                        } else {
                            // Validation failed

                            // Display error messages
                            $('#administer-modal')
                                    .html($(response).find('.messages--error'))
                                    .append($(response).find('form'))
                                    .dialog('option', 'title', 'RAPTOR Error')
                                    .dialog('option', 'width', 1200)
                                    .dialog('option', 'height', 800);
                        }
                    },
                    error: function (response) {
                        $('#administer-modal').html([
                            '<div><input class="raptor-dialog-cancel" type="button" value="Exit with No Changes"></div>',
                            '<div class="messages messages--error"><h2 class="element-invisible">Error message</h2>' + response.statusText + '.' + response.responseText.substr(0, 200) + '&hellip;' + '</div>'
                        ].join(''));
                    },
                    dataType: 'html'
                });
            })
            .on('click', '.raptor-dialog-cancel', function (e) {
                // Forget initial dialog loaded
                $.removeCookie('lastDialogURL');
                $.removeCookie('lastDialogTitle');
                // Close dialog
                try {
                    $('#administer-modal').dialog('close');
                } catch (exception) {
                    //Do nothing
                }

                try {
                    $('#cancelorder-modal').dialog('close');
                } catch (exception) {
                    //Do nothing
                }
                //replicating cancel order behavior here for replace order alex-edits
                try {
                    $('#replaceorder-modal').dialog('close');
                } catch (exception) {
                    //Do nothing
                }

            })
            .on('click', combineSelector('.admin-cancel-button'), function (e) {
                if ($.cookie('lastDialogURL').length) {
                    Drupal.behaviors.raptorShowAdministerDialog(
                            $.cookie('lastDialogTitle'),
                            $.cookie('lastDialogURL'),
                            Drupal.behaviors.dataTableCallback,
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


    /***** END WORKFLOW DIALOGS *****/


    $(document).ready(function () {


        /*** Idle timeout ***/

        var countdownIntervalId = 0,
                $timeoutWarningContainer = $('#timeout-warning'),
                $timeLeftContainer = $('#timeout-warning-time-left'),
                i;

        // 'Yes, Keep me signed in'
        $('#timeout-stay-signed-in').on('click', function (event) {
            stopTimeoutWarning(countdownIntervalId);
        });

        // 'No, Sign me out'
        $('#timeout-sign-me-out').on('click', function (event) {
            // debugger
            kickUserOut();
        });

        var kickUserOut = function () {
            clearInterval(countdownIntervalId);
            window.location.href = Drupal.pageData.baseURL + '/raptor/kickout_timeout'
        };

        var isUserStillAuthenticated = function (authenticated) {
            // User may have logged out and is no longer authenticated
            return authenticated.toLowerCase() === 'yes';
        };

        var timeoutWarningIsDisplayed = false;

        var isLoginPage = function () {
            return location.href.indexOf('user/login') !== -1 || location.href.indexOf('raptor/kickout_timeout') !== -1;
        };

        var isWorklistPage = function () {
            return location.href.split('/').indexOf('worklist') !== -1;
        };

        var isProtocolPage = function () {
            return location.href.indexOf('/protocol') !== -1;
        };

        var lastChange = {};

        if (!isLoginPage()) {
            // Ensure user isn't timed out if they are actively editing information on the page
            lastChange.lastAjaxCall = new Date(); // Idle time gets reset when page is first loaded
            lastChange.alivePingIntervalSeconds = 60; // This determines how often key presses and form changes reset the user's idle time

            // Obtain lastChange.alivePingIntervalSeconds from server
            $.getJSON(Drupal.pageData.baseURL + '/raptor/userinteractionping', function (response) {
                var seconds = parseFloat(response.thisuser.alive_ping_interval_seconds);
                if (typeof seconds === 'number' && seconds > 0) {
                    lastChange.alivePingIntervalSeconds = seconds;
                }
            });

            var resetSecondsSinceLastActionAjaxCall = function () {
                if (!isProtocolPage()) {
                    $.get(Drupal.pageData.baseURL + '/raptor/userinteractionping?resetsecondssincelastaction', function () { /* Intentionally left blank */
                    });
                } else {
                    $.get(Drupal.pageData.baseURL + '/raptor/userinteractionping?refreshlocks', function () { /* Intentionally left blank */
                    });
                }
                lastChange.lastAjaxCall = new Date();
            };

            var resetSecondsSinceLastAction = function () {
                var now, secondsSinceLastAjaxCall;

                now = new Date();
                secondsSinceLastAjaxCall = (now - lastChange.lastAjaxCall) / 1000;

                if (secondsSinceLastAjaxCall > lastChange.alivePingIntervalSeconds) {
                    resetSecondsSinceLastActionAjaxCall();

                    //alert("resetting keep alive seconds");
                }
            }

            // Keep user from being timed out if they change any form values
            // Keep user from being timed out if they type anything
            $(document).on('change keypress', 'input, select, textarea', function () {
                resetSecondsSinceLastAction();
            });

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

            //keeps track of locks as they are added to records
            var lockedTIDSRecords = [];

            var addLockedTID = function (tid) {
                if (lockedTIDSRecords.indexOf(tid) === -1) {
                    lockedTIDSRecords.push(tid);
                    console.log("Adding a TID " + lockedTIDSRecords);
                }
            }
            ;
            var removeLockedTID = function (tid) {
                if (lockedTIDSRecords.indexOf(tid) !== -1) {
                    console.log("Removing a TID " + tid);
                    lockedTIDSRecords = jQuery.grep(lockedTIDSRecords, function (value) {
                        return value !== tid;
                    });
                }
            }
            ;
            
            var checkLockedTID = function(tid){
                return lockedTIDSRecords.indexOf(tid);
            }
            
            var checkLockedRecords = function (response) {
                var removedTIDS = [];
                var worklistTable = $('#worklistTable').DataTable();


                // TODO: Find more efficient way of showing locks aside from looping through table once for each locked protocol
                // Add locks to newly locked pages
                for (i = 0; i < response.tickets.edit_locks.length; i++) {
                    var otherUserLocks = worklistTable
                            .cells(function (idx, data, node) {
                                var lockedProtocol = response.tickets.edit_locks[i];
                                if (lockedProtocol.IEN === data) {
                                    addLockedTID(data); //lockedProtocol IEN is a TID
                                } else {
                                    if (lockedTIDSRecords.indexOf(data)) {
                                        removedTIDS.push(data);
                                        removeLockedTID(data);//TODO: only remove if it's not a self and other user lock
                                    }
                                }
                                return data === lockedProtocol.IEN && lockedProtocol.locked_by_uid !== Drupal.pageData.userID ? true : false;
                            })
                            .nodes();

                    // Add a class to the cells
                    otherUserLocks.to$().addClass('locked_column');

                    var selfLocks = worklistTable
                            .cells(function (idx, data, node) {
                                var lockedProtocol = response.tickets.edit_locks[i];
                                if (lockedProtocol.IEN === data) {
                                    addLockedTID(data); //lockedProtocol IEN is a TID
                                } else {
                                    if (lockedTIDSRecords.indexOf(data)) {
                                        removedTIDS.push(data);
                                        removeLockedTID(data);//TODO: only remove if it's not a self and other user lock
                                    }
                                }
                                return data === lockedProtocol.IEN && lockedProtocol.locked_by_uid === Drupal.pageData.userID ? true : false;
                            })
                            .nodes();

                    // Add a class to the cells
                    selfLocks.to$().addClass('locked_owned_column');
                    console.log("Currently locked record id: " + response.tickets.edit_locks[i].IEN + " Current state of Array: " + lockedTIDSRecords );
                    //remove locks from records
                    var removeLocks = worklistTable
                            .cells(function (idx, data, node) {
                                var lockedProtocol = response.tickets.edit_locks[i];
                                
                            })
                            .nodes();

                    //removes locks class from the cells
                    //removeLocks.to$().removeClass('locked_owned_column');
                    //removeLocks.to$().removeClass('locked_column');
                }
                ;
            }
            ;

            // Check each minute to see if the user needs to be logged out or not
            setInterval(function () {
                //userinteractionpingParam = isProtocolPage() ? '?refreshlocks' : '';

                // Need to use grab the base URL from PHP to keep the URL path from breaking userinteractionpingParam
                $.getJSON(Drupal.pageData.baseURL + '/raptor/userinteractionping', function (response) {
                    // console.log('Outer raptor/userinteractionping %s', 0, response);

                    if (!timeoutWarningIsDisplayed) {
                        // User may have logged out and is no longer authenticated
                        if (!isUserStillAuthenticated(response.thisuser.authenticated)) {
                            // debugger
                            kickUserOut();
                        }

                        // User has been idle far too long
                        if (response.thisuser.idle_seconds > response.thisuser.allowed_idle_seconds) {
                            // console.log('timeout warning start');
                            // Display the timeout warning banner
                            showTimeoutWarning(response.thisuser.allowed_grace_seconds);

                            // Start the countdown
                            countdownIntervalId = setInterval(function () {
                                // Update countdown display
                                var timeLeft = parseFloat($timeLeftContainer.text());

                                if (timeLeft <= 0) {
                                    // debugger
                                    kickUserOut();
                                } else {

                                    $timeLeftContainer.text(timeLeft - 1);

                                    // Ping server for updated status every 10 seconds
                                    if (timeLeft % 5 === 0) {
                                        // Check to see if the user has been reactivated.
                                        // Perhaps the user has multiple tabs open and has reactivated session
                                        // from another tab.
                                        $.getJSON(Drupal.pageData.baseURL + '/raptor/userinteractionping', function (countDownResponse) {
                                            // console.log('Inner raptor/userinteractionping %s', timeLeft, countDownResponse);
                                            // User may have logged out and is no longer authenticated
                                            if (!isUserStillAuthenticated(countDownResponse.thisuser.authenticated)) {
                                                // debugger
                                                kickUserOut();
                                            }

                                            if (countDownResponse.thisuser.idle_seconds <= countDownResponse.thisuser.allowed_idle_seconds) {
                                                stopTimeoutWarning(countdownIntervalId);
                                            }
                                        });
                                    }
                                }
                            }, 1000);
                        }
                    }

                    // Mark Worklist rows as locked whenever another user is accessing the page

                    if (isWorklistPage() && response.tickets.edit_locks.length) {
                        checkLockedRecords(response);
                    }
                    ;
                }); // END $.getJSON
            }, 2 * 1000);
        }



        /*** Page events ***/

        // Clickable logo
        $('.logo').on('click', function (e) {
            window.location.href = Drupal.pageData.baseURL + '/worklist';
        });

        // jQuery Data Tables
        $('.dataTable').dataTable({
            'pageLength': 30
        });

        // Form submit handlers for stand alone data layer pages
        $('.raptor-dialog-cancel').on('click', function (e) {
            location.href = Drupal.pageData.baseURL + '/worklist';
        });

        $('#administer-modal')
                .on('click', '.raptor-dialog-submit, .admin-cancel-button', function (e) {
                    location.href = $(this).attr('data-redirect');
                });

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

        // Sub-navigation links
        $('.top-nav > ul > li > ul > li:not([data-no-dialog])')
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
                        callback = Drupal.behaviors.dataTableCallback;
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
        $.getJSON(Drupal.pageData.baseURL + '/raptor/userinteractionping', function (response) {
            if (isWorklistPage() && response.tickets.edit_locks.length) {
                checkLockedRecords(response);
            }
            ;
        });
        //bottom of document.ready()
    });

})(jQuery);
