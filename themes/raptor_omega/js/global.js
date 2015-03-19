
(function (document, $) {
    $(document).ajaxSuccess(function () {
        $('.datepicker').datepicker({
            onSelect: function () {
                $(".ui-datepicker a").removeAttr("href");
            }
        });
        $('.hasTimepicker').timepicker({
            'timeFormat': 'H:i',
            'step': 15,
            onSelect: function () {
                $('.ui-timepicker a').removeAttr("href");
            }
        });
    });

    $(document).ajaxSuccess(function () {
        $('#my-raptor-dialog-table-rad-reports')
                .DataTable()
                .order([1, 'desc'])
                .draw();
    });

}(document, jQuery));

