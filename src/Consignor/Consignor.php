<?php

/**
 * @file
 * Consignor class, Object manager class.
 */

namespace Consignor;

/**
 * Class Consignor
 * @package Consignor
 */
class Consignor {

  protected $ServerRequestType;
  protected $ServerURL;
  protected $ServerAPIKey;
  protected $ServerRequestHeaders;

  /**
   * @param array $data
   * @return mixed
   * @throws \Exception
   */
  protected function getRequest($data = array()) {
    return $this->httpRequest('GET', $data);
  }

  /**
   * @param array $data
   * @return mixed
   * @throws \Exception
   */
  protected function postRequest($data = array()) {
    return $this->httpRequest('POST', $data);
  }

  /**
   * @param $type
   * @param $data
   * @return mixed
   * @throws \Exception
   */
  private function httpRequest($type, $data) {
    $ch = curl_init();
    switch ($type) {
      case 'GET':
        curl_setopt($ch, CURLOPT_URL, $this->ServerURL . '?' . $data);
        break;

      case 'POST':
        curl_setopt($ch, CURLOPT_URL, $this->ServerURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        break;
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);

    $data = curl_exec($ch);
    $info = curl_getinfo($ch);

    $error = $this->deserializeData($data);
    if ($info['http_code'] != 200 || isset($error->ErrorMessages)) {
      $error_message = '';
      if (isset($error->ErrorMessages)) {
        $error_message .= implode(', ', $error->ErrorMessages);
      }
      else {
        $error_message = 'Error when getting ' . $this->ServerURL . '. Message: "';
        $error_message .= strip_tags($error);
        $error_message .= '" (' . $info['http_code'] . ')';
      }
      throw new \Exception($error_message);
    }
    return $data;
  }

  /**
   * @param array $data
   * @param bool $method
   * @return array|string
   */
  protected function serializeRequestData($data = array(), $method = FALSE) {
    if (!$method) {
      $method = $this->ServerRequestType;
    }
    switch ($method) {
      case 'JSON':
        return json_encode($data);

      case 'QUERY':
        return http_build_query($data);

      default:
        return $data;
    }
  }

  /**
   * @param $data
   * @param bool $method
   * @return mixed
   */
  protected function deserializeData($data, $method = FALSE) {
    if (!$method) {
      $method = $this->ServerRequestType;
    }
    switch ($method) {
      case 'JSON':
        return json_decode($data);

      default:
        return $data;
    }
  }

}

/**
 * Class ObjectManager
 * @package Consignor
 */
class ObjectManager implements \JsonSerializable {

  /**
   * @param null $Object
   */
  public function __construct($Object = NULL) {
    if (is_array($Object) || is_object($Object)) {
      foreach ($Object as $Key => $Value) {
        try {
          $this->setValue($Key, $Value);
        }
        catch (\Exception $e) {
          continue;
        }
      }
    }
  }

  /**
   * @param $Key
   * @param $Value
   * @throws \Exception
   */
  public function setValue($Key, $Value) {
    if (!property_exists($this, $Key)) {
      throw new \Exception("Key $Key does not exist.");
    }
    if (is_array($this->{$Key}) && is_object($Value) && $class = $this->NestedClass($Key)) {
      $this->{$Key}[] = new $class($Value);
    }
    elseif (is_array($this->{$Key}) && !is_array($Value)) {
      $this->addValue($Key, $Value);
    }
    elseif (is_array($this->{$Key}) && is_array($Value)) {
      foreach ($Value as $v) {
        $this->setValue($Key, $v);
      }
    }
    else {
      $this->{$Key} = $Value;
    }
  }

  /**
   * @param $Key
   * @param $Value
   * @throws \Exception
   */
  public function addValue($Key, $Value) {
    if (!property_exists($this, $Key)) {
      throw new \Exception("Key $Key does not exist.");
    }
    if (!is_array($this->{$Key})) {
      throw new \Exception("Key $Key is not an array.");
    }
    $this->{$Key}[] = $Value;
  }

  /**
   * @param $Key
   * @param null $index
   * @return array
   * @throws \Exception
   */
  public function getValue($Key, $index = NULL) {
    if (!property_exists($this, $Key)) {
      throw new \Exception("Key $Key does not exist.");
    }
    if (is_array($this->{$Key}) && isset($index)) {
      return $this->{$Key}[$index];
    }
    return $this->{$Key};
  }

  /**
   * @param $Key
   * @return bool
   */
  protected function NestedClass($Key) {
    return FALSE;
  }

  /**
   * @return array
   *
   * @todo This is PHP5.4 compatible only - do something about that.
   */
  public function jsonSerialize() {
    return get_object_vars($this);
  }
}
