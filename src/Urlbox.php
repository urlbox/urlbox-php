<?php

namespace Urlbox\Screenshots;

class Urlbox
{
  private $api_key;
  private $api_secret;
  
  public function __construct($api_key, $api_secret)
  {   
    $this->api_key = $api_key;
    $this->api_secret = $api_secret;
  }

  public static function fromCredentials($api_key, $api_secret)
  {
    return new self($api_key, $api_secret);
  }

  public function generateUrl($options)
  {
    $format = isset($options['format']) ? $options['format'] : 'png';
    unset($options['format']);
    $_parts = [];
    foreach ($options as $key => $values) {
      $values = is_array($values) ? $values : [$values];
      foreach ($values as $value) {
        if(isset($value)){
          $encodedValue = $this->sanitizeValue($value);
          $_parts[] = "$key=$encodedValue";
        }
      }
    }
    $query_string = implode("&", $_parts);
    $TOKEN = hash_hmac("sha1", $query_string, $this->api_secret);
    return "https://api.urlbox.io/v1/$this->api_key/$TOKEN/$format?$query_string";
  }

  private function sanitizeValue($val)
  {
    $type = gettype($val);
    if($type == 'string'){return $this->encodeURIComponent($val);}
    return var_export($val, true);

  }

  public function encodeURIComponent2($val)
  {
    $result = rawurlencode($val);
    $result = str_replace('+', '%20', $result);
    $result = str_replace('%21', '!', $result);
    $result = str_replace('%2A', '*', $result);
    $result = str_replace('%27', '\'', $result);
    $result = str_replace('%28', '(', $result);
    $result = str_replace('%29', ')', $result);
    return $result;
  }

  public function encodeURIComponent($val)
  {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($val), $revert);
  }

  private function ensureIsValidCredentials($api_key, $api_secret)
  {
    if (!$api_key) {
      throw new InvalidArgumentException(
        sprintf('requires an api key')
      );
    }
    if (!$api_secret) {
      throw new InvalidArgumentException(
        sprintf('requires an api secret')
      );
    }
  }
}