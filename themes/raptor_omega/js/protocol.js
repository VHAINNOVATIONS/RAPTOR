/*jslint browser: true*/
/*global jQuery, document*/

// Drupal jQuery (version 1.4.4 as of 1/3/15)
(function (document, $) {

    $(document)
            .ajaxStart(function () {
                Drupal.behaviors.disableProtocolControls();
            })
            .ajaxStop(function () {
                Drupal.behaviors.enableProtocolControls();
            });

    // Keep users from fiddling with form options
    Drupal.behaviors.disableProtocolControls = function () {
        Drupal.pageData.disableLinks = true;
        $('input, select, textarea')
                .not('#edit-protocol1-nm')
                .attr('disabled', 'disabled');

        // For links that have the "javascript" prefix
        $('a[href^=javascript]').each(function (index, element) {
            $(element)
                    .attr('data-onclick', $(this).attr('href'))
                    .attr('href', 'javascript:void();');
        });
    };

    // Re-enabled form controls
    Drupal.behaviors.enableProtocolControls = function () {
        Drupal.pageData.disableLinks = false;
        $('input, select, textarea')
                .not('#edit-protocol1-nm, .container-inline select, #edit-contrast-fieldset select')
                .removeAttr('disabled');

        // For links that have the "javascript" prefix
        $('[data-onclick]').each(function (index, element) {
            $(element)
                    .attr('href', $(this).attr('data-onclick'))
                    .removeAttr('data-onclick');
        });
    };

    // When "select" link under Library tab is clicked select the corresponding 
    // drop down option under the Protocol tab
    $(document).delegate('.select-protocol', 'click', function (e) {
        e.preventDefault();
        $('#tab1').trigger('click');
        $('[id^=edit-protocol1-nm]').val($(this).attr('data-protocol_shortname')).change();
    });

})(document, DrupaljQuery);

// Theme jQuery (version 1.11.1 as of 1/3/15)
(function (document, $) {
    $(window).load(function () {
        $("#header-sticky-wrapper-patient-name").hide();
        $("#header-sticky-wrapper-patient-name").sticky({topSpacing: 0});
    });
    $(window).scroll(function () {
        var height = $(window).scrollTop();
        if (height > 300) {
            $("#header-sticky-wrapper-patient-name").fadeIn("slow");
        } else {
            $("#header-sticky-wrapper-patient-name").hide();
        }
    });
    //The function below will warn the users about unsaved changes if they 
    //try to navigate away from the screen
    var confirmOnPageExit = function (e) {
        e = e || $(window).event;
        var message = "You are about to navigate away from this page without saving your changes.";
        if (e) {
            e.returnValue = message;
        }
        return message;
    };

    setInterval(function () {
        if ($('#timeout-warning').is(':visible')) {
            window.onbeforeunload = null;
            //direct user's attention to timeout warning, scroll up
            $("html, body").animate({scrollTop: "0px"}, 1000);
        }
    }, 5000);


    //disable confirmation message for submit buttons
    $(document).on('click', '.form-submit', function () {
        //console.log("You've hit submit button!");
        window.onbeforeunload = null;
    });


    /*If the user presses a key, types something in or makes an ajax call, we have to reset action seconds
     * to keep the session active
     * Correction: not every ajax call is counted as a change - users were getting warnings by just clicking tabs,
     * solved by removing the 'chage' keyword from event handler keypress...etc*/
    $(document).on('keypress keyup keydown', 'input, select, textarea, .checkbox', function () {
        //console.log("Something is happening");
        window.onbeforeunload = confirmOnPageExit;
    });


    //dectects changes in drop down selectors which make ajax calls on protocol page
    $(document).on('click', '.form-select, .form-checkbox', function () {
        //console.log("Something is happening");
        window.onbeforeunload = confirmOnPageExit;
    });

    'use strict';
    // this function is strict...

    //
    //
    //
    $(document).on('click', '.select-protocol', function () {
        Drupal.behaviors.raptorShowSpinner("Please wait, loading selected template...");
        function checkAjaxLoadStatus() {
            setTimeout(
                    function() {
                        if ($('.message').is(':visible')) {
                            loop();
                        } else {
                            $('.ui-dialog-titlebar-close').click();
                        }
                    }, 5000
            );
        }
        checkAjaxLoadStatus();
    });

    // This content will be loaded by Ajax so we attach the click event to the document
    // which is always available.
    $(document)
            .on('click', '.raptor-dose-xs-crud', function (e) {
                e.preventDefault();
                Drupal.behaviors.raptorShowAdministerDialog(
                        'Radiation Dose Hx',
                        this.href,
                        null,
                        true,
                        {
                            width: 1000,
                            height: 600
                        },
                null,
                        false
                        );
            });

    $(document).ready(function () {
        // Determine whether all hyperlinks need to be disabled
        Drupal.pageData.disableLinks = false;

        $('a', '.tabs-wrapper').on('click', function (e) {
            if (Drupal.pageData.disableLinks) {
                e.preventDefault();
            }
        })

        $('#medications_detail').on('click', function (e) {
            e.preventDefault();
            $('#tab2').trigger('click');
        });

        $('#vitals_detail').on('click', function (e) {
            e.preventDefault();
            $('#tab3').trigger('click');
        });

        $('#allergies_detail').on('click', function (e) {
            e.preventDefault();
            $('#tab4').trigger('click');
        });

        $('#labs_detail').on('click', function (e) {
            e.preventDefault();
            $('#tab5').trigger('click');
        });

        $('#radiology_detail').on('click', function (e) {
            e.preventDefault();
            $('#tab10').trigger('click');
        });

        $('#static-warnings').addClass('read-only').insertBefore('#tabs-wrapper');

        $('#edit-suspend-button')
                .on('click', function (e) {
                    var $dialog;

                    e.preventDefault();

                    $(window).scrollTo('div:first-of-type', 800, {
                        onAfter: function () {

                            $('#suspend-modal')
                                    .load('raptor/suspendticket form', function () {
                                        $dialog = $(this).dialog({
                                            modal: false,
                                            show: {
                                                effect: 'fadeIn'
                                            },
                                            hide: {
                                                effect: 'fadeOut'
                                            },
                                            position: {
                                                my: "left top",
                                                at: "center bottom",
                                                of: $('header.cf').find('table')
                                            },
                                            width: 375
                                        });

                                        $(this).on('click', '.raptor-dialog-cancel', function (e) {
                                            $dialog.dialog('close');
                                        });
                                    });
                        }
                    })
                });

        $('#edit-cancelorder-button')
                .on('click', function (e) {
                    var $dialog,
                            spinnerHTML = '<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading&hellip;</div>';

                    e.preventDefault();

                    $(window).scrollTo('div:first-of-type', 800, {
                        onAfter: function () {
                            $('#cancelorder-modal')
                                    .html(spinnerHTML)
                                    .dialog({
                                        modal: true,
                                        show: {
                                            effect: 'fadeIn'
                                        },
                                        hide: {
                                            effect: 'fadeOut'
                                        },
                                        position: {
                                            my: "left top",
                                            at: "center bottom",
                                            of: $('header.cf').find('table')
                                        },
                                        width: 550
                                    })
                                    .load('raptor/cancelorder form', function () {
                                        // Just have content load as is
                                    });
                        }
                    })
                });

        $('#cancelorder-modal')
                .on('click', '.raptor-dialog-cancel', function (e) {
                    // Exit with no changes button
                    var $dialog = $('#cancelorder-modal');
                    $dialog.dialog('close');
                })
                .on('click', '#edit-remove', function (e) {
                    var $form = $('#cancelorder-modal').find('form'),
                            $dialog = $('#cancelorder-modal');

                    e.preventDefault();

                    // Submit form via Ajax
                    $.ajax({
                        type: "POST",
                        url: $form.attr('action'),
                        data: $form.serialize(),
                        beforeSend: function (jqXHR, settings) {
                            $dialog.html('<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Canceling order&hellip;</div>');
                        },
                        success: function (response) {
                            if ($(response).find('.messages--error').length === 0) {
                                // Validation passed
                                //window.location.href = Drupal.pageData.baseURL + '/worklist?successactioncompleted=canceledorder';
                                window.location.href = Drupal.pageData.baseURL + '/worklist?successmsg=' + encodeURIComponent($(response).find('.messages--status').text());
                            } else {
                                // Validation failed

                                // Replace contents of admin dialog with HTML
                                $dialog
                                        .html($(response).find('.messages--error'))
                                        .append($(response).find('form'));
                            }
                        },
                        error: function (response) {
                            $dialog.html([
                                '<div><input class="raptor-dialog-cancel" type="button" value="Exit with No Changes"></div>',
                                '<div class="messages messages--error"><h2 class="element-invisible">Error message</h2>' + response.statusText + '.' + response.responseText.substr(0, 200) + '&hellip;' + '</div>'
                            ].join(''));
                        },
                        dataType: 'html'
                    });
                });

        //alex edits
        //Loads the Replace order form
        $('#raptor-protocol-replace-order-button')
                .on('click', function (e) {
                    var $dialog,
                            spinnerHTML = '<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading order details from VistA&hellip;</div>';

                    e.preventDefault();

                    $(document).scrollTo('.cf', 800, {
                        onAfter: function () {
                            $('#replaceorder-modal')
                                    .html(spinnerHTML)
                                    .dialog({
                                        modal: true,
                                        show: {
                                            effect: 'fadeIn'
                                        },
                                        hide: {
                                            effect: 'fadeOut'
                                        },
                                        position: {
                                            my: "left top",
                                            at: "center bottom",
                                            of: $('header.cf').find('table')
                                        },
                                        width: 900
                                    })
                                    .load('raptor/replaceorder form', function () {
                                        // Just have content load as is
                                        $('.form-item.form-type-textfield.form-item-navigationoverride').hide();
                                    });
                        }
                    })
                });
        //Specify what to do for each action/button on the form
        //replace-order-wizard
        $('#replaceorder-modal')

                .on('click', '.raptor-dialog-cancel', function (e) {
                    // Exit with no changes button
                    var $dialog = $('#replaceorder-modal');
                    $dialog.dialog('close');
                })
                .on('click', '#replace-order-submit-next-button', function (e) {
                    var $form = $('#replaceorder-modal').find('form'),
                            $dialog = $('#replaceorder-modal');
                    //$('.form-item.form-type-textfield.form-item-navigationoverride').hide();
                    $('#replaceorderstep').val("next");
                    $('#formhost').val("embedded");
                    e.preventDefault();
                    $.ajax({
                        type: "POST",
                        url: $form.attr('action'),
                        data: $form.serialize(),
                        beforeSend: function (jqXHR, settings) {
                            $dialog.html('<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading Information&hellip;</div>');
                        },
                        success: function (response) {
                            //if we have a successful submission, the user is redirected to the updated record (look at TID)
                            if ($(response).find('#redirection-target').text()) {
                                window.location.href = $(response).find('#redirection-target').text();
                            } else {
                                if ($(response).find('.messages--error').length === 0) {

                                    $dialog
                                            .html($(response).find('.messages--status'))
                                            .append($(response).find('form'));
                                } else {
                                    // Validation failed
                                    // Replace contents of admin dialog with HTML
                                    $dialog
                                            .html($(response).find('.messages--error'))
                                            .append($(response).find('form'));
                                }
                            }
                        },
                        error: function (response) {
                            $dialog.html([
                                '<div><input class="raptor-dialog-cancel" type="button" value="Exit with No Changes"></div>',
                                '<div class="messages messages--error"><h2 class="element-invisible">Error message</h2>' + response.statusText + '.' + response.responseText.substr(0, 200) + '&hellip;' + '</div>'
                            ].join(''));
                        },
                        dataType: 'html'
                    });

                })

                .on('click', '#replace-order-submit-back-button', function (e) {
                    var $form = $('#replaceorder-modal').find('form'),
                            $dialog = $('#replaceorder-modal');
                    $('#replaceorderstep').val("back");
                    $('#formhost').val("embedded");
                    e.preventDefault();
                    $.ajax({
                        type: "POST",
                        url: $form.attr('action'),
                        data: $form.serialize(),
                        beforeSend: function (jqXHR, settings) {
                            $dialog.html('<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading Information&hellip;</div>');
                        },
                        success: function (response) {
                            if ($(response).find('.messages--error').length === 0) {

                                $dialog
                                        .html($(response).find('.messages--status'))
                                        .append($(response).find('form'));
                            } else {
                                // Validation failed
                                // Replace contents of admin dialog with HTML
                                $dialog
                                        .html($(response).find('.messages--error'))
                                        .append($(response).find('form'));
                            }
                        },
                        error: function (response) {
                            $dialog.html([
                                '<div><input class="raptor-dialog-cancel" type="button" value="Exit with No Changes"></div>',
                                '<div class="messages messages--error"><h2 class="element-invisible">Error message</h2>' + response.statusText + '.' + response.responseText.substr(0, 200) + '&hellip;' + '</div>'
                            ].join(''));
                        },
                        dataType: 'html'
                    });

                })

                .on('click', '#replace-order-submit-finish-button', function (e) {
                    var $form = $('#replaceorder-modal').find('form'),
                            $dialog = $('#replaceorder-modal');
                    //$('.form-item.form-type-textfield.form-item-navigationoverride').hide();
                    $('#replaceorderstep').val("finish");
                    $('#formhost').val("embedded");
                    e.preventDefault();
                    // Submit form via Ajax
                    $.ajax({
                        type: "POST",
                        url: $form.attr('action'),
                        data: $form.serialize(),
                        beforeSend: function (jqXHR, settings) {

                            $dialog.html('<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Replacing order&hellip;</div>');
                        },
                        success: function (response) {
                            if ($(response).find('.messages--error').length === 0) {
                                // Validation passed
                                //alert("Finished");
                                //window.location.href = Drupal.pageData.baseURL + '/worklist?successmsg=' + encodeURIComponent($(response).find('.messages--status').text());
                            } else {
                                // Validation failed

                                // Replace contents of admin dialog with HTML
                                $dialog
                                        .html($(response).find('.messages--error'))
                                        .append($(response).find('form'));
                            }
                        },
                        error: function (response) {
                            $dialog.html([
                                '<div><input class="raptor-dialog-cancel" type="button" value="Exit with No Changes"></div>',
                                '<div class="messages messages--error"><h2 class="element-invisible">Error message</h2>' + response.statusText + '.' + response.responseText.substr(0, 200) + '&hellip;' + '</div>'
                            ].join(''));
                        },
                        dataType: 'html'
                    });
                });
        //end edits


        // Request Collaboration
        $('#raptor-protocol-collaborate').on('click', function (e) {
            e.preventDefault();
            Drupal.behaviors.raptorShowAdministerDialog(
                    'Request Collaboration',
                    'raptor/requestcollaborate',
                    null,
                    true,
                    {
                        width: 1000,
                        height: 600
                    },
            'Radiologist'
                    );
        });

        $('#administer-modal')
                .on('click', '#edit-action-buttons-okay', function (e) {
                    var $protocolForm = $('#raptor-glue-protocolinfo-form-builder'),
                            $chooseVisitForm = $('#raptor-glue-choosevisit-form-builder');

                    e.preventDefault();
                    //$protocolForm.prop('selected_vid').value = $chooseVisitForm.find('[name=group_vid]').val();
                    var selectedVid = $chooseVisitForm.find('input[name=group_vid]:checked').val();
                    var eSig = $chooseVisitForm.find('[name=subform_commit_esig]').val()

                    if (selectedVid === '' || selectedVid === null) {
                        alert('Cannot commit without selecting a visit!');
                    } else if (eSig === '' || eSig === null) {
                        alert('Cannot commit without providing your electronic signature!');
                    } else {
                        $protocolForm.prop('selected_vid').value = selectedVid;
                        $protocolForm.prop('commit_esig').value = eSig;
                        // debugger
                        window.onbeforeunload = null;
                        $protocolForm.trigger('submit');
                    }
                })
                .on('click', '#request-raptor-protocol-collaboration', function (e) {
                    var $protocolForm = $('#raptor-glue-protocolinfo-form-builder'),
                            $collaborateForm = $('#raptor-glue-requestcollaborate-form-builder');

                    e.preventDefault();

                    //$protocolForm.prop('collaboration_uid').value = $collaborateForm.prop('group_collaborator_uid').value;
                    if ($collaborateForm.find('input[name=collaborator_uid]').val() > '') {
                        $protocolForm.prop('collaboration_uid').value = $collaborateForm.find('input[name=collaborator_uid]').val();
                    } else {
                        //Assume comment is for the existing collaborator.
                        $protocolForm.prop('collaboration_uid').value = $collaborateForm.find('input[name=prev_collaborator_uid]').val();
                    }

                    $protocolForm.prop('collaboration_note_tx').value = $collaborateForm.find('#edit-requester-notes-tx').val().trim();

                    //Only submit the form if valid.
                    if ($protocolForm.prop('collaboration_note_tx').value > '') {
                        if ($protocolForm.prop('collaboration_uid').value > '') {
                            //alert('Will submit with ['+$protocolForm.prop('collaboration_uid').value + ']  and ' + $protocolForm.prop('collaboration_note_tx').value);
                            //Form is valid so go ahead and submit it now.
                            //if form is submitting successfully, allow the user to navigate 
                            //away from the page without throwing the warning - 
                            window.onbeforeunload = null;
                            $protocolForm.trigger('submit');
                        } else {
                            alert('A collaborator must be selected');
                        }
                    } else {
                        alert('A comment is required');
                    }
                });

        //POSSIBLE TODO 4/25 --- Instead of commit based on ID use a class name such as 'trigger-choosevist'?
        // Interpretation Complete and Commit Details to VistA
        // QA Complete and Commit Details to VistA
        // Acknowledge and Commit Details to VistA
        // Exam Complete and Commit Details to VistA
        $('#edit-interpret-button-and-commit, #edit-qa-button-and-commit, #edit-finish-ap-button-and-commit, #edit-finish-pa-button-and-commit').on('click', function (e) {
            e.preventDefault();
            Drupal.behaviors.raptorShowAdministerDialog(
                    'Choose Visit',
                    'raptor/choosevisit',
                    null,
                    true,
                    {
                        width: 1000,
                        height: 600
                    },
            null
                    );
        });

        Drupal.behaviors.loadContent = function (element) {
            if (element) {
                var contentURL = $(element).attr('data-url');

                if (contentURL.length) {
                    $(element)
                            .html('<div style="text-align: center; padding-top: 8px;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading&hellip;</div>')
                            .load(contentURL + ' form', function () {
                                $(this)
                                        .find('h1')
                                        .remove()
                                        .end()
                                        .find('table.dataTable')
                                        .dataTable()
                                        .end();

                                $(this).find('#getprotocollibtab-table').each(function (index, element) {
                                    var table = $(this).DataTable();
                                    table
                                            .order([1, 'desc'])
                                            .draw();
                                })

                            });
                }
            }
        };

        // Load content into container via Ajax
        $('section[data-url]').each(function (index, element) {
            Drupal.behaviors.loadContent(element);
        });


        // Default sort for Vitals table
        $('.vitals-tab-table').
                DataTable().
                order([0, 'desc']).
                draw();
        // Default sort for Allergies Tab table
        $('.allergies-tab-table').
                DataTable().
                order([0, 'desc']).
                draw();

        // Default sort for Labs table
        $('.labs-tab-table').
                DataTable().
                order([0, 'desc']).
                draw();

        // Default sort for clinical reports tables
        $('.clinical-reports-tab-table-pathologly').
                DataTable().
                order([1, 'desc']).
                draw();
        $('.clinical-reports-tab-table-surgery').
                DataTable().
                order([1, 'desc']).
                draw();

        // Default sort for Problem List table
        $('.problem-list-tab-table').
                DataTable().
                order([1, 'desc']).
                draw();

        // Default sort for Notes tab tables
        $('#tab9').on('click', function () {
            //once the table is loaded, default sorting is applied
            $(document).ajaxSuccess(function () {
                $('.notes-tab-table').
                        DataTable().
                        order([1, 'desc']).
                        draw();
            });
        });

        // Attaching it to the tbody ensures that any rows dynamically added or displayed via jQuery Datables will also get the functionality
        $('#protocol_container').on('click', 'tbody .raptor-details', function (e) {
            e.preventDefault();
            $(this).hide().siblings('.hide').show();
        });

        $('.logo').click(function () {
            window.location.href = Drupal.pageData.baseURL + '/worklist?releasealltickets=TRUE'
        });

        //Navigate back to protocol tab handler
        $('.back-to-protocol-tab-link').on('click', function () {
            $('#tab1').click();
        });
        /*
         $('#edit-interpret-button').on('click', function (e) {
         document.cookie = 'worklistFilterMode=Interpretation';
         });
         
         $('#edit-qa-button, #edit-release-button').on('click', function (e) {
         document.cookie = 'worklistFilterMode=QA';
         });
         */

    });
}(document, jQuery));