/*jslint browser: true*/
/*global jQuery, document*/

(function (document, $) {
    'use strict';
    // this function is strict...

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

        $('#static-warnings').addClass('read-only').prependTo('#tab-content1');
    
        $('#edit-suspend-button')
            .on('click', function (e) {
                var $dialog;

                e.preventDefault();

                $(window).scrollTo('div:first-of-type', 800, {
                    onAfter: function () {

                        $('#suspend-modal')
                            .load('/drupal/raptor_datalayer/suspendticket form', function () {
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

        $('#raptor-protocol-collaborate').on('click', function (e) {
            e.preventDefault();
            Drupal.behaviors.raptorShowAdministerDialog('Request Collaboration', '/drupal/raptor_datalayer/requestcollaborate');
        });

        // Load content into container via Ajax
        $('section[data-url]').each(function (index, element) {
            if ($(this).attr('data-url')) {
                $(element)
                    .append('<div style="text-align: center;"><img src="sites/all/themes/raptor_omega/images/worklist-loader.gif"><br>Loading&hellip;</div>')
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

        // Attaching it to the tbody ensures that any rows dynamically added or displayed via jQuery Datables will also get the functionality
        $('#protocol_container').on('click', 'tbody .raptor-details', function (e) {
            e.preventDefault();
            $(this).hide().siblings('.hide').show();
        })

        if (Drupal.pageData.modality !== 'NM') {
            $('#edit-radioisotope-fieldset').hide();
        }
    });
}(document, jQuery));