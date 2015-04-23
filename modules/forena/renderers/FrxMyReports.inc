<?php
class FrxMyReports extends FrxRenderer {
  /*
   * This custom renderer uses the existing forena reports module
   */
  public function render() {
    $variables = $this->mergedAttributes();
    $category = isset($variables['category']) ? $variables['category']: '';
    if (user_access('list reports')) {
      return forena_user_reports($category);
    }
  }
}