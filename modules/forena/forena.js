/**
 * @file
 * Legacy forena behaviors.  These are deprecated. 
 */
(function ($) {

  Drupal.behaviors.forenaCallback = {
    attach: function (context, settings) {
       forenaCallback(Drupal.settings.forena.form, context, settings); 
       forenaCallback(Drupal.settings.forena.report, context, settings); 
    }
  };

})(jQuery);

function forenaCallback(fnName, context, settings) { 
  fn = window[fnName];
  fnExists = typeof fn === 'function';
  if(fnExists) {
	  fn(context, settings);
  }
}
