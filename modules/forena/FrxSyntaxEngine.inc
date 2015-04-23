<?php
// $Id$
/**
 * @file
 * FrXSytnaxEngine defines how regular expression procesing/token substitution takes place.
 * It includes support for passing in a formatter oobject that will escape strings properly
 * before substituting them.
 *
 */

class FrxSyntaxEngine {
  private $tpattern;
  private $trim_chars;
  private $data; // Data object for current context.
  private $formatter; // Object used to format the data
  /**
  * Class for doing syntax replacements;
  * @param $regexp
  * @param $trim Trim characgers
  * @param $data Data object to be used for resolving contexts.
  * @return unknown_type
  */
  public function __construct($regexp, $trim, $formatter=NULL, $data=NULL) {
    $this->tpattern = $regexp;
    $this->trim_chars = $trim;
    if (is_object($formatter)) {
      $this->formatter=$formatter;
    }
    if ($data) {
      $this->data = $data;
    }
    else {
      $this->data = Frx::Data();
    }
  }





  /**
   * Get the value from the data.
   * This is used by token_replace method to extract the data based on the path provided.
   * @param $data
   * @param $key
   * @return unknown_type
   */
  protected function get_value($key, $raw=FALSE) {
    $context = '';
    $raw_key = $key;
    if ($key && strpos($key, '.')) {
      @list($context, $key) = explode('.', $key, 2);
      $o = Frx::Context($context);
    }
    else {
      $o = $this->data;
    }
    $value = $o->getValue($key, $context);
    if ($this->formatter) {
      $value = $this->formatter->format($value, $raw_key, $raw);
    }
    return $value;
  }


  /**
   *
   * @param $text text that needs replacing
   * @param $data
   * @return unknown_type
   */
  public function replace($text, $raw=FALSE) {
    if (is_array($text)) {
      foreach ($text as $key => $value) {
        $text[$key] = $this->replace($value, $raw);
      }
      return $text;
    }
    //Otherswise assume text
    $match=array();
    $o_text = $text;

    // Put the data on the stack.
    if (preg_match_all($this->tpattern, $o_text, $match)) {
      // If we are replacing a single value then return exactly
      // the single value in its native type;
      $single_value = ($match && count($match[0]) == 1 && $match[0][0]==$text && $raw);
      //list($params) = $match[1];
      $i=0;

      foreach ($match[0] as $match_num => $token) {
        $path = trim($token, $this->trim_chars);
        $value = $this->get_value($path, $raw);
        if ($single_value) {
          return $value;
        }
        else {
          $pos = strpos($text, $token);
          if ($pos !== FALSE) {
            $text = substr_replace($text, $value, $pos, strlen($token));
          }
        }
      }
    }
    return $text;
  }

  /**
   * List all of the tokens used in a piece of text, ignoring duplicates.
   *
   * @param string $text
   * @return array tokens contained in the text according to the regular expression.
   */
  public function tokens($text) {
    $match=array();
    $tokens = array();

    if (preg_match_all($this->tpattern, $text, $match)) {
      $i=0;
      foreach ($match[0] as $match_num => $token) {
        $path = trim($token, $this->trim_chars);
        if (array_search($path, $tokens)===FALSE) {
          $tokens[] = $path;
        }
      }
    }
    return $tokens;
  }

  /**
   * Convert an object into an array
   * @param mixed $data
   * Iterates the object and builds an array of strings from it.
   * If the object appears to be an xml object and has an attributes
   * method, do the same for it.
   */
  public function object_to_array($data) {
    if (is_object($data)) {
      $ar = array();
      foreach ($data as $key => $value) {
        $ar[$key] = (string)$value;
      }
      if (method_exists($data, 'attributes')) {
        foreach ($data->attributes() as $key => $value) {
          $ar[$key] = (string)$value;
        }
      }
      return $ar;
    }
    else {
      return $data;
    }
  }

  /**
   * Test for TRUE/FALSE for conditions that are able to be represented using bind parameters
   * Note that | are used to separate the different conditions and these are to be OR'd together.
   * @param  $condition String
   */
  public function test($condition) {
    $eval = TRUE;
    $tests = explode('&', $condition);
    if ($tests) foreach ($tests as $test) {
      $t = trim($test);
      $res = $this->replace($t, TRUE);
      if (is_string($res)) {
        $res = (trim($res, ' !')) ? TRUE : FALSE;
        if (strpos($t, '!')===0) $res = !$res;
      }
      else {
        $res = $res ? TRUE : FALSE;
      }
      $eval = $eval && $res;
    }
    return $eval;
  }
}
