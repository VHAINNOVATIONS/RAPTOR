/**
 * @file
 * Legacy forena behaviors.  These are deprecated. 
 */
(function ($) {

  Drupal.behaviors.forenaAdmin = {
    attach: function (context, settings) {
      if ($.fn.dataTable) {
        $('table.dataTable-paged', context).dataTable({
          "sPaginationType": "full_numbers"
        });

        $('#forena-block-preview .FrxTable table', context).dataTable({
          "sPaginationType" : "full_numbers",
          "bSort": true,
          "bAutoWidth": false,
          "sScrollX": "100%",
          "bScrollCollapse": true
        });
      }
    }
  };

})(jQuery);
