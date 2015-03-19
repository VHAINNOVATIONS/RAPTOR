/*jslint browser: true*/
/*global jQuery, document*/

(function (document, $) {
    'use strict';
    // this function is strict...

    $(document).on('click', '.select-protocol', function (e) {
        e.preventDefault();
        $('#edit-protocol1-nm').val( $(this).attr('data-protocol_shortname') );
        $('#tab1').trigger('click');
    })

    $(document).ready(function () {
        // Determine whether all hyperlinks need to be disabled
        Drupal.pageData.disableLinks = false;

        $('a', '.tabs-wrapper').on('click', function (e) {
            if (Drupal.pageData.disableLinks) {
                e.preventDefault();
            }
        })

        Drupal.behaviors.disableProtocolControls = function() {
            Drupal.pageData.disableLinks = true;
            $('input, select, textarea')
                .not('#edit-protocol1-nm')
                .attr('disabled', 'disabled');
            $('a[href^=javascript]').each(function (index, element) {
                $(element)
                    .attr('data-onclick', $(this).attr('href'))
                    .attr('href', 'javascript:void();');
            })
        }

        Drupal.behaviors.enableProtocolControls = function() {
            Drupal.pageData.disableLinks = false;
            $('input, select, textarea')
                .not('#edit-protocol1-nm, .container-inline select, #edit-contrast-fieldset select')
                .removeAttr('disabled');
            $('[data-onclick]').each(function (index, element) {
                $(element)
                    .attr('href', $(this).attr('data-onclick'))
                    .removeAttr('data-onclick');
            })
        }

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
                var $dialog;

                e.preventDefault();

                $(window).scrollTo('div:first-of-type', 800, {
                    onAfter: function () {
                        $('#cancelorder-modal')
                            .html('<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading&hellip;</div>')
                            .dialog({
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
                                    width: 550
                                })
                            .load('raptor/cancelorder form', function () {
                                $dialog = $(this);
                                
                                $(this).on('click', '.raptor-dialog-cancel', function (e) {
                                    $dialog.dialog('close');
                                });
                            });
                    }
                })
        });


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

        $('#administer-modal').on('click', '#request-raptor-protocol-collaboration', function (e) {
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
                    $protocolForm.trigger('submit');
                } else {
                    alert('A collaborator must be selected');
                }
            } else {
                alert('A comment is required');
            }
        });

        // Interpretation Complete and Commit Details to Vista
        // QA Complete and Commit Details to Vista
        $('#edit-interpret-button-and-commit, #edit-qa-button-and-commit').on('click', function (e) {
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

        $('#administer-modal').on('click', '#edit-action-buttons-okay', function (e) {
            var $protocolForm = $('#raptor-glue-protocolinfo-form-builder'),
                $chooseVisitForm = $('#raptor-glue-choosevisit-form-builder');

            e.preventDefault();
            //$protocolForm.prop('selected_vid').value = $chooseVisitForm.find('[name=group_vid]').val();
            var selectedVid = $chooseVisitForm.find('input[name=group_vid]:checked').val();
            var eSig = $chooseVisitForm.find('[name=subform_commit_esig]').val()
            if(selectedVid == '' || selectedVid == null)
            {
                alert('Cannot commit without selecting a visit!');
            } else
            if(eSig == '' || eSig == null)
            {
                alert('Cannot commit without providing your electronic signature!');
            } else {
                $protocolForm.prop('selected_vid').value = selectedVid;
                $protocolForm.prop('commit_esig').value = eSig;
                // debugger
                $protocolForm.trigger('submit');
            }
        });

        // Load content into container via Ajax
        $('section[data-url]').each(function (index, element) {
            if ($(this).attr('data-url')) {
                $(element)
                    .append('<div style="text-align: center; padding-top: 8px;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading&hellip;</div>')
                    .load($(element).attr('data-url') + ' form', function () {
                        $(this)
                          .find('h1')
                          .remove()
                          .end()
                          .find('table.dataTable')
                          .dataTable()
                          .end()
                    });
            }
        });

        // Default sort for Notes table
        $('#selected-notes')
            .DataTable()
            .order([1, 'desc'])
            .draw();

        // Attaching it to the tbody ensures that any rows dynamically added or displayed via jQuery Datables will also get the functionality
        $('#protocol_container').on('click', 'tbody .raptor-details', function (e) {
            e.preventDefault();
            $(this).hide().siblings('.hide').show();
        })

        $('#edit-interpret-button').on('click', function (e) {
            document.cookie = 'worklistFilterMode=Interpretation';
        });

        $('#edit-qa-button, #edit-release-button').on('click', function (e) {
            document.cookie = 'worklistFilterMode=QA';
        });

    });
}(document, jQuery));