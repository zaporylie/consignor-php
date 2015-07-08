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
   *
   * @return mixed
   * @throws \Exception
   */
  protected function getRequest($data = array()) {
    return $this->httpRequest('GET', $data);
  }

  /**
   * @param array $data
   *
   * @return mixed
   * @throws \Exception
   */
  protected function postRequest($data = array()) {
    return $this->httpRequest('POST', $data);
  }

  /**
   * @param $type
   * @param $data
   *
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
   *
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
   *
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
   * @param null $object
   */
  public function __construct($object = NULL) {
    if (is_array($object) || is_object($object)) {
      foreach ($object as $key => $value) {
        try {
          $this->setValue($key, $value);
        }
        catch (\Exception $e) {
          continue;
        }
      }
    }
  }

  /**
   * @param $key
   * @param $value
   *
   * @throws \Exception
   */
  public function setValue($key, $value) {
    if (!property_exists($this, $key)) {
      throw new \Exception("Key $key does not exist.");
    }
    if (is_array($this->{$key}) && is_object($value) && $class = $this->nestedClass($key)) {
      $this->{$key}[] = new $class($value);
    }
    elseif (is_array($this->{$key}) && !is_array($value)) {
      $this->addValue($key, $value);
    }
    elseif (is_array($this->{$key}) && is_array($value)) {
      foreach ($value as $v) {
        $this->setValue($key, $v);
      }
    }
    else {
      $this->{$key} = $value;
    }
  }

  /**
   * @param $key
   * @param $value
   *
   * @throws \Exception
   */
  public function addValue($key, $value) {
    if (!property_exists($this, $key)) {
      throw new \Exception("Key $key does not exist.");
    }
    if (!is_array($this->{$key})) {
      throw new \Exception("Key $key is not an array.");
    }
    $this->{$key}[] = $value;
  }

  /**
   * @param $key
   * @param null $index
   *
   * @return array
   * @throws \Exception
   */
  public function getValue($key, $index = NULL) {
    if (!property_exists($this, $key)) {
      throw new \Exception("Key $key does not exist.");
    }
    if (is_array($this->{$key}) && isset($index)) {
      return $this->{$key}[$index];
    }
    return $this->{$key};
  }

  /**
   * @param $key
   *
   * @return bool
   */
  protected function nestedClass($key) {
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
