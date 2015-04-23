<?php

/**
 * Interface class for Data contexts. The basic methods are implemented as below. This is too simple for real use in
 * Forena, but serves as a model for implementation.
 * @author davidmetzler
 *
 */
class FrxContext {
  private $values;

  public function __construct() {
    $this->values = array();
  }

  public function getValue($key,  $context = '') {
    $value = @$values[$key];
    return $value;
  }

  public function setValue($key, $value, $context = '') {
    $this->values[$key] = $value;
  }

}