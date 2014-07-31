/*jslint browser: true*/
/*global jQuery, document*/

(function (document, $) {
    'use strict';
    // this function is strict...

    $(document).ready(function () {
	
        /*** Column Modal Section ***/

        //Click function to open up the modal
        $('.change-columns').on('click', function () {
            $('#column-modal').dialog({
                modal: true,
                show: {
                    effect: 'fadeIn'
                },
                hide: {
                    effect: 'fadeOut'
                },
                buttons: {
                    'Save': function () {
                        var columnsToHide = [];

                        $('[name=column_display]', '#column-modal').each(function (index, element) {
                            // Use DataTables API to hide/show columns
                            $worklistTable.column( element.value ).visible( element.checked ? true : false );

                            // Keep track of columns hidden
                            if (!element.checked) {
                                columnsToHide.push($(element).next('span').text());
                            }
                        });

                        // Persist hidden columns via Ajax
                        $.get('raptor_declarehiddenworklistcols', { hidden_worklistcols: columnsToHide.join(',') });

                        $(this).dialog('close');
                    }
                }
            });
        });

        $('.chk-all').on('click', function (e) {
            var inputs = document.getElementsByTagName('input'),
                checkId = $(this).attr('data-checkId'),
                i;

            e.preventDefault();
            for (i = 0; i < inputs.length; i += 1) {
                if (inputs[i].type === 'checkbox' && inputs[i].id === checkId) {
                    if (inputs[i].checked === true) {
                        inputs[i].checked = false;
                    } else if (inputs[i].checked === false) {
                        inputs[i].checked = true;
                    }
                }
            }
        });

        // This will set a cookie containing the worklist filter mode. It will usually get called when the user is going to a protocol
        var setWorklistFilterMode = function () {
            if ($('#worklist_filter').val() === 'AP|PA') {
                document.cookie = 'worklistFilterMode=Ready for Examination';
            } else {
                document.cookie = 'worklistFilterMode=';
            }
        }

        var checkWorklistFilterMode = function () {
            // TODO: Turn the cookie retrieval method below into a global function
            var worklistFilterMode = document.cookie.replace(/(?:(?:^|.*;\s*)worklistFilterMode\s*\=\s*([^;]*).*$)|^.*$/, "$1");
            
            if (worklistFilterMode === 'Ready for Examination') {
                // Select 'Ready for Examination' option
                $('#worklist_filter').prop('selectedIndex', 1);
            }
        }

        checkWorklistFilterMode();

        $('#worklistTable')
            .find('td:nth-of-type(n):not(td:first-child, td:last-child)').on('click', function (e) {
                var myrawrtid = $(this).closest('tr').attr('data-rawrtid'),
                    clickMode = $('#selection_mode').val();

                e.preventDefault();
                setWorklistFilterMode();

                if (clickMode === 'edit') {
                    window.location.href = 'protocol?rawrtid=' + myrawrtid; 
                } else if (clickMode === 'view') {
                    window.location.href = 'protocol?rawrtid=' + myrawrtid + '&mode=VIEW'; 
                } else if (clickMode === 'checkmark') {
                    $(this)
                        .parent()
                        .find('td:first-child :checkbox')
                        .trigger('click');
                }
            })
            .end()
            .find('td:first-child').on('click', function (e) {
                // Usability improvement: toggle the checkbox when it's parent cell is clicked
                if ($(e.target).is('td')) {
                    $(this)
                        .find('[type=checkbox]')
                        .each(function () {
                            if ($(this).is(':checked') === true) {
                                this.checked = false;
                            } else {
                                this.checked = true;
                            }
                        })
                }
            })
            .end()
            .find('td:last-child').on('click', function (e) {
                var $thisTd = $(this),
                    scheduledText = $thisTd.text(),
                    scheduledArray = scheduledText.split('@'),
                    scheduledDate = new Date(scheduledArray[0].replace(/-/g, '/')),
                    scheduledTime = scheduledArray[1];

                e.preventDefault();

                Drupal.behaviors.raptorShowAdministerDialog(
                    'Schedule for ' + $(this).parent().find('.pat_column').text(), 
                    'raptor_datalayer/scheduleticket?rawrtid='+ $(this).parent().attr('data-rawrtid')
                );

            })
        
        // Have to do this one separately since we'll use $worklistTable to hide and show columns later
        var $worklistTable = $('#worklistTable').DataTable({
                "order": [[ 2, "desc" ]],
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
            });

        // Filter Worklist by Worklist Status Code
        var updateWorklistFilterMode = function () {
            $('#worklistTable')
                .DataTable()
                .column(13)
                .search('^(' + $('#worklist_filter').val() + ')$', true, true)
                .draw();
        }

        $('#worklist_filter').change(updateWorklistFilterMode);

        updateWorklistFilterMode();

        var hideWorklistColumns = function () {
            var columnKey = '',
                columnNumber = 0,
                valuePattern = /[\d]+/,
                i = 0,
                columns = {
                    'Tracking ID': {
                        selector: '[name=column_display][value=1]'
                    },
                    'Patient': {
                        selector: '[name=column_display][value=3]'
                    },
                    'Date/Time Desired': {
                        selector: '[name=column_display][value=4]'
                    },
                    'Date Ordered': {
                        selector: '[name=column_display][value=5]'
                    },
                    'Modality': {
                        selector: '[name=column_display][value=6]'
                    },
                    'Image Type': {
                        selector: '[name=column_display][value=7]'
                    },
                    'Study': {
                        selector: '[name=column_display][value=8]'
                    },
                    'Urgency': {
                        selector: '[name=column_display][value=9]'
                    },
                    'Transport': {
                        selector: '[name=column_display][value=10]'
                    },
                    'Patient Category / Location': {
                        selector: '[name=column_display][value=11]'
                    },
                    'Workflow Status': {
                        selector: '[name=column_display][value=12]'
                    },
                    'Assignment': {
                        selector: '[name=column_display][value=14]'
                    },
                    'Scheduled': {
                        selector: '[name=column_display][value=15]'
                    }
                };

            // Loop through hidden columns
            for (i = 0; i < Drupal.pageData.hiddenColumns.length; i++) {
                columnKey = Drupal.pageData.hiddenColumns[i];
                if (columns[columnKey] !== undefined) {
                    // Get the column from the value attribute
                    columnNumber = valuePattern.exec(columns[columnKey].selector)[0];

                    // Hide the column using the DataTables API
                    $worklistTable.column(columnNumber).visible(false);

                    // Uncheck the checkbox
                    $(columns[columnKey].selector).attr('checked', false);
                } else {
                    // Log missing columns just in case the labels don't coincide with what's in columns
                    console.log('%s worklist column not found', columnKey);
                }
            }
        };

        // Hide hidden worklist columns on page load
        hideWorklistColumns();

        // Hide loader animation and display table
        $('#worklistLoaderWrapper, #worklistTable').toggle();

        // Edit Ranking Mode button
        $('#edit-ranking-mode').on('click', function (e) {
            e.preventDefault();
            $('#edit-ranking-mode-modal')
              .load('raptor_datalayer/editworklistranking #raptor-admin-container', function () {

                  $(this)
                    .find('h1')
                    .remove()
                    .end()
                    .dialog({
                        width: 1000,
                        height: 800,
                        modal: true,
                        autoOpen: true
                    });

              })
              .on('click', '.raptor-dialog-cancel', function (e) {
                $('#edit-ranking-mode-modal').dialog('close');
              });

        });

        // Edit Top Work Order button
        $('#edit-top-work-order-top, #edit-top-work-order-bottom').on('click', function (e) {
            var $checked, checkboxValues = [], urlKey, urlIDs;

            e.preventDefault();
            $checked = $('[name=tracking-id]:checked');

            // Are any checkboxes checked?
            if ($checked.length === 0) {
                // Default to first checkbox if nothing is checked
                // The selector has to check for visible TR elements because the table sort plug-in will occasionally hide rows from view
                // TODO: Make sure rows that aren't visible due to jQuery table pagination are also selected
                $checked = $('table tbody tr:visible')
                                .eq(0)
                                .find('td:first-child :checkbox');
            }

            // Create array of checked items
            $checked.each(function (index, element) {
                checkboxValues.push(element.value);
            });

            urlIDs = checkboxValues.reverse().join(',');

            // Use different URL parameter if there's only one checkbox
            if ($checked.length === 1) {
                urlKey = 'rawrtid';
                urlIDs = '[' + urlIDs + ']';
            } else {
                urlKey = 'pbatch';
            }

            setWorklistFilterMode();
            window.location.href = 'protocol?' + urlKey + '=' + urlIDs;
        });

        $('.refresh-worklist').on('click', function (e) {
            e.preventDefault();
            window.location.href = 'worklist';
        });  

        // When user is redirected back to Worklist via Cancel button bring up dialog to go back to where user left off

        if (location.search.indexOf('dialog=manageUsers') !== -1) {
          $('#nav-manageusers').trigger('click');
        }

        if (location.search.indexOf('dialog=manageProtocolLib') !== -1) {
          $('#nav-manageprotocolLibpage').trigger('click');
        }

        if (location.search.indexOf('dialog=manageContraindications') !== -1) {
          $('#nav-managecontraindications').trigger('click');
        }

        if (location.search.indexOf('dialog=managelists') !== -1) {
          $('#nav-managelists').trigger('click');
        }

        if (location.search.indexOf('dialog=viewReports') !== -1) {
          $('#nav-viewReports').trigger('click');
        }

    }); // end $(document).ready()
}(document, jQuery));